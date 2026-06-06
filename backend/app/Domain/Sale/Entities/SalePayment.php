<?php

declare(strict_types=1);

namespace App\Domain\Sale\Entities;

final readonly class SalePayment
{
    public function __construct(
        public int $id,
        public string $paymentMethod,
        public string $amount,
    ) {
    }
}
