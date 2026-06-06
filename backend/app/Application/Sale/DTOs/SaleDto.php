<?php

declare(strict_types=1);

namespace App\Application\Sale\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Sale context. Extend for specific use cases.
 */
abstract readonly class SaleDto extends DataTransferObject
{
}
