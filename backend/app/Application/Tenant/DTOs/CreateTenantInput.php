<?php

declare(strict_types=1);

namespace App\Application\Tenant\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class CreateTenantInput extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $status,
        public ?int $planId,
        public ?string $planName,
        public ?\DateTimeImmutable $subscriptionStartsAt,
        public ?\DateTimeImmutable $subscriptionEndsAt,
        public string $branchName,
        public string $branchCode,
        public ?string $branchAddress,
        public string $branchStatus,
        public string $adminName,
        public string $adminUsername,
        public ?string $adminEmail,
        public string $adminPassword,
        public ?string $adminPin,
    ) {
    }
}
