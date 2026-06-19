<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum PrintJobType: string
{
    case OrderCommand = 'ORDER_COMMAND';
    case Precheck = 'PRECHECK';
    case SaleReceipt = 'SALE_RECEIPT';
    case CashClose = 'CASH_CLOSE';
    case Settlement = 'SETTLEMENT';
    case RoomService = 'ROOM_SERVICE';
}
