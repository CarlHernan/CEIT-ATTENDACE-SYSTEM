<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\User;
use App\Services\OllamaAiService;
use App\Support\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AiController extends Controller
{
    protected OllamaAiService $ai;

    public function __construct(OllamaAiService $ai)
    {
        $this->ai = $ai;
    }

    public function generateSummary(Request $request, Event $event)
    {
        $user = $request->user();
        $this->authorizeEventAccess($user, $event);

        $context = $this->buildEventContext($event);

        try {
            $text = $this->ai->generateEventSummary($context);
            return response()->json(['summary' => $text]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function insightsPage(Request $request): View
    {
        $user = $request->user();
        $events = $this->queryVisibleEvents($user)->orderBy('start_at', 'desc')->limit(20)->get();

        return view('ai.insights', compact('events'));
    }

    public function answerQuestion(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'mode' => ['required', Rule::in(['event', 'student'])],
            'event_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'question' => ['required', 'string', 'max:800'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            if ($data['mode'] === 'event') {
                $event = Event::findOrFail($data['event_id']);
                $this->authorizeEventAccess($user, $event);
                $context = $this->buildEventContext($event);
                $answer = $this->ai->answerAttendanceQuestion($data['question'], $context, 'event');
            } else {
                $student = User::where('id_number', $data['student_id'])->firstOrFail();
                $context = $this->buildStudentContext($user, $student, $data['start_date'] ?? null, $data['end_date'] ?? null);
                $answer = $this->ai->answerAttendanceQuestion($data['question'], $context, 'student');
            }
            return response()->json(['answer' => $answer]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    protected function buildEventContext(Event $event): array
    {
        $records = AttendanceRecord::with(['user.societies'])
            ->where('event_id', $event->id)
            ->get();

        $byStatus = ['present' => 0, 'late' => 0, 'no_timeout' => 0, 'left_early' => 0];
        $byYear = [];
        $byCourse = [];
        $bySociety = [];
        $attendees = [];

        foreach ($records as $record) {
            $status = AttendanceStatus::for($record, $event);
            if ($status === 'Present') $byStatus['present']++;
            if ($status === 'Late') $byStatus['late']++;
            if ($status === 'No time-out') $byStatus['no_timeout']++;
            if ($status === 'Left early') $byStatus['left_early']++;

            $u = $record->user;
            if ($u) {
                $byYear[$u->year_level] = ($byYear[$u->year_level] ?? 0) + 1;
                $byCourse[$u->course] = ($byCourse[$u->course] ?? 0) + 1;
                $soc = $u->societies->first();
                if ($soc) {
                    $bySociety[$soc->abbreviation] = ($bySociety[$soc->abbreviation] ?? 0) + 1;
                }

                $attendees[] = [
                    'id_number' => $u->id_number,
                    'name' => $u->name,
                    'year_level' => $u->year_level,
                    'course' => $u->course,
                    'section' => $u->section,
                    'society' => $soc?->abbreviation,
                    'status' => $status,
                ];
            }
        }

        return [
            'event' => [
                'title' => $event->title,
                'description' => $event->description,
                'start_at' => $event->start_at,
                'end_at' => $event->end_at,
                'organizer' => $event->society?->abbreviation ?? 'CEIT-LSG',
                'attendance_mode' => $event->attendance_mode,
                'audience' => $event->audience,
            ],
            'stats' => [
                'total_attendees' => $records->count(),
                'present' => $byStatus['present'],
                'late' => $byStatus['late'],
                'no_timeout' => $byStatus['no_timeout'],
                'left_early' => $byStatus['left_early'],
                'by_year_level' => $byYear,
                'by_course' => $byCourse,
                'by_society' => $bySociety,
            ],
            'attendees' => $attendees,
        ];
    }

    protected function buildStudentContext($requester, User $student, ?string $start = null, ?string $end = null): array
    {
        $events = $this->queryVisibleEvents($requester)
            ->when($start, fn ($q) => $q->whereDate('start_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('start_at', '<=', $end))
            ->whereHas('attendanceRecords', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('id_number', $student->id_number)))
            ->withCount('attendanceRecords')
            ->get(['id', 'title', 'start_at', 'audience', 'society_id']);

        $eventDetails = $events->map(function ($ev) {
            return [
                'title' => $ev->title,
                'date' => $ev->start_at,
                'audience' => $ev->audience,
                'society' => $ev->society?->abbreviation ?? null,
                'attendance_count' => $ev->attendance_records_count,
            ];
        });

        return [
            'student' => [
                'id_number' => $student->id_number,
                'name' => $student->name,
                'course' => $student->course,
                'year_level' => $student->year_level,
                'society' => $student->societies()->pluck('abbreviation')->first(),
            ],
            'events_attended' => $eventDetails,
        ];
    }

    protected function queryVisibleEvents($user)
    {
        $role = $user->role?->slug;
        if ($role === 'admin') {
            return Event::query();
        }
        if ($role === 'lsg_officer') {
            return Event::where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhere('audience', 'ceit_students');
            });
        }
        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            return Event::where(function ($q) use ($societyIds) {
                $q->whereIn('society_id', $societyIds)
                    ->orWhere('audience', 'ceit_students');
            });
        }
        abort(403);
    }

    protected function authorizeEventAccess($user, Event $event): void
    {
        $role = $user->role?->slug;
        if ($role === 'admin') {
            return;
        }
        if ($role === 'lsg_officer') {
            abort_unless($event->society_id === null || $event->audience === 'ceit_students' || $event->creator?->role?->slug === 'lsg_officer', 403);
            return;
        }
        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            $isOwn = $event->society_id && $societyIds->contains($event->society_id);
            $isCeitWide = $event->audience === 'ceit_students';
            abort_unless($isOwn || $isCeitWide, 403);
            return;
        }
        abort(403);
    }
}
