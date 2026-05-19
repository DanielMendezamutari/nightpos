<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Domain\Ports;

interface ShiftRepository
{
    public function open(array $payload): int;
}
