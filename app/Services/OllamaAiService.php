<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OllamaAiService
{
    public function chat(array $messages): string
    {
        $base = config('services.ollama.base_url');
        $key = config('services.ollama.api_key');
        $model = config('services.ollama.model');

        if (! $base || ! $key) {
            throw new RuntimeException('Ollama Cloud is not configured.');
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
        ];

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout((int) config('services.ollama.timeout', 25))
            ->post(rtrim($base, '/').'/chat', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('AI request failed: '.$response->body());
        }

        $rawBody = $response->body();
        $data = $response->json();

        $content = '';
        if (is_array($data)) {
            $content = $data['message']['content']
                ?? ($data['choices'][0]['message']['content'] ?? null)
                ?? ($data['response'] ?? null)
                ?? '';
        }

        if ((! is_string($content) || trim($content) === '') && is_string($rawBody) && trim($rawBody) !== '') {
            $content = $rawBody;
        }

        if (! is_string($content) || trim($content) === '') {
            $bodyPreview = Str::limit((string) $rawBody, 200);
            throw new RuntimeException('AI returned an empty response. Body preview: '.$bodyPreview);
        }

        return $content;
    }

    public function generateEventSummary(array $context): string
    {
        $system = [
            'role' => 'system',
            'content' => 'You are an assistant that writes formal summaries of university event attendance for documentation and reports. Respond in plain text sentences without bullets, bold/italics, or tables.',
        ];

        $user = [
            'role' => 'user',
            'content' => "Using only the provided JSON, write a 2-3 paragraph neutral, formal summary. Mention event name, date/time, organizer, total attendance, notable patterns by year/course/society, and late/no-timeout if significant. Do not invent data. Important: interpret attendance_mode literally; 'hybrid' here means QR + manual ID options, not virtual/remote participation. Respond in plain text sentences without bullets, tables, or bold/italics.\n\nJSON:\n".json_encode($context, JSON_PRETTY_PRINT),
        ];

        return $this->chat([$system, $user]) ?: 'AI could not generate a response. Please try again.';
    }

    public function answerAttendanceQuestion(string $question, array $context, string $mode): string
    {
        $system = [
            'role' => 'system',
            'content' => 'You are an assistant that answers questions about CEIT event attendance using the provided JSON data. Only answer using that data; if unclear, say it cannot be answered exactly. Respond in plain text without bullets, bold/italics, or tables.',
        ];

        $user = [
            'role' => 'user',
            'content' => "Mode: {$mode}\nUser question: {$question}\nData:\n".json_encode($context, JSON_PRETTY_PRINT),
        ];

        return $this->chat([$system, $user]) ?: 'AI could not generate a response. Please try again.';
    }
}
