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
        $selectedEventId = null;

        if ($request->filled('event_id')) {
            $candidateId = (int) $request->query('event_id');

            // Ensure the requested event is in the dropdown if the user can see it.
            $visibleCandidate = $this->queryVisibleEvents($user)->where('id', $candidateId)->first();
            if ($visibleCandidate) {
                $selectedEventId = $candidateId;
                if (! $events->pluck('id')->contains($candidateId)) {
                    $events->prepend($visibleCandidate);
                }
            }
        }

        return view('ai.insights', compact('events', 'selectedEventId'));
    }

    public function answerQuestion(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'mode' => ['required', Rule::in(['event', 'student', 'freeform'])],
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

    public function chat(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'mode' => ['required', Rule::in(['event', 'student', 'freeform'])],
            'event_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'question' => ['required', 'string', 'max:800'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reset' => ['nullable', 'boolean'],
        ]);

        // Conditional requirements to avoid 422 on freeform
        $request->validate([
            'event_id' => [Rule::requiredIf($data['mode'] === 'event'), 'nullable', 'integer'],
            'student_id' => [Rule::requiredIf($data['mode'] === 'student'), 'nullable', 'string', 'max:50'],
        ]);

        $sessionKey = null;
        $context = [];

        try {
            if ($data['mode'] === 'event') {
                $event = Event::findOrFail($data['event_id']);
                $this->authorizeEventAccess($user, $event);
                $context = $this->buildEventContext($event);
                $sessionKey = 'ai_chat.event.'.$event->id;
            } elseif ($data['mode'] === 'student') {
                $student = User::where('id_number', $data['student_id'])->firstOrFail();
                $context = $this->buildStudentContext($user, $student, $data['start_date'] ?? null, $data['end_date'] ?? null);
                $sessionKey = 'ai_chat.student.'.$student->id;
            } else {
                // freeform
                $sessionKey = 'ai_chat.freeform';
            }

            if ($sessionKey && $request->boolean('reset')) {
                session()->forget($sessionKey);
            }

            $history = $sessionKey ? session($sessionKey, []) : [];
            $system = [
                'role' => 'system',
                'content' => $data['mode'] === 'freeform'
                    ? 'You are a helpful assistant. Keep replies concise and in plain text (no bullets, no tables, no bold/italics).'
                    : 'You are an assistant that answers questions about CEIT attendance using only the provided JSON context. Keep replies concise and in plain text (no bullets, no tables, no bold/italics). Always use the latest context supplied with each user turn; do not rely on older context if it conflicts.',
            ];

            $messages = [$system];
            foreach ($history as $msg) {
                $messages[] = $msg;
            }
            if ($data['mode'] === 'freeform') {
                $messages[] = [
                    'role' => 'user',
                    'content' => $data['question'],
                ];
            } else {
                $messages[] = [
                    'role' => 'user',
                    'content' => "Fresh context (re-fetched this turn):\n".json_encode($context, JSON_PRETTY_PRINT)."\nUser question: ".$data['question'],
                ];
            }

            $answer = $this->ai->chat($messages);

            // Store trimmed history (last 12 messages max)
            $history[] = ['role' => 'user', 'content' => $data['question']];
            $history[] = ['role' => 'assistant', 'content' => $answer];
            $history = array_slice($history, -12);
            if ($sessionKey) {
                session([$sessionKey => $history]);
            }

            return response()->json([
                'answer' => $answer,
                'history' => $history,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    protected function buildEventContext(Event $event): array
    {
        $tz = config('app.timezone', 'UTC');
        $startLocal = optional($event->start_at)->timezone($tz);
        $endLocal = optional($event->end_at)->timezone($tz);

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
                'start_at_local' => $startLocal?->format('M d, Y g:i A'),
                'end_at_local' => $endLocal?->format('M d, Y g:i A'),
                'timezone' => $tz,
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
        $tz = config('app.timezone', 'UTC');
        $events = $this->queryVisibleEvents($requester)
            ->when($start, fn ($q) => $q->whereDate('start_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('start_at', '<=', $end))
            ->whereHas('attendanceRecords', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('id_number', $student->id_number)))
            ->withCount('attendanceRecords')
            ->get(['id', 'title', 'start_at', 'audience', 'society_id']);

        $eventDetails = $events->map(function ($ev) {
            $tzInner = config('app.timezone', 'UTC');
            return [
                'title' => $ev->title,
                'date_local' => optional($ev->start_at)->timezone($tzInner)?->format('M d, Y g:i A'),
                'timezone' => $tzInner,
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
