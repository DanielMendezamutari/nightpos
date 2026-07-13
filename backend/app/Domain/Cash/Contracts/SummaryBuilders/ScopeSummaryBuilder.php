<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts\SummaryBuilders;

use App\Application\Cash\DTOs\ScopeSummaryDTO;
use App\Domain\Cash\ValueObjects\CashSessionId;

interface ScopeSummaryBuilder
{
    public function build(CashSessionId $cashSessionId): ScopeSummaryDTO;
}
