<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $societyId = $user->societies()->value('societies.id');

        $societyEvents = Event::with(['society', 'creator.role'])
            ->visibleToStudent($user)
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->whereDate('start_at', '>=', now()->subDays(7))
            ->orderBy('start_at')
            ->take(6)
            ->get();

        $ceitEvents = Event::with(['society', 'creator.role'])
            ->visibleToStudent($user)
            ->where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhere('audience', 'ceit_students')
                    ->orWhere('audience', 'all_officers')
                    ->orWhere('audience', 'lsg_officers');
            })
            ->whereDate('start_at', '>=', now()->subDays(7))
            ->orderBy('start_at')
            ->take(6)
            ->get();

        return view('dashboards.student', [
            'societyEvents' => $societyEvents,
            'ceitEvents' => $ceitEvents,
        ]);
    }
}
