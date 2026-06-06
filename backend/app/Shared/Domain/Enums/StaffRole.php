<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum StaffRole: string
{
    case Waiter = 'WAITER';
    case Girl = 'GIRL';
    case Cashier = 'CASHIER';
    case Manager = 'MANAGER';
    case Inventory = 'INVENTORY';
    case Reports = 'REPORTS';
}
