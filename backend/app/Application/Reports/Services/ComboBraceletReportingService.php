<?php

declare(strict_types=1);

namespace App\Application\Reports\Services;

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ComboBraceletReportingService
{
    /**
     * @return array<string, mixed>
     */
    public function mapAllocationRow(SaleItemAllocationModel $row): array
    {
        return [
            'id' => (int) $row->id,
            'girl_user_id' => (int) $row->girl_user_id,
            'girl_name' => $row->girl?->name ?? '-',
            'units' => (int) $row->units,
            'unit_amount' => number_format((float) $row->unit_amount_snapshot, 2, '.', ''),
            'total_amount' => number_format((float) $row->total_amount_snapshot, 2, '.', ''),
            'allocation_type' => $row->allocation_type,
        ];
    }

    /**
     * @param  Collection<int, SaleItemAllocationModel>  $allocations
     * @return array<string, mixed>
     */
    public function enrichSaleItemRow(array $item, int $quantity, ?ProductModel $product, Collection $allocations): array
    {
        $requiresAllocation = $product?->requires_allocation ?? $allocations->isNotEmpty();
        $unitsPerLine = (int) ($product?->bracelet_units_per_line ?? 1);
        $requiredUnits = $requiresAllocation ? $quantity * $unitsPerLine : 0;
        $allocatedUnits = (int) $allocations->sum('units');

        $item['requires_allocation'] = $requiresAllocation;
        $item['bracelet_units_per_line'] = $unitsPerLine;
        $item['required_bracelet_units'] = $requiredUnits;
        $item['allocated_bracelet_units'] = $allocatedUnits;
        $item['allocation_complete'] = ! $requiresAllocation || $allocatedUnits === $requiredUnits;
        $item['allocations'] = $allocations
            ->map(fn (SaleItemAllocationModel $row) => $this->mapAllocationRow($row))
            ->values()
            ->all();

        return $item;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildScopeSummary(int $tenantId, int $branchId, array $filters = []): array
    {
        $base = $this->scopedAllocationsQuery($tenantId, $branchId, $filters);

        $saleItemIds = (clone $base)
            ->distinct()
            ->pluck('sale_item_allocations.sale_item_id');

        $productRows = SaleItemModel::query()
            ->whereIn('sale_items.id', $saleItemIds)
            ->select(
                'sale_items.product_id',
                DB::raw('MAX(sale_items.product_name_snapshot) as product_name'),
                DB::raw('SUM(sale_items.quantity) as combo_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_amount'),
            )
            ->groupBy('sale_items.product_id')
            ->get();

        $braceletByProduct = (clone $base)
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_item_allocations.units) as bracelet_units_sold'),
            )
            ->groupBy('sale_items.product_id')
            ->pluck('bracelet_units_sold', 'product_id');

        $girlRows = (clone $base)
            ->join('users', 'users.id', '=', 'sale_item_allocations.girl_user_id')
            ->select(
                'sale_item_allocations.girl_user_id',
                DB::raw('MAX(users.name) as girl_name'),
                DB::raw('SUM(sale_item_allocations.units) as units'),
                DB::raw('SUM(sale_item_allocations.total_amount_snapshot) as total_amount'),
            )
            ->groupBy('sale_item_allocations.girl_user_id')
            ->get();

        $settlementRows = (clone $base)
            ->leftJoin('staff_settlement_items as ssi', function ($join) {
                $join->on('ssi.source_id', '=', 'sale_item_allocations.id')
                    ->where('ssi.source_type', '=', 'GIRL_BRACELET_ALLOCATION');
            })
            ->leftJoin('staff_settlements as ss', 'ss.id', '=', 'ssi.staff_settlement_id')
            ->select(
                DB::raw('SUM(sale_item_allocations.units) as total_units'),
                DB::raw('SUM(sale_item_allocations.total_amount_snapshot) as total_amount'),
                DB::raw("SUM(CASE WHEN ss.status = 'PAID' THEN sale_item_allocations.units ELSE 0 END) as settled_units"),
                DB::raw("SUM(CASE WHEN ss.status = 'PAID' THEN sale_item_allocations.total_amount_snapshot ELSE 0 END) as settled_amount"),
                DB::raw("SUM(CASE WHEN ss.id IS NULL OR ss.status = 'PENDING' THEN sale_item_allocations.units ELSE 0 END) as pending_units"),
                DB::raw("SUM(CASE WHEN ss.id IS NULL OR ss.status = 'PENDING' THEN sale_item_allocations.total_amount_snapshot ELSE 0 END) as pending_amount"),
            )
            ->first();

        $productsSold = $productRows->map(fn ($row) => [
            'product_id' => (int) $row->product_id,
            'product_name' => $row->product_name,
            'combo_quantity' => (int) $row->combo_quantity,
            'bracelet_units_sold' => (int) ($braceletByProduct[(int) $row->product_id] ?? 0),
            'total_amount' => number_format((float) $row->total_amount, 2, '.', ''),
        ])->values()->all();

        $distributionByGirl = $girlRows->map(fn ($row) => [
            'girl_user_id' => (int) $row->girl_user_id,
            'girl_name' => $row->girl_name,
            'units' => (int) $row->units,
            'total_amount' => number_format((float) $row->total_amount, 2, '.', ''),
        ])->values()->all();

        return [
            'products_sold' => $productsSold,
            'total_combo_quantity' => (int) collect($productsSold)->sum('combo_quantity'),
            'total_bracelet_units' => (int) collect($productsSold)->sum('bracelet_units_sold'),
            'distribution_by_girl' => $distributionByGirl,
            'settlement_status' => [
                'settled_units' => (int) ($settlementRows->settled_units ?? 0),
                'pending_units' => (int) ($settlementRows->pending_units ?? 0),
                'settled_amount' => number_format((float) ($settlementRows->settled_amount ?? 0), 2, '.', ''),
                'pending_amount' => number_format((float) ($settlementRows->pending_amount ?? 0), 2, '.', ''),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function braceletUnitsSoldByProduct(int $tenantId, int $branchId, array $filters = []): array
    {
        $rows = $this->scopedAllocationsQuery($tenantId, $branchId, $filters)
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_item_allocations.units) as bracelet_units_sold'),
                DB::raw('SUM(DISTINCT sale_items.id) as combo_line_count'),
            )
            ->groupBy('sale_items.product_id')
            ->get();

        $comboQtyRows = SaleItemModel::query()
            ->whereIn('id', $this->scopedAllocationsQuery($tenantId, $branchId, $filters)
                ->distinct()
                ->pluck('sale_item_allocations.sale_item_id'))
            ->select('product_id', DB::raw('SUM(quantity) as combo_quantity'))
            ->groupBy('product_id')
            ->pluck('combo_quantity', 'product_id');

        $map = [];
        foreach ($rows as $row) {
            $productId = (int) $row->product_id;
            $map[$productId] = [
                'bracelet_units_sold' => (int) $row->bracelet_units_sold,
                'combo_quantity' => (int) ($comboQtyRows[$productId] ?? 0),
            ];
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    public function enrichSettlementItem(array $item): array
    {
        if (($item['source_type'] ?? '') !== 'GIRL_BRACELET_ALLOCATION' || empty($item['source_id'])) {
            return $item;
        }

        $allocation = SaleItemAllocationModel::query()
            ->with(['girl', 'saleItem.sale'])
            ->find((int) $item['source_id']);

        if ($allocation === null) {
            return $item;
        }

        $productName = $allocation->saleItem?->product_name_snapshot ?? $item['product_name'] ?? 'Combo';
        $units = (int) $allocation->units;

        $item['units'] = $units;
        $item['unit_amount'] = number_format((float) $allocation->unit_amount_snapshot, 2, '.', '');
        $item['allocation_total_amount'] = number_format((float) $allocation->total_amount_snapshot, 2, '.', '');
        $item['girl_name'] = $allocation->girl?->name;
        $item['product_name'] = $productName;
        $item['sale_number'] = $allocation->saleItem?->sale?->sale_number ?? ($item['sale_number'] ?? null);
        $item['order_id'] = $allocation->saleItem?->sale?->order_id ?? ($item['order_id'] ?? null);
        $item['display_description'] = sprintf('%s — %d %s', $productName, $units, $units === 1 ? 'manilla' : 'manillas');

        return $item;
    }

    /**
     * @param  list<int>  $saleItemIds
     * @return Collection<int, Collection<int, SaleItemAllocationModel>>
     */
    public function loadAllocationsGroupedBySaleItem(array $saleItemIds): Collection
    {
        if ($saleItemIds === []) {
            return collect();
        }

        return SaleItemAllocationModel::query()
            ->with('girl')
            ->whereIn('sale_item_id', $saleItemIds)
            ->orderBy('id')
            ->get()
            ->groupBy('sale_item_id');
    }

    /**
     * @param  list<int>  $productIds
     * @return Collection<int, ProductModel>
     */
    public function loadProductsByIds(array $productIds): Collection
    {
        if ($productIds === []) {
            return collect();
        }

        return ProductModel::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function scopedAllocationsQuery(int $tenantId, int $branchId, array $filters): Builder
    {
        $query = SaleItemAllocationModel::query()
            ->where('sale_item_allocations.tenant_id', $tenantId)
            ->where('sale_item_allocations.branch_id', $branchId)
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_allocations.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id');

        if (! empty($filters['official_shift_id'])) {
            $query->where('sales.official_shift_id', (int) $filters['official_shift_id']);
        }

        if (! empty($filters['cash_session_id'])) {
            $query->where('sales.cash_session_id', (int) $filters['cash_session_id']);
        }

        if (! empty($filters['shift_ids']) && is_array($filters['shift_ids'])) {
            $query->whereIn('sales.official_shift_id', $filters['shift_ids']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('sales.paid_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('sales.paid_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['waiter_user_id'])) {
            $query->where('sales.waiter_user_id', (int) $filters['waiter_user_id']);
        }

        if (! empty($filters['girl_user_id'])) {
            $query->where('sale_item_allocations.girl_user_id', (int) $filters['girl_user_id']);
        }

        return $query;
    }
}
