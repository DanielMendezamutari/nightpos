<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface NotificationChannelInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function send(array $payload): void;
}
