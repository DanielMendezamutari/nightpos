<?php

declare(strict_types=1);

namespace App\Domain\Order\Entities;

final readonly class OrderItemAllocation
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public int $orderItemId,
        public int $girlUserId,
        public int $units,
        public string $unitAmount,
        public string $totalAmount,
        public string $allocationType,
        public ?string $girlName = null,
    ) {
    }
}
