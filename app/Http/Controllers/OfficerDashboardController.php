<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficerDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $societyIds = $user->societies()->pluck('societies.id');

        // Events they manage (their society)
        $manageEvents = Event::with('society')
            ->whereIn('society_id', $societyIds)
            ->orderBy('start_at', 'desc')
            ->take(6)
            ->get();

        // CEIT-wide awareness (LSG-created ceit_students events)
        $ceitAwareness = Event::with(['society', 'creator.role'])
            ->where('audience', 'ceit_students')
            ->where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhereHas('creator.role', fn ($r) => $r->where('slug', 'lsg_officer'));
            })
            ->orderBy('start_at', 'desc')
            ->take(4)
            ->get();

        return view('dashboards.officer', compact('manageEvents', 'ceitAwareness'));
    }
}
