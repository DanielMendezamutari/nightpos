<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum ShiftStatus: string
{
    case Scheduled = 'SCHEDULED';
    case Active = 'ACTIVE';
    case Closed = 'CLOSED';
}
