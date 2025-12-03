<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $societyId = $user->societies()->value('societies.id');
        $attendedEventIds = AttendanceRecord::where('user_id', $user->id)
            ->pluck('event_id')
            ->unique();
        $now = now();

        $upcomingClause = function ($q) use ($now) {
            $q->where(function ($w) use ($now) {
                $w->whereNull('end_at')->where('start_at', '>=', $now);
            })->orWhere(function ($w) use ($now) {
                $w->whereNotNull('end_at')->where('end_at', '>=', $now);
            });
        };

        $societyEvents = Event::with(['society', 'creator.role'])
            ->visibleToStudent($user)
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->where($upcomingClause)
            ->orderBy('start_at')
            ->take(6)
            ->get();

        $societyEventsAttended = $attendedEventIds->isEmpty()
            ? 0
            : Event::whereIn('id', $attendedEventIds)
                ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
                ->count();

        $ceitEvents = Event::with(['society', 'creator.role'])
            ->visibleToStudent($user)
            ->where(function ($q) {
                $q->whereNull('society_id')
                    ->orWhere('audience', 'ceit_students')
                    ->orWhere('audience', 'all_officers')
                    ->orWhere('audience', 'lsg_officers');
            })
            ->where($upcomingClause)
            ->orderBy('start_at')
            ->take(6)
            ->get();

        $ceitEventsAttended = $attendedEventIds->isEmpty()
            ? 0
            : Event::whereIn('id', $attendedEventIds)
                ->where(function ($q) {
                    $q->whereNull('society_id')
                        ->orWhere('audience', 'ceit_students')
                        ->orWhere('audience', 'all_officers')
                        ->orWhere('audience', 'lsg_officers');
                })
                ->count();

        return view('dashboards.student', [
            'societyEvents' => $societyEvents,
            'ceitEvents' => $ceitEvents,
            'societyEventsAttended' => $societyEventsAttended,
            'ceitEventsAttended' => $ceitEventsAttended,
        ]);
    }
}
