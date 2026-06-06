<?php

declare(strict_types=1);

namespace App\Application\Staff\UseCases;

use App\Application\Staff\DTOs\QuickCreateWaiterInput;
use App\Application\Staff\Support\WaiterUsernameGenerator;
use App\Application\User\Support\StaffProfileRules;
use App\Application\User\Support\StaffRoleToRoleResolver;
use App\Application\User\Support\UserAdminMapper;
use App\Domain\User\Exceptions\UserDomainException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class QuickCreateWaiterUseCase implements UseCaseInterface
{
    private const DEFAULT_COMMISSION = '5.00';

    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof QuickCreateWaiterInput) {
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

        if ($this->users->activeWaiterExistsByNameInBranch($tenant->id, $branch->id, $name)) {
            throw UserDomainException::duplicateWaiterName();
        }

        $commission = $input->waiterCommissionPercent !== null && $input->waiterCommissionPercent !== ''
            ? $input->waiterCommissionPercent
            : self::DEFAULT_COMMISSION;

        $profile = StaffProfileRules::normalize('WAITER', $commission, false);
        $roleId = StaffRoleToRoleResolver::resolveRoleId($tenant->id, 'WAITER', null);
        $username = WaiterUsernameGenerator::generate($tenant->id, $name);

        $this->users->createForTenant(
            tenantId: $tenant->id,
            branchId: $branch->id,
            roleId: $roleId,
            name: $name,
            username: $username,
            email: null,
            password: null,
            pinPlain: $input->pin,
            status: 'active',
            staffRole: 'WAITER',
            waiterCommissionPercent: $profile['waiter_commission_percent'],
            canReceiveGirlCommissions: $profile['can_receive_girl_commissions'],
            accessibleBranchIds: [$branch->id],
            staffNotes: $input->notes,
        );

        $model = UserModel::query()
            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])
            ->where('tenant_id', $tenant->id)
            ->where('username', $username)
            ->first();

        return OperationResult::ok('Garzón registrado correctamente.', [
            'waiter' => $model ? UserAdminMapper::user($model) : null,
        ]);
    }
}
