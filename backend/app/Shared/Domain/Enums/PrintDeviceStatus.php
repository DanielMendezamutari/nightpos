<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum PrintDeviceStatus: string
{
    case Active = 'ACTIVE';
    case Disabled = 'DISABLED';
    case Revoked = 'REVOKED';
}
