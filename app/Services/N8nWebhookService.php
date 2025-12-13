<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class N8nWebhookService
{
    public function sendEmail(array $payload): void
    {
        $url = config('services.n8n.webhook_url');
        $token = config('services.n8n.webhook_token');

        if (! $url || ! $token) {
            throw new RuntimeException('n8n webhook is not configured.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->post($url, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('n8n webhook failed: '.$response->body());
        }
    }
}
