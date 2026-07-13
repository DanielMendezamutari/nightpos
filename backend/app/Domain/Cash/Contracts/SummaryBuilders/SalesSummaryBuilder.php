<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts\SummaryBuilders;

use App\Application\Cash\DTOs\SalesSummaryDTO;
use App\Domain\Cash\ValueObjects\CashSessionId;

interface SalesSummaryBuilder
{
    public function build(CashSessionId $cashSessionId): SalesSummaryDTO;
}
