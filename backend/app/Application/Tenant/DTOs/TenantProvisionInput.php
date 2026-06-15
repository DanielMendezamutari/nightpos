<?php

declare(strict_types=1);

namespace App\Application\Tenant\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class TenantProvisionInput extends DataTransferObject
{
    public function __construct(
        public string $tenantName,
        public string $tenantSlug,
        public string $tenantStatus,
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
