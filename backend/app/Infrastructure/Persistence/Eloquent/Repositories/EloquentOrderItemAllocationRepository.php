<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Order\Entities\OrderItemAllocation;
use App\Domain\Order\Repositories\OrderItemAllocationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;

final class EloquentOrderItemAllocationRepository implements OrderItemAllocationRepositoryInterface
{
    public function listByOrderItemId(int $orderItemId): array
    {
        return OrderItemAllocationModel::query()
            ->with('girl:id,name')
            ->where('order_item_id', $orderItemId)
            ->orderBy('id')
            ->get()
            ->map(fn (OrderItemAllocationModel $model) => $this->map($model))
            ->all();
    }

    public function listGroupedByOrderId(int $orderId): array
    {
        $itemIds = OrderItemModel::query()
            ->where('order_id', $orderId)
            ->pluck('id')
            ->all();

        if ($itemIds === []) {
            return [];
        }

        $grouped = [];

        foreach ($itemIds as $itemId) {
            $grouped[(int) $itemId] = [];
        }

        $rows = OrderItemAllocationModel::query()
            ->with('girl:id,name')
            ->whereIn('order_item_id', $itemIds)
            ->orderBy('id')
            ->get();

        foreach ($rows as $model) {
            $grouped[(int) $model->order_item_id][] = $this->map($model);
        }

        return $grouped;
    }

    public function sumUnitsForOrderItem(int $orderItemId): int
    {
        return (int) OrderItemAllocationModel::query()
            ->where('order_item_id', $orderItemId)
            ->sum('units');
    }

    public function deleteForOrderItem(int $orderItemId): void
    {
        OrderItemAllocationModel::query()
            ->where('order_item_id', $orderItemId)
            ->delete();
    }

    public function sync(
        int $tenantId,
        int $branchId,
        int $orderItemId,
        string $allocationType,
        array $rows,
    ): array {
        $this->deleteForOrderItem($orderItemId);

        $created = [];

        foreach ($rows as $row) {
            $model = OrderItemAllocationModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'order_item_id' => $orderItemId,
                'girl_user_id' => $row['girl_user_id'],
                'units' => $row['units'],
                'unit_amount' => $row['unit_amount'],
                'total_amount' => $row['total_amount'],
                'allocation_type' => $allocationType,
            ]);

            $created[] = $this->map($model->load('girl:id,name'));
        }

        return $created;
    }

    private function map(OrderItemAllocationModel $model): OrderItemAllocation
    {
        return new OrderItemAllocation(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            orderItemId: (int) $model->order_item_id,
            girlUserId: (int) $model->girl_user_id,
            units: (int) $model->units,
            unitAmount: (string) $model->unit_amount,
            totalAmount: (string) $model->total_amount,
            allocationType: $model->allocation_type,
            girlName: $model->girl?->name,
        );
    }
}
