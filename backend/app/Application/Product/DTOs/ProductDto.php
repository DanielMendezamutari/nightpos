<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Product context. Extend for specific use cases.
 */
abstract readonly class ProductDto extends DataTransferObject
{
}
