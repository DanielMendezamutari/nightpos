<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Sale\Repositories\SaleItemAllocationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;

final class EloquentSaleItemAllocationRepository implements SaleItemAllocationRepositoryInterface
{
    public function listUnsettledForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        $rows = SaleItemAllocationModel::query()
            ->select([
                'sale_item_allocations.id',
                'sale_item_allocations.sale_item_id',
                'sale_item_allocations.girl_user_id',
                'sale_item_allocations.units',
                'sale_item_allocations.unit_amount_snapshot',
                'sale_item_allocations.total_amount_snapshot',
                'sale_item_allocations.allocation_type',
                'sale_items.sale_id',
                'sale_items.product_name_snapshot',
                'sales.order_id',
                'sales.sale_number',
                'sales.cash_session_id',
            ])
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_allocations.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_item_allocations.tenant_id', $tenantId)
            ->where('sale_item_allocations.branch_id', $branchId)
            ->where('sales.official_shift_id', $officialShiftId)
            ->get();

        $unsettled = [];

        foreach ($rows as $row) {
            $already = StaffSettlementItemModel::query()
                ->where('source_id', (int) $row->id)
                ->where('source_type', 'GIRL_BRACELET_ALLOCATION')
                ->exists();

            if ($already) {
                continue;
            }

            $unsettled[] = [
                'id' => (int) $row->id,
                'sale_item_id' => (int) $row->sale_item_id,
                'girl_user_id' => (int) $row->girl_user_id,
                'units' => (int) $row->units,
                'unit_amount_snapshot' => (string) $row->unit_amount_snapshot,
                'total_amount_snapshot' => (string) $row->total_amount_snapshot,
                'allocation_type' => (string) $row->allocation_type,
                'sale_id' => (int) $row->sale_id,
                'order_id' => $row->order_id !== null ? (int) $row->order_id : null,
                'product_name_snapshot' => (string) $row->product_name_snapshot,
                'sale_number' => (string) $row->sale_number,
                'cash_session_id' => (int) $row->cash_session_id,
            ];
        }

        return $unsettled;
    }

    public function snapshotFromOrderItem(
        int $tenantId,
        int $branchId,
        int $saleItemId,
        int $orderItemId,
    ): void {
        $orderAllocations = OrderItemAllocationModel::query()
            ->where('order_item_id', $orderItemId)
            ->orderBy('id')
            ->get();

        foreach ($orderAllocations as $allocation) {
            SaleItemAllocationModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'sale_item_id' => $saleItemId,
                'girl_user_id' => $allocation->girl_user_id,
                'units' => $allocation->units,
                'unit_amount_snapshot' => $allocation->unit_amount,
                'total_amount_snapshot' => $allocation->total_amount,
                'source_order_item_allocation_id' => $allocation->id,
                'allocation_type' => $allocation->allocation_type,
            ]);
        }
    }

    public function existsForSaleItem(int $saleItemId): bool
    {
        return SaleItemAllocationModel::query()
            ->where('sale_item_id', $saleItemId)
            ->exists();
    }
}
