<?php

namespace App\Http\Controllers;

use App\Exports\EventAbsentExport;
use App\Exports\EventAttendanceExport;
use App\Models\Event;
use App\Models\User;
use App\Support\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LsgAnalyticsController extends Controller
{
    public function show(Request $request, Event $event): View
    {
        $this->authorizeLsg($request->user(), $event);

        [$records, $summary, $filters] = $this->buildAttendanceData($request, $event);
        $absent = $this->computeAbsent($event, $filters, $request->user());
        $breakdown = $this->societyBreakdown($event, $filters);

        return view('lsg.analytics', [
            'event' => $event,
            'records' => $records,
            'summary' => $summary,
            'filters' => $filters,
            'absent' => $absent,
            'breakdown' => $breakdown,
        ]);
    }

    public function export(Request $request, Event $event)
    {
        $this->authorizeLsg($request->user(), $event);
        [, , $filters] = $this->buildAttendanceData($request, $event, paginate: false);

        $format = $request->input('format', 'xlsx');
        $file = "attendance-event-{$event->id}.{$format}";
        $export = new EventAttendanceExport($event, $filters, $file);

        return Excel::download($export, $file);
    }

    public function exportAbsent(Request $request, Event $event)
    {
        $this->authorizeLsg($request->user(), $event);
        $filters = $this->extractFilters($request);
        $expectedIds = $this->expectedUserIds($event, $request->user());
        $format = $request->input('format', 'xlsx');
        $file = "absent-event-{$event->id}.{$format}";
        $export = new EventAbsentExport($event, $expectedIds, $filters, $file);

        return Excel::download($export, $file);
    }

    protected function buildAttendanceData(Request $request, Event $event, bool $paginate = true): array
    {
        $filters = $this->extractFilters($request);

        $query = $event->attendanceRecords()->with(['user.societies'])
            ->when($filters['society'], fn ($q, $v) => $q->whereHas('user.societies', fn ($s) => $s->where('society_id', $v)))
            ->when($filters['course'], fn ($q, $v) => $q->whereHas('user', fn ($u) => $u->whereIn('course', (array) $v)))
            ->when($filters['section'], fn ($q, $v) => $q->whereHas('user', fn ($u) => $u->where('section', $v)))
            ->when($filters['year_level'], fn ($q, $v) => $q->whereHas('user', fn ($u) => $u->where('year_level', $v)))
            ->when($filters['position'], fn ($q, $v) => $q->whereHas('user.societies', fn ($s) => $s->where('position', $v)));

        $records = $paginate ? $query->paginate(20) : $query->get();

        $summary = [
            'total' => $query->count(),
            'societies_represented' => $event->attendanceRecords()
                ->join('society_user', 'society_user.user_id', '=', 'attendance_records.user_id')
                ->distinct('society_user.society_id')
                ->count('society_user.society_id'),
        ];

        $records->each(function ($record) use ($event) {
            $record->computed_status = AttendanceStatus::for($record, $event);
        });

        return [$records, $summary, $filters];
    }

    protected function computeAbsent(Event $event, array $filters, $requester = null)
    {
        $expectedIds = $this->expectedUserIds($event, $requester ?? auth()->user());
        $attendedIds = $event->attendanceRecords()->pluck('user_id')->all();
        $absentIds = array_values(array_diff($expectedIds, $attendedIds));

        if (! $this->eventHasEnded($event)) {
            return collect();
        }

        return User::with('societies')
            ->whereIn('id', $absentIds)
            ->when($filters['society'], fn ($q, $v) => $q->whereHas('societies', fn ($s) => $s->where('society_id', $v)))
            ->when($filters['course'], fn ($q, $v) => $q->whereIn('course', (array) $v))
            ->when($filters['section'], fn ($q, $v) => $q->where('section', $v))
            ->when($filters['year_level'], fn ($q, $v) => $q->where('year_level', $v))
            ->when($filters['position'], fn ($q, $v) => $q->whereHas('societies', fn ($s) => $s->where('position', $v)))
            ->get();
    }

    protected function societyBreakdown(Event $event, array $filters)
    {
        $query = $event->attendanceRecords()
            ->selectRaw('society_user.society_id, count(attendance_records.id) as total')
            ->join('society_user', 'society_user.user_id', '=', 'attendance_records.user_id')
            ->when($filters['society'], fn ($q, $v) => $q->where('society_user.society_id', $v))
            ->when($filters['course'], fn ($q, $v) => $q->join('users', 'users.id', '=', 'attendance_records.user_id')->whereIn('users.course', (array) $v))
            ->groupBy('society_user.society_id')
            ->get();

        return $query;
    }

    protected function extractFilters(Request $request): array
    {
        $course = $request->input('course');
        if (is_string($course) && str_contains($course, ',')) {
            $course = array_filter(explode(',', $course));
        }
        return [
            'society' => $request->input('society'),
            'course' => $course ? (array) $course : null,
            'section' => $request->input('section'),
            'year_level' => $request->input('year_level'),
            'position' => $request->input('position'),
        ];
    }

    protected function expectedUserIds(Event $event, User $requester): array
    {
        $aud = $event->audience;
        $societyId = $event->society_id;
        $creatorId = $event->created_by;

        $ids = match ($aud) {
            'ceit_students' => User::where('department', 'CEIT')->pluck('id')->all(),
            'society_members', 'others' => User::whereHas('societies', fn ($q) => $q->where('society_id', $societyId))->pluck('id')->all(),
            'society_officers' => User::whereHas('societies', fn ($q) => $q->where('society_id', $societyId)->where('position', 'Officer'))->pluck('id')->all(),
            'all_officers' => User::where(function ($q) {
                $q->where('is_society_officer', true)
                    ->orWhere('is_lsg_officer', true);
            })->pluck('id')->all(),
            'lsg_officers' => User::where('is_lsg_officer', true)->pluck('id')->all(),
            default => [],
        };

        // Exclude management-only accounts (admin/LSG) and the event creator.
        $filtered = User::whereIn('id', $ids)
            ->whereHas('role', fn ($r) => $r->whereNotIn('slug', ['admin', 'lsg_officer']))
            ->where('id', '!=', $creatorId)
            ->pluck('id')
            ->all();

        // If a society officer somehow hits this (shouldn’t via route), still restrict to their society on CEIT-wide.
        if ($requester->role?->slug === 'officer' && $event->audience === 'ceit_students') {
            $officerSocietyIds = $requester->societies()->pluck('societies.id');
            return User::whereIn('id', $filtered)
                ->whereHas('societies', fn ($q) => $q->whereIn('societies.id', $officerSocietyIds))
                ->pluck('id')
                ->all();
        }

        return $filtered;
    }

    protected function eventHasEnded(Event $event): bool
    {
        $end = $event->end_at ?? $event->start_at;
        return $end ? now()->gte($end) : false;
    }

    protected function authorizeLsg($user, Event $event): void
    {
        abort_unless($user->role?->slug === 'lsg_officer', 403);
        abort_unless(in_array($event->audience, ['ceit_students', 'all_officers'], true), 403);
    }
}
