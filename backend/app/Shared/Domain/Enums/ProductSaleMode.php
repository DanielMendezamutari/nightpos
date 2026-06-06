<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum ProductSaleMode: string
{
    case Regular = 'REGULAR';
    case Solo = 'SOLO';
    case ConAcompanante = 'CON_ACOMPANANTE';
    case Promo = 'PROMO';
    case Vip = 'VIP';
}
