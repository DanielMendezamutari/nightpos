<?php

declare(strict_types=1);

namespace App\Application\Waiter\Services;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;

final class WaiterOrderAccessPolicy
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function isWaiter(): bool
    {
        return $this->staffContext->staffRole() === 'WAITER';
    }

    public function waiterUserId(): ?int
    {
        return $this->staffContext->userId();
    }

    public function assertCanAccess(Order $order): void
    {
        if (! $this->isWaiter()) {
            return;
        }

        $userId = $this->waiterUserId();

        if ($userId === null || $order->waiterUserId !== $userId) {
            throw new OrderNotFoundException();
        }
    }
}
