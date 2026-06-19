<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum PrintJobStatus: string
{
    case Pending = 'PENDING';
    case Claimed = 'CLAIMED';
    case Printing = 'PRINTING';
    case Printed = 'PRINTED';
    case Failed = 'FAILED';
    case Cancelled = 'CANCELLED';
}
