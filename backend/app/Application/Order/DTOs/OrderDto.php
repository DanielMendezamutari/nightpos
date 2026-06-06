<?php

declare(strict_types=1);

namespace App\Application\Order\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Order context. Extend for specific use cases.
 */
abstract readonly class OrderDto extends DataTransferObject
{
}
