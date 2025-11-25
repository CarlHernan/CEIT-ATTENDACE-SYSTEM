<?php

namespace App\Support;

use App\Models\AttendanceRecord;
use App\Models\Event;
use Illuminate\Support\Carbon;

class AttendanceStatus
{
    /**
     * Compute a human-readable status for an attendance record.
     *
     * Rules:
     * - If no time_in: Absent (handled separately, but kept for completeness)
     * - Present if time_in <= start_at + late_threshold_minutes
     * - Late if time_in > start_at + late_threshold_minutes
     * - No time-out if time_in exists, no time_out, and event already ended
     * - Left early if time_out exists and event.end_at exists and time_out < end_at
     */
    public static function for(AttendanceRecord $record, Event $event): string
    {
        $start = $event->start_at instanceof Carbon ? $event->start_at : Carbon::parse($event->start_at);
        $end = $event->end_at ? ($event->end_at instanceof Carbon ? $event->end_at : Carbon::parse($event->end_at)) : null;
        $lateCutoff = $start->copy()->addMinutes((int) ($event->late_threshold_minutes ?? 0));

        if (! $record->time_in) {
            return 'Absent';
        }

        $timeIn = $record->time_in instanceof Carbon ? $record->time_in : Carbon::parse($record->time_in);
        $timeOut = $record->time_out ? ($record->time_out instanceof Carbon ? $record->time_out : Carbon::parse($record->time_out)) : null;

        if (! $timeOut && $end && now()->greaterThan($end)) {
            return 'No time-out';
        }

        if ($timeOut && $end && $timeOut->lt($end)) {
            return 'Left early';
        }

        return $timeIn->lte($lateCutoff) ? 'Present' : 'Late';
    }
}
