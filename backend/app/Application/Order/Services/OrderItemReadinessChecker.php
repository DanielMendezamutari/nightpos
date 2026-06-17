<?php

declare(strict_types=1);

namespace App\Application\Order\Services;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderItemAllocationRepositoryInterface;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\SaleMode;

final class OrderItemReadinessChecker
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly OrderItemAllocationRepositoryInterface $allocations,
        private readonly BraceletAllocationValidator $allocationValidator,
    ) {
    }

    public function assertOrderReady(int $tenantId, Order $order): void
    {
        $activeItems = $this->activeItems($order);

        foreach ($activeItems as $item) {
            $this->assertItemReady($tenantId, $item);
        }
    }

    public function assertItemReady(int $tenantId, OrderItem $item): void
    {
        $check = $this->checkItemReady($tenantId, $item);

        if ($check['ok']) {
            return;
        }

        if ($check['blocker'] === 'GIRL_MISSING') {
            throw OrderDomainException::girlRequiredForSaleMode();
        }

        if ($check['blocker'] === 'ALLOCATION_INCOMPLETE' && $check['product'] !== null) {
            $rows = $this->allocations->listByOrderItemId($item->id);
            $this->allocationValidator->assertComplete($check['product'], $item->quantity, $rows);
        }
    }

    /**
     * @return array{
     *     has_companion_items: bool,
     *     has_combo_items: bool,
     *     allocation_incomplete: bool,
     *     girl_missing_count: int,
     *     charge_blocked: bool,
     *     charge_blockers: list<string>,
     * }
     */
    public function assessOrder(int $tenantId, Order $order): array
    {
        $activeItems = $this->activeItems($order);

        if ($activeItems === []) {
            return $this->assessmentResult(
                hasCompanion: false,
                hasCombo: false,
                allocationIncomplete: false,
                girlMissingCount: 0,
                blockers: ['ORDER_EMPTY'],
            );
        }

        $hasCompanion = false;
        $hasCombo = false;
        $allocationIncomplete = false;
        $girlMissingCount = 0;
        $blockers = [];

        foreach ($activeItems as $item) {
            $check = $this->checkItemReady($tenantId, $item);

            if ($check['has_companion']) {
                $hasCompanion = true;
            }

            if ($check['has_combo']) {
                $hasCombo = true;
            }

            if ($check['allocation_incomplete']) {
                $allocationIncomplete = true;
            }

            $girlMissingCount += $check['girl_missing'];

            if (! $check['ok'] && $check['blocker'] !== null && ! in_array($check['blocker'], $blockers, true)) {
                $blockers[] = $check['blocker'];
            }
        }

        return $this->assessmentResult(
            hasCompanion: $hasCompanion,
            hasCombo: $hasCombo,
            allocationIncomplete: $allocationIncomplete,
            girlMissingCount: $girlMissingCount,
            blockers: $blockers,
        );
    }

    /**
     * @return list<OrderItem>
     */
    private function activeItems(Order $order): array
    {
        return array_values(array_filter(
            $order->items,
            static fn (OrderItem $item) => $item->itemStatus !== 'CANCELLED',
        ));
    }

    /**
     * @return array{
     *     ok: bool,
     *     blocker: ?string,
     *     has_companion: bool,
     *     has_combo: bool,
     *     allocation_incomplete: bool,
     *     girl_missing: int,
     *     product: ?Product,
     * }
     */
    private function checkItemReady(int $tenantId, OrderItem $item): array
    {
        $product = $this->products->findById($item->productId, $tenantId);

        if ($product === null) {
            return [
                'ok' => true,
                'blocker' => null,
                'has_companion' => false,
                'has_combo' => false,
                'allocation_incomplete' => false,
                'girl_missing' => 0,
                'product' => null,
            ];
        }

        if ($product->requiresAllocation) {
            $rows = $this->allocations->listByOrderItemId($item->id);
            $complete = $this->allocationValidator->isComplete($product, $item->quantity, $rows);

            return [
                'ok' => $complete,
                'blocker' => $complete ? null : 'ALLOCATION_INCOMPLETE',
                'has_companion' => SaleMode::fromString($item->saleMode)->isConAcompanante(),
                'has_combo' => true,
                'allocation_incomplete' => ! $complete,
                'girl_missing' => 0,
                'product' => $product,
            ];
        }

        $isCompanion = SaleMode::fromString($item->saleMode)->isConAcompanante();

        if ($isCompanion && $item->girlUserId === null) {
            return [
                'ok' => false,
                'blocker' => 'GIRL_MISSING',
                'has_companion' => true,
                'has_combo' => false,
                'allocation_incomplete' => false,
                'girl_missing' => 1,
                'product' => $product,
            ];
        }

        return [
            'ok' => true,
            'blocker' => null,
            'has_companion' => $isCompanion,
            'has_combo' => false,
            'allocation_incomplete' => false,
            'girl_missing' => 0,
            'product' => $product,
        ];
    }

    /**
     * @param  list<string>  $blockers
     * @return array{
     *     has_companion_items: bool,
     *     has_combo_items: bool,
     *     allocation_incomplete: bool,
     *     girl_missing_count: int,
     *     charge_blocked: bool,
     *     charge_blockers: list<string>,
     * }
     */
    private function assessmentResult(
        bool $hasCompanion,
        bool $hasCombo,
        bool $allocationIncomplete,
        int $girlMissingCount,
        array $blockers,
    ): array {
        return [
            'has_companion_items' => $hasCompanion,
            'has_combo_items' => $hasCombo,
            'allocation_incomplete' => $allocationIncomplete,
            'girl_missing_count' => $girlMissingCount,
            'charge_blocked' => $blockers !== [],
            'charge_blockers' => array_values($blockers),
        ];
    }
}
