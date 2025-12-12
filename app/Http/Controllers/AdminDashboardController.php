<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Society;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $now = Carbon::now();

        // Overall Statistics
        $totalUsers = User::count();
        $totalStudents = User::whereHas('role', fn($q) => $q->where('slug', 'student'))->count();
        $totalOfficers = User::where(fn($q) => $q->where('is_society_officer', true)->orWhere('is_lsg_officer', true))->count();
        $totalSocieties = Society::count();

        // Event Statistics
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('start_at', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->limit(5)
            ->get();
        $activeEventsCount = Event::where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->where('status', '!=', 'cancelled')
            ->count();
        $eventsThisMonth = Event::whereMonth('start_at', $now->month)
            ->whereYear('start_at', $now->year)
            ->count();

        // Attendance Statistics
        $totalAttendanceRecords = AttendanceRecord::count();
        $attendanceToday = AttendanceRecord::whereDate('time_in', $now->toDateString())->count();
        $attendanceThisWeek = AttendanceRecord::whereBetween('time_in', [
            $now->startOfWeek()->toDateString(),
            $now->endOfWeek()->toDateString()
        ])->count();

        // Calculate overall attendance rate (users who checked in vs total expected)
        $recentEventsWithAttendance = Event::where('start_at', '<=', $now)
            ->where('start_at', '>', $now->copy()->subDays(30))
            ->withCount('attendanceRecords')
            ->get();

        $totalExpectedAttendees = $recentEventsWithAttendance->sum(function($event) use ($totalStudents) {
            // Simplified: assume all students are expected for CEIT-wide, society member count otherwise
            return $event->is_ceit_wide ? $totalStudents : ($event->society?->members()->count() ?? 0);
        });

        $totalActualAttendees = $recentEventsWithAttendance->sum('attendance_records_count');
        $attendanceRate = $totalExpectedAttendees > 0
            ? round(($totalActualAttendees / $totalExpectedAttendees) * 100, 1)
            : 0;

        // Recent Activity
        $recentEvents = Event::with(['society', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Society Activity
        $societyStats = Society::withCount(['members'])
            ->get()
            ->map(function($society) use ($now) {
                $eventCount = Event::where('society_id', $society->id)
                    ->whereMonth('start_at', $now->month)
                    ->count();
                return [
                    'society' => $society,
                    'members_count' => $society->members_count,
                    'events_this_month' => $eventCount,
                ];
            });

        // Compliance Alerts
        $alerts = [];

        // Events without timeout records
        $eventsRequiringTimeout = Event::where('require_timeout', true)
            ->where('end_at', '<', $now)
            ->where('end_at', '>', $now->copy()->subDays(7))
            ->whereHas('attendanceRecords', function($q) {
                $q->whereNull('time_out');
            })
            ->count();

        if ($eventsRequiringTimeout > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$eventsRequiringTimeout} recent event(s) have incomplete timeout records",
                'action' => 'Review attendance records',
            ];
        }

        // Students with no attendance in last 30 days
        $inactiveStudents = User::whereHas('role', fn($q) => $q->where('slug', 'student'))
            ->whereDoesntHave('attendanceRecords', function($q) use ($now) {
                $q->where('time_in', '>', $now->copy()->subDays(30));
            })
            ->count();

        if ($inactiveStudents > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$inactiveStudents} student(s) have not attended any events in the last 30 days",
                'action' => 'View inactive students',
            ];
        }

        // Events created in last 24 hours
        $newEvents = Event::where('created_at', '>', $now->copy()->subDay())->count();
        if ($newEvents > 0) {
            $alerts[] = [
                'type' => 'success',
                'message' => "{$newEvents} new event(s) created in the last 24 hours",
                'action' => 'View events',
            ];
        }

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalStudents',
            'totalOfficers',
            'totalSocieties',
            'totalEvents',
            'upcomingEvents',
            'activeEventsCount',
            'eventsThisMonth',
            'totalAttendanceRecords',
            'attendanceToday',
            'attendanceThisWeek',
            'attendanceRate',
            'recentEvents',
            'societyStats',
            'alerts'
        ));
    }
}

