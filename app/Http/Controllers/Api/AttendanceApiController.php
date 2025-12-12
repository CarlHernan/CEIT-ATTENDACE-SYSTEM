<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function index(Request $request, Event $event)
    {
        if (! $this->canManageEvent($request->user(), $event)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $query = AttendanceRecord::with(['user'])
            ->where('event_id', $event->id);

        if ($course = $request->string('course')->toString()) {
            $query->whereHas('user', fn ($q) => $q->where('course', $course));
        }

        if ($year = $request->string('year_level')->toString()) {
            $query->whereHas('user', fn ($q) => $q->where('year_level', $year));
        }

        if ($method = $request->string('method')->toString()) {
            $query->where('method', $method);
        }

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        $records = $query->orderByDesc('time_in')->paginate($request->integer('per_page', 20));
        $lean = $request->boolean('lean', false);

        return response()->json([
            'data' => collect($records->items())->map(fn ($record) => $this->transformAttendance($record, $lean)),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        if (! $this->canManageEvent($user, $event)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($event->status === 'cancelled') {
            return response()->json(['message' => 'Event is cancelled.'], 422);
        }

        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'direction' => ['required', 'in:in,out'],
            'method' => ['required', 'in:qr,manual,hybrid'],
        ]);

        $attendee = User::where('qr_raw_text', $data['identifier'])
            ->orWhere('id_number', $data['identifier'])
            ->first();

        if (! $attendee) {
            return response()->json([
                'message' => 'Student not found.',
                'data' => ['identifier' => $data['identifier']],
            ], 404);
        }

        $record = AttendanceRecord::firstOrNew([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
        ]);

        $now = Carbon::now();
        $alreadyRecorded = false;

        if ($data['direction'] === 'in') {
            if ($record->time_in) {
                $alreadyRecorded = true;
            } else {
                $record->time_in = $now;
            }
        } elseif ($data['direction'] === 'out') {
            if ($record->time_out) {
                $alreadyRecorded = true;
            } else {
                $record->time_out = $now;
            }
        }

        if (! $record->exists || $record->isDirty()) {
            $record->method = $data['method'];
            $record->recorded_by = $user->id;
            $record->save();
        }

        return response()->json([
            'data' => [
                'event_id' => $event->id,
                'user' => $this->transformUserLean($attendee),
                'time_in' => optional($record->time_in)->toDateTimeString(),
                'time_out' => optional($record->time_out)->toDateTimeString(),
                'already_recorded' => $alreadyRecorded,
                'direction' => $data['direction'],
            ],
        ]);
    }

    public function stats(Request $request, Event $event)
    {
        if (! $this->canManageEvent($request->user(), $event)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $records = AttendanceRecord::where('event_id', $event->id);

        $total = (clone $records)->count();
        $timeIn = (clone $records)->whereNotNull('time_in')->count();
        $timeOut = (clone $records)->whereNotNull('time_out')->count();

        $byCourse = (clone $records)
            ->join('users', 'users.id', '=', 'attendance_records.user_id')
            ->selectRaw('users.course, count(*) as count')
            ->groupBy('users.course')
            ->pluck('count', 'course');

        $byYear = (clone $records)
            ->join('users', 'users.id', '=', 'attendance_records.user_id')
            ->selectRaw('users.year_level, count(*) as count')
            ->groupBy('users.year_level')
            ->pluck('count', 'year_level');

        return response()->json([
            'data' => [
                'totals' => [
                    'records' => $total,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                ],
                'by_course' => $byCourse,
                'by_year_level' => $byYear,
            ],
        ]);
    }

    protected function canManageEvent(User $user, Event $event): bool
    {
        $role = $user->role?->slug;
        $societyIds = $user->societies()->pluck('societies.id')->toArray();

        if ($role === 'officer') {
            return in_array($event->society_id, $societyIds, true)
                || (bool) $event->is_ceit_wide
                || ($event->society_id === null && in_array($event->audience, ['ceit_students', 'all_officers'], true))
                || $event->type === 'ceit';
        }

        if ($role === 'lsg_officer') {
            return in_array($event->type, ['ceit', 'lsg'], true)
                || (bool) $event->is_ceit_wide
                || $event->society_id === null;
        }

        return false;
    }

    /**
     * Transform attendance record; lean=true only returns minimal user info.
     */
    protected function transformAttendance(AttendanceRecord $record, bool $lean = false): array
    {
        if (! $lean) {
            $arr = $record->toArray();
            if ($record->relationLoaded('user') && $record->user) {
                $arr['user']['computed'] = $this->transformUserLean($record->user);
            }
            return $arr;
        }

        return [
            'id' => $record->id,
            'event_id' => $record->event_id,
            'time_in' => optional($record->time_in)->toDateTimeString(),
            'time_out' => optional($record->time_out)->toDateTimeString(),
            'method' => $record->method,
            'user' => $record->relationLoaded('user') && $record->user
                ? $this->transformUserLean($record->user)
                : null,
        ];
    }

    /**
     * Minimal user shape for attendance responses.
     */
    protected function transformUserLean(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'id_number' => $user->id_number,
            'course' => $user->course,
            'section' => $user->section,
            'year_level' => $user->year_level,
            'qr_raw_text' => $user->qr_raw_text,
            'role_id' => $user->role_id,
            'is_society_officer' => (bool) $user->is_society_officer,
            'is_lsg_officer' => (bool) $user->is_lsg_officer,
        ];
    }
}
