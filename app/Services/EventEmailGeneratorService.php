<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Str;

class EventEmailGeneratorService
{
    public function __construct(
        protected OllamaAiService $ai
    ) {
    }

    public function generate(Event $event, string $type, ?string $reminderType = null, ?string $audienceContext = null): array
    {
        $purpose = $type === 'reminder'
            ? 'Reminder'
            : 'Event creation announcement';

        $tz = config('app.timezone', 'UTC');

        $data = [
            'event' => [
                'title' => $event->title,
                'description' => $event->description,
                'start_at_local' => optional($event->start_at)->timezone($tz)?->format('M d, Y g:i A (T)'),
                'location' => $event->location,
                'audience_context' => $audienceContext,
            ],
            'purpose' => $purpose,
            'reminder_type' => $reminderType,
            'tone' => 'professional, friendly, informative',
        ];

        $system = [
            'role' => 'system',
            'content' => 'You write concise, friendly, professional plain-text emails about university events. No markdown, no bullets, no numbered lists, no bold/italics. Keep the subject under 12 words.',
        ];

        $user = [
            'role' => 'user',
            'content' => "Using only the JSON provided, craft an email.\nReturn ONLY a JSON object with exactly two keys: subject and body.\nBody should be 120-180 words, in 2 short paragraphs max, plain text (no bullets, no markdown, no tables).\nInclude event title, date/time ({$tz}), location if present, and a clear call to action.\nIf this is a reminder, acknowledge it is a {$reminderType} reminder before start.\nData:\n".json_encode($data, JSON_PRETTY_PRINT),
        ];

        $raw = $this->ai->chat([$system, $user]);
        $parsed = json_decode($raw, true);

        $subject = null;
        $body = null;

        if (is_array($parsed)) {
            $subject = $parsed['subject'] ?? null;
            $body = $parsed['body'] ?? null;
        }

        if (! is_string($subject) || trim($subject) === '') {
            $subject = $this->fallbackSubject($event, $type, $reminderType);
        }

        if (! is_string($body) || trim($body) === '') {
            $body = is_string($raw) && trim($raw) !== ''
                ? trim($raw)
                : $this->fallbackBody($event, $type, $reminderType, $tz, $audienceContext);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    protected function fallbackSubject(Event $event, string $type, ?string $reminderType): string
    {
        if ($type === 'reminder') {
            return 'Reminder: '.$event->title.' ('.$reminderType.')';
        }

        return 'New event: '.$event->title;
    }

    protected function fallbackBody(Event $event, string $type, ?string $reminderType, string $tz, ?string $audienceContext): string
    {
        $when = optional($event->start_at)->timezone($tz)?->format('M d, Y g:i A');
        $location = $event->location ? 'Location: '.$event->location."\n" : '';
        $aud = $audienceContext ? "Audience: {$audienceContext}\n" : '';
        $intro = $type === 'reminder'
            ? 'This is a quick reminder for the upcoming event.'
            : 'You are invited to a new event.';

        $rem = $type === 'reminder' && $reminderType ? "This is your {$reminderType} reminder.\n" : '';

        return "{$intro}\n\n".
            "Title: {$event->title}\n".
            ($event->description ? 'Details: '.Str::limit($event->description, 220)."\n" : '').
            "When: {$when} ({$tz})\n".
            $location.
            $aud.
            $rem.
            "Please mark your calendar and we look forward to your participation.";
    }
}
