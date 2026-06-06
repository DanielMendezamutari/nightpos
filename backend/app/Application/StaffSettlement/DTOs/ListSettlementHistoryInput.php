<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\DTOs;

final readonly class ListSettlementHistoryInput
{
    public function __construct(
        public int $limit = 50,
        public ?int $officialShiftId = null,
        public ?int $staffUserId = null,
        public ?string $settlementType = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {
    }
}
