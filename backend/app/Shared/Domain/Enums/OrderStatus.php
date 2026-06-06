<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum OrderStatus: string
{
    case Open = 'OPEN';
    case SentToBar = 'SENT_TO_BAR';
    case InPreparation = 'IN_PREPARATION';
    case Ready = 'READY';
    case Billed = 'BILLED';
    case Cancelled = 'CANCELLED';
}
