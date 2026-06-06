<?php

declare(strict_types=1);

namespace App\Application\Tenant\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Tenant context. Extend for specific use cases.
 */
abstract readonly class TenantDto extends DataTransferObject
{
}
