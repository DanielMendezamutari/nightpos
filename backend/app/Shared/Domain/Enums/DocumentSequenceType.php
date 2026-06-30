<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum DocumentSequenceType: string
{
    case SettlementPayment = 'SETTLEMENT_PAYMENT';

    // Reservado para futura unificación: Sale, Order, etc.
}
