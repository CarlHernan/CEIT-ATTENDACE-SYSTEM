<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EventApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->slug;
        $societyIds = $user->societies()->pluck('societies.id')->toArray();

        $query = Event::with(['society', 'creator.role'])
            ->where('status', '!=', 'cancelled');

        $query = $this->applyVisibilityScope($query, $role, $societyIds);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($from = $request->date('from')) {
            $query->whereDate('start_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('start_at', '<=', $to);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $events = $query->orderBy('start_at', 'desc')->paginate($request->integer('per_page', 15));
        $lean = $request->boolean('lean', false);

        return response()->json([
            //'data' => $events->items(),
            'data' => collect($events->items())->map(fn ($event) => $this->transformEvent($event, $lean)),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    public function show(Request $request, Event $event)
    {
        $user = $request->user();
        $role = $user->role?->slug;
        $societyIds = $user->societies()->pluck('societies.id')->toArray();

        if (! $this->isVisibleToUser($event, $role, $societyIds)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $lean = $request->boolean('lean', false);
        return response()->json(['data' => $this->transformEvent($event->load('society'), $lean)]);
    }

    //return response()->json(['data' => $event->load('society')]);
    protected function applyVisibilityScope($query, ?string $role, array $societyIds)
    {
        return $query->where(function ($q) use ($role, $societyIds) {
            if ($role === 'officer') {
                $q->whereIn('society_id', $societyIds)
                    ->orWhere(function ($lsg) {
                        $lsg->whereNull('society_id')
                            ->whereIn('audience', ['ceit_students', 'all_officers']);
                    });
            } elseif ($role === 'lsg_officer') {
                $q->where(function ($w) {
                    $w->whereNull('society_id')
                        ->orWhere('is_ceit_wide', true)
                        ->orWhere('type', 'ceit');
                })
                ->orWhereHas('creator.role', fn ($role) => $role->where('slug', 'lsg_officer'));
            }
        });
    }

    protected function isVisibleToUser(Event $event, ?string $role, array $societyIds): bool
    {
        if ($role === 'officer') {
            $isSocietyEvent = in_array($event->society_id, $societyIds, true);
            $isCeitWideLsg = $event->society_id === null && in_array($event->audience, ['ceit_students', 'all_officers'], true);
            return $isSocietyEvent || $isCeitWideLsg;
        }

        if ($role === 'lsg_officer') {
            return $event->society_id === null
                || $event->is_ceit_wide
                || $event->type === 'ceit'
                || ($event->creator?->role?->slug === 'lsg_officer');
        }

        return false;
    }

    /**
     * Transform the event payload; lean=true returns only a minimal set of fields.
     */
    protected function transformEvent(Event $event, bool $lean = false): array
    {
        if ($lean) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_at' => $event->start_at,
                'end_at' => $event->end_at,
                'status' => $event->status,
                'runtime_status' => $event->runtime_status,
                'computed_status' => $event->computed_status,
                'location' => $event->location,
                'society' => $event->society ? [
                    'abbreviation' => $event->society->abbreviation,
                ] : null,
            ];
        }

        $arr = $event->toArray();
        $arr['computed_status'] = $event->computed_status;
        return $arr;
    }
}
