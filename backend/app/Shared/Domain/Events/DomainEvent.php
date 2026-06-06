<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

/**
 * Marker for domain events (immutable facts).
 */
interface DomainEvent
{
    public function occurredAt(): \DateTimeImmutable;
}
