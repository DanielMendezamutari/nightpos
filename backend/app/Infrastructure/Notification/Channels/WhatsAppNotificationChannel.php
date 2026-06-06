<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Channels;

use App\Shared\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder: integración futura con API oficial o proveedor (Twilio, Meta, etc.).
 */
final class WhatsAppNotificationChannel implements NotificationChannelInterface
{
    public function send(array $payload): void
    {
        Log::info('WhatsApp notification skipped (not configured).', [
            'title' => $payload['title'] ?? null,
            'type' => $payload['type'] ?? null,
        ]);
    }
}
