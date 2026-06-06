<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class LoginWithPinInput extends DataTransferObject
{
    public function __construct(
        public string $pin,
        public ?int $tenantId = null,
        public ?string $tenantSlug = null,
        public ?int $branchId = null,
        public ?string $branchCode = null,
    ) {
    }
}
