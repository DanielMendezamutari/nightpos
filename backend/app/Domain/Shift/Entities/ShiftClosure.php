<?php

declare(strict_types=1);

namespace App\Domain\Shift\Entities;

final readonly class ShiftClosure
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public int $officialShiftId,
        public string $totalCash,
        public string $totalQr,
        public string $totalCard,
        public string $totalSales,
        public string $totalManualIncome,
        public string $totalManualExpense,
        public ?string $totalGirlPayouts,
        public ?string $totalWaiterPayouts,
        public string $expectedCash,
        public string $countedCash,
        public string $cashDifference,
        public string $status,
        public int $closedByUserId,
        public string $closedAt,
        public ?string $notes,
    ) {
    }
}
