<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum PaymentMethod: string
{
    case Cash = 'CASH';
    case Qr = 'QR';
    case Card = 'CARD';
    case Other = 'OTHER';
}
