<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $query = Event::with(['society', 'creator'])
            ->orderBy('start_at', 'desc');

        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            $query->whereIn('society_id', $societyIds);
        }

        if ($role === 'lsg_officer') {
            $query->whereIn('type', ['ceit', 'lsg']);
        }

        if ($role === 'student') {
            $societyIds = $user->societies()->pluck('societies.id');
            $isSocOfficer = $user->is_society_officer;
            $isLsgOfficer = $user->is_lsg_officer;
            $year = $user->year_level;

            $query->where(function ($sub) use ($societyIds, $isSocOfficer, $isLsgOfficer, $year) {
                $sub->where(function ($q) use ($societyIds) {
                    $q->whereIn('audience', ['all', 'society'])
                        ->whereIn('society_id', $societyIds);
                })
                ->orWhere(function ($q) use ($societyIds, $year) {
                    $q->where('audience', 'year_specific')
                        ->whereIn('society_id', $societyIds)
                        ->where(function ($w) use ($year) {
                            $w->whereRaw('FIND_IN_SET(?, audience_years)', [$year]);
                        });
                })
                ->orWhere(function ($q) use ($societyIds, $isSocOfficer) {
                    $q->where('audience', 'society_officers')
                        ->whereIn('society_id', $societyIds)
                        ->whereRaw($isSocOfficer ? '1=1' : '0=1');
                })
                ->orWhere(function ($q) use ($isLsgOfficer) {
                    $q->where('audience', 'lsg_officers')
                        ->whereRaw($isLsgOfficer ? '1=1' : '0=1');
                })
                ->orWhere(function ($q) use ($isSocOfficer, $isLsgOfficer) {
                    $q->where('audience', 'lsg_and_society_officers')
                        ->whereRaw(($isSocOfficer || $isLsgOfficer) ? '1=1' : '0=1');
                })
                ->orWhere(function ($q) use ($isSocOfficer, $isLsgOfficer) {
                    // generic officers-only: allow if they flagged themselves as an officer (society or LSG)
                    $q->where('audience', 'officers_only')
                        ->whereRaw(($isSocOfficer || $isLsgOfficer) ? '1=1' : '0=1');
                })
                ->orWhere(function ($q) {
                    $q->where('audience', 'all')->where('is_ceit_wide', true);
                });
            })->whereIn('visibility', ['students', 'all']);
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

        $templates = ['GA', 'Meeting', 'Seminar', 'Formal Event'];
        $types = ['society', 'ceit', 'lsg', 'meeting'];
        $audiences = ['society', 'year_specific', 'all', 'officers_only', 'society_officers', 'lsg_officers', 'lsg_and_society_officers'];

        $defaultSocietyId = $societies->first()?->id;

        return view('events.create', compact('societies', 'templates', 'types', 'audiences', 'role', 'defaultSocietyId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $societyIds = $role === 'officer'
            ? $user->societies()->pluck('societies.id')->toArray()
            : Society::pluck('id')->toArray();
        $fallbackSocietyId = $societyIds[0] ?? null;

        // LSG events are CEIT-wide; auto-assign society_id and flags
        if ($role === 'lsg_officer') {
            $request->merge([
                'society_id' => $fallbackSocietyId,
                'is_ceit_wide' => true,
                'type' => 'ceit',
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'attendance_mode' => ['required', 'in:qr,manual,hybrid'],
            'society_id' => ['required', 'integer', 'in:'.implode(',', $societyIds)],
            'is_ceit_wide' => ['boolean'],
            'type' => ['required', 'string'],
            'template' => ['nullable', 'string', 'max:100'],
            'audience' => ['required', 'string', 'in:society,year_specific,all,officers_only,society_officers,lsg_officers,lsg_and_society_officers'],
            'audience_years' => ['nullable', 'string', 'max:50'],
            'require_timeout' => ['boolean'],
            'visibility' => ['required', 'string', 'in:students,officers,all'],
        ]);

        $validated['created_by'] = $user->id;
        $validated['society_id'] = $request->input('society_id', $fallbackSocietyId);
        $validated['is_ceit_wide'] = $request->boolean('is_ceit_wide');
        $validated['require_timeout'] = $request->boolean('require_timeout');
        $validated['status'] = 'active';

        $event = Event::create($validated);

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
        $templates = ['GA', 'Meeting', 'Seminar', 'Formal Event'];
        $types = ['society', 'ceit', 'lsg', 'meeting'];
        $audiences = ['society', 'year_specific', 'all', 'officers_only', 'society_officers', 'lsg_officers', 'lsg_and_society_officers'];

        $defaultSocietyId = $societies->first()?->id;

        return view('events.edit', compact('event', 'societies', 'templates', 'types', 'audiences', 'role', 'defaultSocietyId'));
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
        $fallbackSocietyId = $societyIds[0] ?? null;

        if ($role === 'lsg_officer') {
            $request->merge([
                'society_id' => $fallbackSocietyId,
                'is_ceit_wide' => true,
                'type' => 'ceit',
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'attendance_mode' => ['required', 'in:qr,manual,hybrid'],
            'society_id' => ['required', 'integer', 'in:'.implode(',', $societyIds)],
            'is_ceit_wide' => ['boolean'],
            'type' => ['required', 'string'],
            'template' => ['nullable', 'string', 'max:100'],
            'audience' => ['required', 'string', 'in:society,year_specific,all,officers_only,society_officers,lsg_officers,lsg_and_society_officers'],
            'audience_years' => ['nullable', 'string', 'max:50'],
            'require_timeout' => ['boolean'],
            'visibility' => ['required', 'string', 'in:students,officers,all'],
        ]);

        $validated['is_ceit_wide'] = $request->boolean('is_ceit_wide');
        $validated['require_timeout'] = $request->boolean('require_timeout');
        $validated['society_id'] = $request->input('society_id', $fallbackSocietyId);

        $event->update($validated);

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

        if ($role === 'officer') {
            $societyIds = $user->societies()->pluck('societies.id');
            abort_unless($societyIds->contains($event->society_id), 403);
        }

        return view('events.show', compact('event'));
    }

    /**
     * Ensure the user can manage the event (edit/update/cancel).
     */
    protected function authorizeAccess($user, Event $event): void
    {
        $role = $user->role?->slug;

        if (in_array($role, ['admin', 'lsg_officer'], true)) {
            return;
        }

        if ($role === 'officer') {
            // Officers cannot manage events created by LSG/admin
            $creatorRole = $event->creator?->role?->slug;
            if (in_array($creatorRole, ['lsg_officer', 'admin'], true)) {
                abort(403);
            }
            $societyIds = $user->societies()->pluck('societies.id');
            abort_unless($societyIds->contains($event->society_id), 403);
            return;
        }

        abort(403);
    }
}
