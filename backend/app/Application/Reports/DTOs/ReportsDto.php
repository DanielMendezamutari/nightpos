<?php

declare(strict_types=1);

namespace App\Application\Reports\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Reports context. Extend for specific use cases.
 */
abstract readonly class ReportsDto extends DataTransferObject
{
}
