<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the Auth context. Extend for specific use cases.
 */
abstract readonly class AuthDto extends DataTransferObject
{
}
