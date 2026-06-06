<?php

declare(strict_types=1);

namespace App\Application\Shift\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Shift context. Extend for specific use cases.
 */
abstract readonly class ShiftDto extends DataTransferObject
{
}
