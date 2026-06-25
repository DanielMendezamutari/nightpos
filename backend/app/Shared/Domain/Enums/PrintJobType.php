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
    case SettlementPayment = 'SETTLEMENT_PAYMENT';
    case RoomService = 'ROOM_SERVICE';
    case ShowTicket = 'SHOW_TICKET';
    case CashMovement = 'CASH_MOVEMENT';
    case ShiftClose = 'SHIFT_CLOSE';
    case Test = 'TEST';
}
