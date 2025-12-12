<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\OfficerDashboardController;
use App\Http\Controllers\LsgDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\EventReportController;
use App\Http\Controllers\LsgAnalyticsController;

Route::get('/', function () {
    $user = auth()->user();

    if ($user) {
        $role = $user->role?->slug;

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'lsg_officer' => redirect()->route('lsg.dashboard'),
            'officer' => redirect()->route('officer.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard/student', StudentDashboardController::class)->name('student.dashboard')->middleware('role:student');
    Route::get('dashboard/officer', OfficerDashboardController::class)->name('officer.dashboard')->middleware('role:officer');
    Route::get('dashboard/lsg', LsgDashboardController::class)->name('lsg.dashboard')->middleware('role:lsg_officer');
    Route::get('dashboard/admin', [AdminDashboardController::class, '__invoke'])->name('admin.dashboard')->middleware('role:admin');

    Route::view('profile', 'profile')->name('profile');

    // Events listing (scope param: upcoming/ended), visibility enforced in controller
    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/upcoming', [EventController::class, 'index'])->name('events.upcoming');

    // Event show (all authenticated; controller enforces visibility)
    Route::get('events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');

    Route::middleware('role:officer,lsg_officer,admin')->group(function () {
        Route::get('events/create', [EventController::class, 'create'])->name('events.create')->middleware('role:officer,lsg_officer,admin');
        Route::post('events', [EventController::class, 'store'])->name('events.store')->middleware('role:officer,lsg_officer,admin');
        Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit')->middleware('role:officer,lsg_officer,admin');
        Route::put('events/{event}', [EventController::class, 'update'])->name('events.update')->middleware('role:officer,lsg_officer,admin');
        Route::patch('events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel')->middleware('role:officer,lsg_officer,admin');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy')->middleware('role:officer,lsg_officer,admin');
        Route::get('events/{event}/attendance', [AttendanceController::class, 'show'])->name('events.attendance');
        Route::get('events/{event}/attendance/export', [AttendanceController::class, 'export'])->name('events.attendance.export')->middleware('role:officer,lsg_officer,admin');
        Route::post('events/{event}/attendance', [AttendanceController::class, 'record'])->name('events.attendance.record')->middleware('role:officer,lsg_officer,admin');

        // Reporting (society officer)
        Route::get('events/{event}/report', [EventReportController::class, 'show'])->name('events.report')->middleware('role:officer');
        Route::get('events/{event}/report/export', [EventReportController::class, 'export'])->name('events.report.export')->middleware('role:officer');
        Route::get('events/{event}/report/export-absent', [EventReportController::class, 'exportAbsent'])->name('events.report.export-absent')->middleware('role:officer');

        // CEIT-LSG analytics
        Route::get('lsg/events/{event}/analytics', [LsgAnalyticsController::class, 'show'])->name('lsg.analytics')->middleware('role:lsg_officer');
        Route::get('lsg/events/{event}/analytics/export', [LsgAnalyticsController::class, 'export'])->name('lsg.analytics.export')->middleware('role:lsg_officer');
        Route::get('lsg/events/{event}/analytics/export-absent', [LsgAnalyticsController::class, 'exportAbsent'])->name('lsg.analytics.export-absent')->middleware('role:lsg_officer');

        // AI
        Route::post('events/{event}/ai-summary', [AiController::class, 'generateSummary'])->name('events.ai.summary')->middleware('role:officer,lsg_officer,admin');
        Route::get('ai/attendance-insights', [AiController::class, 'insightsPage'])->name('ai.insights')->middleware('role:officer,lsg_officer,admin');
        Route::post('ai/attendance-question', [AiController::class, 'answerQuestion'])->name('ai.insights.ask')->middleware('role:officer,lsg_officer,admin');
        Route::post('ai/attendance-chat', [AiController::class, 'chat'])->name('ai.insights.chat')->middleware('role:officer,lsg_officer,admin');
    });
});

require __DIR__.'/auth.php';
