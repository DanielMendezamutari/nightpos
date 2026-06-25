<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum PrintJobSourceType: string
{
    case Order = 'order';
    case Sale = 'sale';
    case CashSession = 'cash_session';
    case Shift = 'shift';
    case RoomService = 'room_service';
    case Show = 'show';
    case CashMovement = 'cash_movement';
    case StaffSettlement = 'staff_settlement';
    case PrintDevice = 'print_device';
}
