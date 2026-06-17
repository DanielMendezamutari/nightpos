<?php

declare(strict_types=1);

namespace App\Application\Order\Services;

use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Repositories\OrderItemAllocationRepositoryInterface;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Domain\User\Repositories\UserRepositoryInterface;

final class OrderPresentationService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly OrderItemAllocationRepositoryInterface $allocations,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function presentOrder(Order $order, int $tenantId): array
    {
        $products = $this->loadProducts($order, $tenantId);
        $allocationsByItem = $this->allocations->listGroupedByOrderId($order->id);
        $girlNames = $this->loadGirlNames($order->items, $products);

        $items = array_map(
            function (OrderItem $item) use ($products, $allocationsByItem, $girlNames) {
                $product = $products[$item->productId] ?? null;
                $itemAllocations = $allocationsByItem[$item->id] ?? [];
                $girlName = $item->girlUserId !== null
                    ? ($girlNames[$item->girlUserId] ?? null)
                    : null;

                return OrderMapper::item($item, $product, $itemAllocations, $girlName);
            },
            $order->items,
        );

        return array_merge(OrderMapper::order($order, false), [
            'items' => $items,
        ]);
    }

    /**
     * @param  list<OrderItem>  $items
     * @param  array<int, Product>  $products
     * @return array<int, string>
     */
    private function loadGirlNames(array $items, array $products): array
    {
        $girlIds = [];

        foreach ($items as $item) {
            if ($item->girlUserId === null || $item->girlUserId <= 0) {
                continue;
            }

            if ($item->saleMode !== SaleMode::CON_ACOMPANANTE) {
                continue;
            }

            $product = $products[$item->productId] ?? null;

            if ($product !== null && $product->requiresAllocation) {
                continue;
            }

            $girlIds[] = $item->girlUserId;
        }

        return $this->users->findDisplayNamesByIds($girlIds);
    }

    /**
     * @return array<int, Product>
     */
    private function loadProducts(Order $order, int $tenantId): array
    {
        $productIds = array_values(array_unique(array_map(
            static fn (OrderItem $item) => $item->productId,
            $order->items,
        )));

        $products = [];

        foreach ($productIds as $productId) {
            $product = $this->products->findById($productId, $tenantId);

            if ($product !== null) {
                $products[$productId] = $product;
            }
        }

        return $products;
    }
}
