<?php

declare(strict_types=1);

namespace App\Application\Sale\Support;

use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;

final class SaleAllocationPresenter
{
    public function __construct(
        private readonly ComboBraceletReportingService $comboReporting,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @param  array<string, mixed>  $saleData
     * @return array<string, mixed>
     */
    public function enrichSale(array $saleData): array
    {
        $items = $saleData['items'] ?? [];
        $itemIds = array_map(static fn (array $item) => (int) $item['id'], $items);
        $productIds = array_values(array_unique(array_filter(array_map(
            static fn (array $item) => isset($item['product_id']) ? (int) $item['product_id'] : null,
            $items,
        ))));

        $allocationsByItem = $this->comboReporting->loadAllocationsGroupedBySaleItem($itemIds);
        $products = $this->comboReporting->loadProductsByIds($productIds);

        $girlIds = [];
        foreach ($items as $item) {
            if (($item['sale_mode'] ?? '') === 'CON_ACOMPANANTE' && ! empty($item['girl_user_id'])) {
                $girlIds[] = (int) $item['girl_user_id'];
            }
        }
        $girlNames = $this->users->findDisplayNamesByIds($girlIds);

        $saleData['items'] = array_map(function (array $item) use ($allocationsByItem, $products, $girlNames) {
            $saleItemId = (int) $item['id'];
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;
            /** @var \Illuminate\Support\Collection<int, SaleItemAllocationModel> $rows */
            $rows = $allocationsByItem->get($saleItemId, collect());

            $enriched = $this->comboReporting->enrichSaleItemRow(
                $item,
                (int) $item['quantity'],
                $productId !== null ? $products->get($productId) : null,
                $rows,
            );

            if (
                ($enriched['sale_mode'] ?? '') === 'CON_ACOMPANANTE'
                && ! ($enriched['requires_allocation'] ?? false)
                && ! empty($enriched['girl_user_id'])
            ) {
                $enriched['girl_name'] = $girlNames[(int) $enriched['girl_user_id']] ?? null;
            }

            return $enriched;
        }, $items);

        return $saleData;
    }
}
