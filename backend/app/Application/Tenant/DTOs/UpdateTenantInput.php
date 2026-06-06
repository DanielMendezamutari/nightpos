<?php

declare(strict_types=1);

namespace App\Application\Tenant\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class UpdateTenantInput extends DataTransferObject
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public string $slug,
        public string $status,
        public ?string $planName,
        public ?\DateTimeImmutable $subscriptionStartsAt,
        public ?\DateTimeImmutable $subscriptionEndsAt,
    ) {
    }
}
