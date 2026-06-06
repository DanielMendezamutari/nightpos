<?php

declare(strict_types=1);

namespace App\Application\Auth\Services;

use App\Application\Auth\DTOs\AuthTokenOutput;
use App\Domain\User\Entities\AuthenticatedUser;

final class AuthResponseMapper
{
    public function toTokenOutput(AuthenticatedUser $user, string $token): AuthTokenOutput
    {
        return new AuthTokenOutput(
            token: $token,
            tokenType: 'bearer',
            user: [
                'id' => $user->id,
                'tenant_id' => $user->tenantId,
                'branch_id' => $user->branchId,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->roleSlug,
                'staff_role' => $user->staffRole,
                'waiter_commission_percent' => $user->waiterCommissionPercent,
                'accessible_branch_ids' => $user->accessibleBranchIds,
                'permissions' => $user->permissions,
            ],
        );
    }
}
