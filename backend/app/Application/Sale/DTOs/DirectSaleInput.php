<?php

declare(strict_types=1);

namespace App\Application\Sale\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class DirectSaleInput extends DataTransferObject
{
    /**
     * @param list<array{product_id: int, sale_mode: string, quantity: int, girl_user_id: int|null}> $items
     * @param list<array{method: string, amount: float|string}> $payments
     */
    public function __construct(
        public array $items,
        public array $payments,
        public ?string $notes = null,
    ) {
    }
}
