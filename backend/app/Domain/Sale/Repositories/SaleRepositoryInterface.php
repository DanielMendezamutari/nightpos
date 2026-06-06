<?php

declare(strict_types=1);

namespace App\Domain\Sale\Repositories;

use App\Domain\Sale\Entities\Sale;
use App\Shared\Contracts\RepositoryInterface;

interface SaleRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Sale;

    public function existsForOrder(int $orderId): bool;

    /**
     * @return list<Sale>
     */
    public function listForBranch(int $tenantId, int $branchId, ?int $cashSessionId = null, ?int $officialShiftId = null): array;

    /**
     * @param list<array{
     *   order_item_id: int|null,
     *   product_id: int,
     *   product_name_snapshot: string,
     *   sale_mode: string,
     *   quantity: int,
     *   unit_price_snapshot: string,
     *   line_total: string,
     *   girl_user_id: int|null,
     *   girl_amount_snapshot: string|null,
     *   house_amount_snapshot: string|null,
     *   waiter_commission_percent_snapshot: string|null,
     *   waiter_commission_amount_snapshot: string|null,
     * }> $items
     * @param list<array{payment_method: string, amount: string}> $payments
     */
    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $cashSessionId,
        ?int $orderId,
        string $saleNumber,
        int $cashierUserId,
        ?int $waiterUserId,
        string $subtotal,
        string $total,
        string $currency,
        string $paymentMode,
        array $items,
        array $payments,
    ): Sale;

    public function nextSaleNumber(int $branchId): string;

    /**
     * @return array{cash: string, qr: string, card: string}
     */
    public function sumPaymentsByMethodForSession(int $cashSessionId): array;
}
