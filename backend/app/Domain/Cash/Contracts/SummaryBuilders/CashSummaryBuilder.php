<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts\SummaryBuilders;

use App\Application\Cash\DTOs\CashSummaryDTO;
use App\Domain\Cash\ValueObjects\CashSessionId;

interface CashSummaryBuilder
{
    public function build(CashSessionId $cashSessionId): CashSummaryDTO;
}