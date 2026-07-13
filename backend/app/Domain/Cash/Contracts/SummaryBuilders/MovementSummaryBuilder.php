<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts\SummaryBuilders;

use App\Application\Cash\DTOs\MovementSummaryDTO;
use App\Domain\Cash\ValueObjects\CashSessionId;

interface MovementSummaryBuilder
{
    public function build(CashSessionId $cashSessionId): MovementSummaryDTO;
}
