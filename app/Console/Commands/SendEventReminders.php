<?php

namespace App\Console\Commands;

use App\Jobs\SendEventEmailJob;
use App\Models\Event;
use App\Models\EventReminderLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Dispatch reminder emails for events starting soon.';

    public function handle(): int
    {
        $now = Carbon::now();
        $offsets = [
            '24h' => 24,
            '1h' => 1,
        ];

        foreach ($offsets as $label => $hours) {
            $target = $now->copy()->addHours($hours);
            $startWindow = $target->copy()->startOfMinute();
            $endWindow = $target->copy()->endOfMinute();

            Event::where('status', '!=', 'cancelled')
                ->whereNotNull('start_at')
                ->whereBetween('start_at', [$startWindow, $endWindow])
                ->whereDoesntHave('eventReminderLogs', fn ($q) => $q->where('reminder_type', $label))
                ->chunkById(50, function ($events) use ($label) {
                    foreach ($events as $event) {
                        // Double-check to avoid duplicate dispatch when chunks overlap.
                        $alreadyQueued = EventReminderLog::where('event_id', $event->id)
                            ->where('reminder_type', $label)
                            ->exists();

                        if ($alreadyQueued) {
                            continue;
                        }

                        SendEventEmailJob::dispatch($event->id, 'reminder', $label);
                    }
                });
        }

        return Command::SUCCESS;
    }
}
