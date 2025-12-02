<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use App\Exports\EventAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function show(Request $request, Event $event): View
    {
        $user = $request->user();
        $this->authorizeAccess($user, $event);

        $filters = [
            'course' => $request->input('course'),
            'year_level' => $request->input('year_level'),
            'society' => $request->input('society'),
        ];

        $role = $user->role?->slug;
        $societies = match ($role) {
            'officer' => $user->societies()->orderBy('abbreviation')->get(),
            default => \App\Models\Society::orderBy('abbreviation')->get(),
        };

        // If officer passed a society filter that isn't theirs, ignore it.
        if ($role === 'officer' && $filters['society'] && ! $societies->pluck('id')->contains((int) $filters['society'])) {
            $filters['society'] = null;
        }

        // For CEIT-wide events, officers can view only their own society members.
        if ($role === 'officer' && $event->audience === 'ceit_students') {
            $filters['society'] = $societies->pluck('id')->first();
        }

        $records = AttendanceRecord::with(['user', 'recorder'])
            ->where('event_id', $event->id)
            ->when($filters['course'], fn ($q, $course) => $q->whereHas('user', fn ($sub) => $sub->where('course', $course)))
            ->when($filters['year_level'], fn ($q, $year) => $q->whereHas('user', fn ($sub) => $sub->where('year_level', $year)))
            ->when($filters['society'], fn ($q, $society) => $q->whereHas('user.societies', fn ($sub) => $sub->where('society_id', $society)))
            ->latest()
            ->paginate(15);

        $counts = [
            'total' => AttendanceRecord::where('event_id', $event->id)->count(),
            'time_in' => AttendanceRecord::where('event_id', $event->id)->whereNotNull('time_in')->count(),
            'time_out' => AttendanceRecord::where('event_id', $event->id)->whereNotNull('time_out')->count(),
        ];

        $courseCounts = AttendanceRecord::selectRaw('users.course, count(attendance_records.id) as total')
            ->join('users', 'attendance_records.user_id', '=', 'users.id')
            ->where('attendance_records.event_id', $event->id)
            ->when($filters['year_level'], fn ($q, $year) => $q->where('users.year_level', $year))
            ->when($filters['society'], fn ($q, $society) => $q->whereExists(function ($sub) use ($society) {
                $sub->selectRaw(1)
                    ->from('society_user')
                    ->whereColumn('society_user.user_id', 'users.id')
                    ->where('society_user.society_id', $society);
            }))
            ->groupBy('users.course')
            ->get();

        $yearCounts = AttendanceRecord::selectRaw('users.year_level, count(attendance_records.id) as total')
            ->join('users', 'attendance_records.user_id', '=', 'users.id')
            ->where('attendance_records.event_id', $event->id)
            ->when($filters['course'], fn ($q, $course) => $q->where('users.course', $course))
            ->when($filters['society'], fn ($q, $society) => $q->whereExists(function ($sub) use ($society) {
                $sub->selectRaw(1)
                    ->from('society_user')
                    ->whereColumn('society_user.user_id', 'users.id')
                    ->where('society_user.society_id', $society);
            }))
            ->groupBy('users.year_level')
            ->get();

        return view('events.attendance', compact('event', 'records', 'counts', 'courseCounts', 'yearCounts', 'filters', 'societies'));
    }

    public function record(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $event);

        $data = $request->validate([
            'mode' => ['required', 'in:time_in,time_out'],
            'method' => ['required', 'in:qr,manual,hybrid'],
            'qr_raw_text' => ['nullable', 'string', 'max:500'],
            'id_number' => ['nullable', 'string', 'max:50'],
        ]);

        $target = $this->findTargetUser($data['qr_raw_text'] ?? null, $data['id_number'] ?? null);
        if (! $target) {
            return response()->json([
                'message' => 'Student not found.',
                'raw_value' => $data['qr_raw_text'] ?? $data['id_number'],
            ], 404);
        }

        $record = AttendanceRecord::firstOrNew([
            'event_id' => $event->id,
            'user_id' => $target->id,
            'recorded_by' => $user->id,
        ]);

        // Restrict society officers on CEIT-wide events to recording only their own society members.
        if ($user->role?->slug === 'officer' && $event->audience === 'ceit_students') {
            $officerSocietyIds = $user->societies()->pluck('societies.id');
            $isSameSociety = $target->societies()->whereIn('societies.id', $officerSocietyIds)->exists();
            abort_unless($isSameSociety, 403);
        }

        $now = Carbon::now();

        if ($data['mode'] === 'time_in') {
            $record->time_in = $record->time_in ?: $now;
        } else {
            // time_out
            $record->time_out = $record->time_out ?: $now;
        }

        $record->method = $data['method'];
        $record->save();

        return response()->json([
            'message' => 'Recorded',
            'student' => [
                'id' => $target->id,
                'name' => $target->name,
                'course' => $target->course,
                'year_level' => $target->year_level,
                'societies' => $target->societies()->pluck('abbreviation'),
            ],
            'record' => [
                'id' => $record->id,
                'time_in' => optional($record->time_in)->toDateTimeString(),
                'time_out' => optional($record->time_out)->toDateTimeString(),
                'method' => $record->method,
            ],
            'recorded_by' => $user->name,
            'raw_value' => $data['qr_raw_text'] ?? $data['id_number'],
        ]);
    }

    protected function authorizeAccess(User $user, Event $event): void
    {
        $role = $user->role?->slug;
        if (in_array($role, ['admin', 'lsg_officer'], true)) {
            return;
        }

        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            $isOwnSocietyEvent = $event->society_id && $societyIds->contains($event->society_id);
            $isCeitWide = $event->audience === 'ceit_students';
            abort_unless($isOwnSocietyEvent || $isCeitWide, 403);
            return;
        }

        abort(403);
    }

    protected function findTargetUser(?string $qr, ?string $idNumber): ?User
    {
        if ($qr) {
            $found = User::where('qr_raw_text', $qr)->first();
            if ($found) {
                return $found;
            }
        }

        if ($idNumber) {
            return User::where('id_number', $idNumber)->first();
        }

        return null;
    }

    public function export(Request $request, Event $event)
    {
        $this->authorizeAccess($request->user(), $event);
        $filters = $request->only(['course', 'year_level', 'society']);

        // Force society filter for officers on CEIT-wide events.
        if ($request->user()->role?->slug === 'officer' && $event->audience === 'ceit_students') {
            $officerSocietyId = $request->user()->societies()->pluck('societies.id')->first();
            $filters['society'] = $officerSocietyId;
        }

        $export = new EventAttendanceExport($event, $filters);
        $export->fileName = 'attendance-'.$event->id.'.xlsx';
        return Excel::download($export, $export->fileName);
    }
}
