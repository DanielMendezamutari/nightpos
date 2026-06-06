<?php

declare(strict_types=1);

namespace App\Application\Order\Services;

use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;

final class OrderAccessGuard
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
    ) {
    }

    public function loadOrder(int $orderId): Order
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw OrderDomainException::branchRequired();
        }

        $order = $this->orders->findById($orderId, $tenant->id);

        if ($order === null || $order->branchId !== $branch->id) {
            throw new OrderNotFoundException();
        }

        $this->waiterAccess->assertCanAccess($order);

        return $order;
    }

    public function assertNotTerminal(Order $order): OrderStatus
    {
        $status = OrderStatus::fromString($order->status);

        if (in_array($status->value, [OrderStatus::BILLED, OrderStatus::CANCELLED], true)) {
            throw OrderDomainException::notModifiable();
        }

        return $status;
    }

    public function assertAllowsLineChanges(OrderStatus $status): void
    {
        if (in_array($status->value, [OrderStatus::IN_PREPARATION, OrderStatus::READY], true)) {
            throw OrderDomainException::notModifiable();
        }
    }
}
