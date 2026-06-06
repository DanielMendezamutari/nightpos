<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

/**
 * Domain representation of a logged-in user (no Laravel types).
 */
final readonly class AuthenticatedUser
{
    /**
     * @param  list<int>  $accessibleBranchIds
     * @param  list<string>  $permissions
     */
    public function __construct(
        public int $id,
        public ?int $tenantId,
        public ?int $branchId,
        public string $name,
        public string $username,
        public ?string $email,
        public string $status,
        public ?string $roleSlug,
        public ?string $staffRole,
        public ?string $waiterCommissionPercent,
        public bool $canReceiveGirlCommissions,
        public array $accessibleBranchIds,
        public array $permissions,
    ) {
    }
}
