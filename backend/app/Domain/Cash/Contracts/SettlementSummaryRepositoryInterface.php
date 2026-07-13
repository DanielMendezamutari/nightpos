<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts;

interface SettlementSummaryRepositoryInterface
{
    public function getSettlementBreakdown(
        int $tenantId,
        int $branchId,
        ?int $cashSessionId = null,
        ?int $officialShiftId = null,
    ): array;
}
