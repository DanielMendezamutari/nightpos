<?php

declare(strict_types=1);

namespace App\Domain\Sale\Entities;

final readonly class Sale
{
    /**
     * @param list<SaleItem> $items
     * @param list<SalePayment> $payments
     */
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public int $officialShiftId,
        public int $cashSessionId,
        public ?int $orderId,
        public string $saleNumber,
        public int $cashierUserId,
        public ?int $waiterUserId,
        public string $subtotal,
        public string $total,
        public string $currency,
        public string $paymentMode,
        public string $status,
        public string $paidAt,
        public array $items = [],
        public array $payments = [],
    ) {
    }
}
