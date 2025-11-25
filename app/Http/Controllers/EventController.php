<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $query = Event::with(['society', 'creator.role'])
            ->orderBy('start_at', 'desc');

        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            $query->where(function ($q) use ($societyIds) {
                $q->whereIn('society_id', $societyIds)
                    ->orWhere(function ($lsg) {
                        $lsg->whereNull('society_id')
                            ->where('audience', 'ceit_students');
                    });
            });
        } elseif ($role === 'lsg_officer') {
            $query->where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhereHas('creator.role', fn ($role) => $role->where('slug', 'lsg_officer'));
            });
        } elseif ($role === 'student') {
            $query->visibleToStudent($user);
        }

        $events = $query->paginate(10);

        return view('events.index', compact('events'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $societies = match ($role) {
            'officer' => $user->societies()->get(),
            default => Society::orderBy('abbreviation')->get(),
        };

        $audiences = $role === 'lsg_officer'
            ? ['ceit_students', 'lsg_officers', 'all_officers', 'others']
            : ['society_members', 'society_officers', 'others'];

        $defaultSocietyId = $societies->first()?->id;

        return view('events.create', compact('societies', 'audiences', 'role', 'defaultSocietyId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $societyIds = $role === 'officer'
            ? $user->societies()->pluck('societies.id')->toArray()
            : Society::pluck('id')->toArray();

        $audienceOptions = $role === 'lsg_officer'
            ? ['ceit_students', 'lsg_officers', 'all_officers', 'others']
            : ['society_members', 'society_officers', 'others'];

        $request->merge([
            'attendance_mode' => $request->input('attendance_mode', 'hybrid'),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'attendance_mode' => ['required', 'in:qr,manual,hybrid'],
            'society_id' => $role === 'lsg_officer'
                ? ['nullable']
                : ['required', 'integer', Rule::in($societyIds)],
            'audience' => ['required', Rule::in($audienceOptions)],
            'audience_notes' => [Rule::requiredIf(fn () => $request->input('audience') === 'others'), 'nullable', 'string', 'max:255'],
        ]);

        $payload = [
            ...$validated,
            'society_id' => $role === 'lsg_officer' ? null : $validated['society_id'],
            'audience_notes' => $validated['audience'] === 'others' ? ($validated['audience_notes'] ?? null) : null,
            'created_by' => $user->id,
            'status' => 'active',
        ];

        $event = Event::create($payload);

        return redirect()->route('events.show', $event)->with('status', 'Event created.');
    }

    public function edit(Request $request, Event $event): View
    {
        $user = $request->user();
        $this->authorizeAccess($user, $event);

        $role = $user->role?->slug;
        $societies = match ($role) {
            'officer' => $user->societies()->get(),
            default => Society::orderBy('abbreviation')->get(),
        };
        $audiences = ($role === 'lsg_officer' || $event->society_id === null)
            ? ['ceit_students', 'lsg_officers', 'all_officers', 'others']
            : ['society_members', 'society_officers', 'others'];

        $defaultSocietyId = $societies->first()?->id;

        return view('events.edit', compact('event', 'societies', 'audiences', 'role', 'defaultSocietyId'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $event);

        $role = $user->role?->slug;
        $societyIds = match ($role) {
            'officer' => $user->societies()->pluck('societies.id')->toArray(),
            default => Society::pluck('id')->toArray(),
        };

        $isLsgContext = $role === 'lsg_officer'
            || $event->society_id === null
            || in_array($event->audience, ['ceit_students', 'lsg_officers', 'all_officers'], true);

        $audienceOptions = $isLsgContext
            ? ['ceit_students', 'lsg_officers', 'all_officers', 'others']
            : ['society_members', 'society_officers', 'others'];

        $request->merge([
            'attendance_mode' => $request->input('attendance_mode', 'hybrid'),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'attendance_mode' => ['required', 'in:qr,manual,hybrid'],
            'society_id' => $isLsgContext
                ? ['nullable', Rule::in($societyIds)]
                : ['required', 'integer', Rule::in($societyIds)],
            'audience' => ['required', Rule::in($audienceOptions)],
            'audience_notes' => [Rule::requiredIf(fn () => $request->input('audience') === 'others'), 'nullable', 'string', 'max:255'],
        ]);

        $payload = [
            ...$validated,
            'society_id' => $isLsgContext ? null : $validated['society_id'],
            'audience_notes' => $validated['audience'] === 'others' ? ($validated['audience_notes'] ?? null) : null,
        ];

        $event->update($payload);

        return redirect()->route('events.show', $event)->with('status', 'Event updated.');
    }

    public function cancel(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $event);

        $event->update(['status' => 'cancelled']);

        return redirect()->route('events.show', $event)->with('status', 'Event cancelled.');
    }

    public function show(Request $request, Event $event): View
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $event->loadMissing(['society', 'creator.role']);

        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            $isOwnSocietyEvent = $event->society_id && $societyIds->contains($event->society_id);
            $isCeitWideLsgEvent = in_array($event->audience, ['ceit_students', 'all_officers'], true)
                && $event->creator?->role?->slug === 'lsg_officer';
            abort_unless($isOwnSocietyEvent || $isCeitWideLsgEvent, 403);
        }

        return view('events.show', compact('event'));
    }

    /**
     * Ensure the user can manage the event (edit/update/cancel).
     */
    protected function authorizeAccess($user, Event $event): void
    {
        $role = $user->role?->slug;

        if ($role === 'admin') {
            return;
        }

        if ($role === 'lsg_officer') {
            abort_unless($event->society_id === null || $event->creator?->role?->slug === 'lsg_officer', 403);
            return;
        }

        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            abort_unless($societyIds->contains($event->society_id), 403);
            return;
        }

        abort(403);
    }
}
