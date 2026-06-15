<?php

declare(strict_types=1);

namespace App\Application\Staff\UseCases;

use App\Application\Staff\DTOs\QuickCreateGirlInput;
use App\Application\Staff\Support\GirlUsernameGenerator;
use App\Application\User\Support\StaffProfileRules;
use App\Application\User\Support\StaffRoleToRoleResolver;
use App\Application\User\Support\UserAdminMapper;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class QuickCreateGirlUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly BranchRepositoryInterface $branches,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof QuickCreateGirlInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $name = trim($input->name);
        if ($name === '') {
            throw UserDomainException::emptyName();
        }

        $accessibleBranchIds = $this->resolveAccessibleBranchIds(
            tenantId: $tenant->id,
            contextBranchId: $branch->id,
            requestedIds: $input->accessibleBranchIds,
        );

        $mainBranchId = $input->branchId ?? $branch->id;
        if (! in_array($mainBranchId, $accessibleBranchIds, true)) {
            throw UserDomainException::branchNotAccessible();
        }

        if ($this->users->activeGirlExistsByNameInBranch($tenant->id, $mainBranchId, $name)) {
            throw UserDomainException::duplicateGirlName();
        }

        $profile = StaffProfileRules::normalize('GIRL', null, true);
        $roleId = StaffRoleToRoleResolver::resolveRoleId($tenant->id, 'GIRL', null);
        $username = GirlUsernameGenerator::generate($tenant->id, $name);

        $this->users->createForTenant(
            tenantId: $tenant->id,
            branchId: $mainBranchId,
            roleId: $roleId,
            name: $name,
            username: $username,
            email: null,
            password: null,
            pinPlain: $input->pin,
            status: 'active',
            staffRole: 'GIRL',
            waiterCommissionPercent: $profile['waiter_commission_percent'],
            canReceiveGirlCommissions: $profile['can_receive_girl_commissions'],
            accessibleBranchIds: $accessibleBranchIds,
            staffNotes: $input->notes,
        );

        $model = UserModel::query()
            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])
            ->where('tenant_id', $tenant->id)
            ->where('username', $username)
            ->first();

        return OperationResult::ok('Chica registrada correctamente.', [
            'girl' => $model ? UserAdminMapper::user($model) : null,
        ]);
    }

    /**
     * @param  list<int>  $requestedIds
     * @return list<int>
     */
    private function resolveAccessibleBranchIds(int $tenantId, int $contextBranchId, array $requestedIds): array
    {
        $creatorId = $this->staffContext->userId();
        if ($creatorId === null) {
            throw UserDomainException::branchNotAccessible();
        }

        $allowedIds = array_map(
            static fn ($branch) => $branch->id,
            $this->branches->listAccessibleForUser($creatorId, $tenantId),
        );
        $allowedSet = array_flip($allowedIds);

        $ids = $requestedIds !== [] ? $requestedIds : [$contextBranchId];
        $ids = array_values(array_unique(array_map('intval', $ids)));

        foreach ($ids as $branchId) {
            if (! isset($allowedSet[$branchId])) {
                throw UserDomainException::branchNotAccessible();
            }
        }

        return $ids;
    }
}
