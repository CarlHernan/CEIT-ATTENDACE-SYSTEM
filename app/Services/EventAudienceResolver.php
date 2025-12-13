<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;

class EventAudienceResolver
{
    /**
     * Resolve recipient emails using the same audience logic as reporting,
     * excluding admin/LSG role accounts and the event creator.
     */
    public function resolve(Event $event, ?User $requester = null): Collection
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

        $query = User::whereIn('id', $ids)
            ->whereHas('role', fn ($r) => $r->whereNotIn('slug', ['admin', 'lsg_officer']))
            ->where('id', '!=', $creatorId)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        // If a society officer is requesting a CEIT-wide event, restrict to their society.
        if ($requester && $requester->role?->slug === 'officer' && $event->audience === 'ceit_students') {
            $officerSocietyIds = $requester->societies()->pluck('societies.id');
            $query->whereHas('societies', fn ($q) => $q->whereIn('societies.id', $officerSocietyIds));
        }

        return $query->pluck('email')->unique()->values();
    }
}
