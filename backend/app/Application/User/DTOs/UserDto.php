<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

/**
 * Base DTO marker for the User context. Extend for specific use cases.
 */
abstract readonly class UserDto extends DataTransferObject
{
}
