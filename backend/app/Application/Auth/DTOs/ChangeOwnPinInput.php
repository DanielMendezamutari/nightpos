<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

final readonly class ChangeOwnPinInput
{
    public function __construct(
        public int $userId,
        public ?int $tenantId,
        public ?int $branchId,
        public string $currentPassword,
        public string $newPin,
    ) {
    }
}
