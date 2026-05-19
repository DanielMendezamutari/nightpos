<?php

declare(strict_types=1);

namespace App\Modules\Sales\Domain\Ports;

interface OrderItemRepository
{
    public function store(array $payload): void;
}
