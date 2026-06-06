<?php

declare(strict_types=1);

namespace App\Application\Sale\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class GetSaleInput extends DataTransferObject
{
    public function __construct(
        public int $saleId,
    ) {
    }
}
