<?php

declare(strict_types=1);

namespace App\Application\SSE\Services;

use App\Domain\SSE\Repositories\OperationalEventRepositoryInterface;

/**
 * Single entry-point for emitting operational events to the SSE stream.
 * Use this from any Use Case that needs to broadcast a state change.
 *
 * Usage:
 *   $emitter->emit($tenantId, $branchId, 'room_service.due', ['id' => 42]);
 *   $emitter->emit($tenantId, $branchId, 'cleaning.task.created', [...], 'cleaning');
 */
final class OperationalEventEmitter
{
    public function __construct(
        private readonly OperationalEventRepositoryInterface $events
    ) {}

    public function emit(
        int $tenantId,
        int $branchId,
        string $type,
        array $payload,
        ?string $targetRole = null
    ): void {
        $this->events->create($tenantId, $branchId, $type, $payload, $targetRole);
    }
}
