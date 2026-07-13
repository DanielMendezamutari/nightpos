<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts;

use App\Application\Cash\DTOs\FinancialDashboardDTO;
use App\Domain\Cash\ValueObjects\CashSessionId;

interface FinancialDashboardAssembler
{
    public function assemble(
        int $tenantId,
        int $branchId,
        CashSessionId $cashSessionId,
        string $openingAmount,
        ?string $storedExpectedAmount,
        ?string $declaredClosingAmount,
        ?string $differenceAmount,
        string $status,
        ?int $officialShiftId = null,
    ): FinancialDashboardDTO;
}
