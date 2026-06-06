<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the StaffSettlement context. Extend for specific use cases.
 */
abstract readonly class StaffSettlementDto extends DataTransferObject
{
}
