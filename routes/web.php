<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentDashboardController;

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
    Route::get('dashboard/student', StudentDashboardController::class)->name('student.dashboard');
    Route::view('dashboard/officer', 'dashboards.officer')->name('officer.dashboard');
    Route::view('dashboard/lsg', 'dashboards.lsg')->name('lsg.dashboard');
    Route::view('dashboard/admin', 'dashboards.admin')->name('admin.dashboard');

    Route::view('profile', 'profile')->name('profile');

    Route::middleware('role:officer,lsg_officer,admin')->group(function () {
        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::get('events/create', [EventController::class, 'create'])->name('events.create')->middleware('role:officer,lsg_officer,admin');
        Route::post('events', [EventController::class, 'store'])->name('events.store')->middleware('role:officer,lsg_officer,admin');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit')->middleware('role:officer,lsg_officer,admin');
        Route::put('events/{event}', [EventController::class, 'update'])->name('events.update')->middleware('role:officer,lsg_officer,admin');
        Route::patch('events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel')->middleware('role:officer,lsg_officer,admin');
        Route::get('events/{event}/attendance', [AttendanceController::class, 'show'])->name('events.attendance');
        Route::get('events/{event}/attendance/export', [AttendanceController::class, 'export'])->name('events.attendance.export')->middleware('role:officer,admin');
        Route::post('events/{event}/attendance', [AttendanceController::class, 'record'])->name('events.attendance.record')->middleware('role:officer,admin');
    });

    Route::get('events/upcoming', [EventController::class, 'index'])->name('events.upcoming'); // student view filtered by visibility
});

require __DIR__.'/auth.php';
