<?php

declare(strict_types=1);

namespace App\Application\Branch\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Branch context. Extend for specific use cases.
 */
abstract readonly class BranchDto extends DataTransferObject
{
}
