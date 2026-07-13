<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts\SummaryBuilders;

use App\Application\Cash\DTOs\SettlementSummaryDTO;

interface SettlementSummaryBuilder
{
    public function build(
        int $tenantId,
        int $branchId,
        ?int $cashSessionId = null,
        ?int $officialShiftId = null,
    ): SettlementSummaryDTO;
}
