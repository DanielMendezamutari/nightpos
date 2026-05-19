<?php

declare(strict_types=1);

namespace App\Modules\Sales\Domain\Enums;

enum ConsumptionType: string
{
    case SOLO = 'solo';
    case WITH_COMPANION = 'with_companion';
}
