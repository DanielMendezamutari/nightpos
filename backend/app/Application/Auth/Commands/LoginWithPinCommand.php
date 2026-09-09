<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

final class LoginWithPinCommand
{
    public function __construct(
        public readonly string $pin,
        public readonly string $tenantSlug,
        public readonly ?string $branchCode = null
    ) {}
}
