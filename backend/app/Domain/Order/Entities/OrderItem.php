<?php

declare(strict_types=1);

namespace App\Domain\Order\Entities;

final readonly class OrderItem
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public int $orderId,
        public int $productId,
        public string $productName,
        public string $saleMode,
        public int $quantity,
        public string $unitPrice,
        public string $lineTotal,
        public ?string $girlAmount,
        public ?string $houseAmount,
        public ?int $girlUserId,
        public string $itemStatus,
        public ?string $notes,
        public ?string $cancellationReason = null,
        public ?string $cancelledAt = null,
    ) {
    }

    public function isCancelled(): bool
    {
        return $this->itemStatus === 'CANCELLED';
    }

    public function isPending(): bool
    {
        return $this->itemStatus === 'PENDING';
    }

    public function isSent(): bool
    {
        return $this->itemStatus === 'SENT';
    }
}
