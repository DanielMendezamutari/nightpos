<?php

declare(strict_types=1);

namespace App\Domain\Order\Entities;

final readonly class Order
{
    /**
     * @param  list<OrderItem>  $items
     */
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public ?int $officialShiftId,
        public string $orderNumber,
        public string $status,
        public ?string $tableLabel,
        public ?int $serviceAreaId = null,
        public ?int $waiterUserId,
        public int $openedByUserId,
        public ?string $notes,
        public string $subtotal,
        public string $total,
        public string $currency,
        public ?string $sentToBarAt,
        public ?string $cancelledAt,
        public array $items = [],
        public ?string $openedAt = null,
        public int $itemsCount = 0,
    ) {
    }

    public function allowsModification(): bool
    {
        return ! in_array($this->status, ['BILLED', 'CANCELLED'], true);
    }
}
