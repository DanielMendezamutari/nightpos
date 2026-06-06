<?php

declare(strict_types=1);

namespace App\Application\Staff\UseCases;

use App\Domain\User\Exceptions\UserDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListOperationalGirlsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $items = UserModel::query()
            ->with('staffProfile')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->whereHas('staffProfile', function ($query) use ($branch) {
                $query->where('staff_role', 'GIRL')
                    ->where('status', 'active')
                    ->where(function ($inner) use ($branch) {
                        $inner->where('branch_id', $branch->id)
                            ->orWhereNull('branch_id');
                    });
            })
            ->where(function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->orWhereHas('accessibleBranches', fn ($b) => $b->where('branches.id', $branch->id));
            })
            ->orderBy('name')
            ->get()
            ->map(static fn (UserModel $user) => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ])
            ->all();

        return OperationResult::ok('Chicas.', ['items' => $items]);
    }
}
