<?php

declare(strict_types=1);

namespace App\Domain\Cash\Entities;

final readonly class CashSession
{
    /**
     * @param list<CashMovement> $movements
     */
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public ?int $officialShiftId,
        public ?int $cashRegisterId,
        public int $openedByUserId,
        public ?int $closedByUserId,
        public string $status,
        public string $openingAmount,
        public ?string $expectedAmount,
        public ?string $declaredClosingAmount,
        public ?string $differenceAmount,
        public ?string $openingNotes,
        public ?string $closingNotes,
        public string $openedAt,
        public ?string $closedAt,
        public string $incomeTotal,
        public string $expenseTotal,
        public array $movements = [],
    ) {
    }
}
