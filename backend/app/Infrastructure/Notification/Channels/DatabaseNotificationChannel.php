<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Channels;

use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Shared\Contracts\NotificationChannelInterface;

final class DatabaseNotificationChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {
    }

    public function send(array $payload): void
    {
        $this->notifications->create($payload);
    }
}
