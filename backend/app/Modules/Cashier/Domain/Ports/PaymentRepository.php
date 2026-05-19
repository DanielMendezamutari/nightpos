<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Domain\Ports;

interface PaymentRepository
{
    public function register(array $payload): int;
}

