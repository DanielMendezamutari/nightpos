<?php

declare(strict_types=1);

namespace App\Application\Inventory\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Inventory context. Extend for specific use cases.
 */
abstract readonly class InventoryDto extends DataTransferObject
{
}
