<?php

declare(strict_types=1);

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Entities\OrderItemAllocation;
use App\Shared\Contracts\RepositoryInterface;

interface OrderItemAllocationRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<OrderItemAllocation>
     */
    public function listByOrderItemId(int $orderItemId): array;

    /**
     * @return array<int, list<OrderItemAllocation>> keyed by order_item_id
     */
    public function listGroupedByOrderId(int $orderId): array;

    public function sumUnitsForOrderItem(int $orderItemId): int;

    public function deleteForOrderItem(int $orderItemId): void;

    /**
     * @param  list<array{girl_user_id: int, units: int, unit_amount: string, total_amount: string}>  $rows
     * @return list<OrderItemAllocation>
     */
    public function sync(
        int $tenantId,
        int $branchId,
        int $orderItemId,
        string $allocationType,
        array $rows,
    ): array;
}
