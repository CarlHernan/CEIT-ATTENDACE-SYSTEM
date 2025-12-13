<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventReminderLog;
use App\Services\EventAudienceResolver;
use App\Services\EventEmailGeneratorService;
use App\Services\N8nWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEventEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $eventId,
        public string $type, // created | reminder
        public ?string $reminderType = null // 24h | 1h
    ) {
    }

    public function handle(
        EventEmailGeneratorService $generator,
        EventAudienceResolver $audienceResolver,
        N8nWebhookService $n8n
    ): void {
        $event = Event::with(['society', 'creator'])->find($this->eventId);
        if (! $event) {
            return;
        }

        if ($event->status === 'cancelled') {
            return;
        }

        if ($this->type === 'reminder' && EventReminderLog::where('event_id', $event->id)->where('reminder_type', $this->reminderType)->exists()) {
            return;
        }

        $recipients = $audienceResolver->resolve($event, $event->creator);
        if ($recipients->isEmpty()) {
            Log::warning('Event email skipped: no recipients resolved', [
                'event_id' => $event->id,
                'type' => $this->type,
                'reminder_type' => $this->reminderType,
            ]);
            return;
        }

        $audienceContext = $this->describeAudience($event);
        $email = $generator->generate($event, $this->type, $this->reminderType, $audienceContext);

        $payload = [
            'subject' => $email['subject'],
            'body' => $email['body'],
            'recipients' => $recipients->all(),
            'event_id' => $event->id,
            'type' => $this->type,
            'reminder_type' => $this->reminderType,
        ];

        $n8n->sendEmail($payload);

        if ($this->type === 'reminder') {
            EventReminderLog::create([
                'event_id' => $event->id,
                'reminder_type' => $this->reminderType ?? '',
                'sent_at' => now(),
            ]);
        }
    }

    public function tags(): array
    {
        return [
            'event:'.$this->eventId,
            'type:'.$this->type,
            $this->reminderType ? 'reminder:'.$this->reminderType : 'reminder:none',
        ];
    }

    protected function describeAudience(Event $event): string
    {
        $notes = $event->audience_notes;

        return match ($event->audience) {
            'ceit_students' => $notes ? 'All CEIT students ('.$notes.')' : 'All CEIT students',
            'society_members', 'others' => $notes
                ? $notes
                : ($event->society?->abbreviation
                    ? 'Members of '.$event->society->abbreviation
                    : 'Society members'),
            'society_officers' => $event->society?->abbreviation
                ? 'Officers of '.$event->society->abbreviation
                : 'Society officers',
            'all_officers' => $notes ? 'All society and LSG officers ('.$notes.')' : 'All society and LSG officers',
            'lsg_officers' => $notes ? 'CEIT LSG officers ('.$notes.')' : 'CEIT LSG officers',
            default => $notes ? $notes : 'Invited audience',
        };
    }
}
