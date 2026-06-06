<?php

declare(strict_types=1);

namespace App\Application\Printing\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Printing context. Extend for specific use cases.
 */
abstract readonly class PrintingDto extends DataTransferObject
{
}
