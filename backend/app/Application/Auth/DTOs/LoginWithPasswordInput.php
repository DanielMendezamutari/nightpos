<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class LoginWithPasswordInput extends DataTransferObject
{
    public function __construct(
        public string $username,
        public string $password,
        public ?int $tenantId = null,
        public ?string $tenantSlug = null,
    ) {
    }
}
