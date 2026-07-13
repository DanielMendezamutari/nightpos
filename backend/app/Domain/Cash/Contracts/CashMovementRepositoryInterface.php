<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts;

use App\Domain\Cash\ValueObjects\CashSessionId;

interface CashMovementRepositoryInterface
{
    public function getCashMovementsSummary(CashSessionId $cashSessionId): array;

    public function getMovementsBreakdown(CashSessionId $cashSessionId): array;
}
