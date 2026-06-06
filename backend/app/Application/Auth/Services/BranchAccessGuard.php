<?php

declare(strict_types=1);

namespace App\Application\Auth\Services;

use App\Domain\Auth\Exceptions\BranchAccessDeniedException;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class BranchAccessGuard
{
    public function userCanAccessBranch(UserModel $user, int $branchId, int $tenantId): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ((int) $user->branch_id === $branchId) {
            return true;
        }

        return $user->branchAccess()
            ->where('branch_id', $branchId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    public function assertUserCanAccessBranch(UserModel $user, int $branchId, int $tenantId): void
    {
        if (! $this->userCanAccessBranch($user, $branchId, $tenantId)) {
            throw BranchAccessDeniedException::create();
        }
    }
}
