<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum SettlementAdjustmentType: string
{
    case CleaningDeduction = 'CLEANING_DEDUCTION';
    case ManualFine = 'MANUAL_FINE';
    case ManualDiscount = 'MANUAL_DISCOUNT';
}
