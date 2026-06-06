<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UpdateUserInput;
use App\Application\User\Support\StaffProfileRules;
use App\Application\User\Support\StaffRoleToRoleResolver;
use App\Application\User\Support\UserAdminMapper;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateUserAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdateUserInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $existing = $this->users->findByIdForTenant($input->userId, $tenant->id);

        if ($existing === null) {
            throw \App\Domain\User\Exceptions\UserDomainException::notFound();
        }

        $staffRole = $input->staffRole ?? $existing->staffRole;
        $existingProfile = StaffProfileModel::query()->where('user_id', $input->userId)->first();

        $profile = StaffProfileRules::normalize(
            $staffRole,
            $input->waiterCommissionPercent ?? $existing->waiterCommissionPercent,
            $input->canReceiveGirlCommissions ?? $existing->canReceiveGirlCommissions,
            $input->cleaningBaseAmount ?? ($existingProfile?->cleaning_base_amount !== null
                ? (string) $existingProfile->cleaning_base_amount
                : null),
            $input->cleaningRoomAmount ?? ($existingProfile?->cleaning_room_amount !== null
                ? (string) $existingProfile->cleaning_room_amount
                : null),
        );

        $roleId = StaffRoleToRoleResolver::resolveRoleId(
            $tenant->id,
            $staffRole,
            $input->roleId,
        );

        $branchIds = $input->accessibleBranchIds;

        if ($input->branchId !== null && ! in_array($input->branchId, $branchIds, true)) {
            $branchIds[] = $input->branchId;
        }

        $this->users->updateForTenant(
            userId: $input->userId,
            tenantId: $tenant->id,
            branchId: $input->branchId,
            roleId: $roleId,
            name: $input->name,
            username: $input->username,
            email: $input->email,
            status: $input->status,
            staffRole: $staffRole,
            waiterCommissionPercent: $profile['waiter_commission_percent'],
            canReceiveGirlCommissions: $profile['can_receive_girl_commissions'],
            accessibleBranchIds: $branchIds,
            cleaningBaseAmount: $profile['cleaning_base_amount'],
            cleaningRoomAmount: $profile['cleaning_room_amount'],
        );

        $model = UserModel::query()
            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])
            ->where('tenant_id', $tenant->id)
            ->find($input->userId);

        return OperationResult::ok('Usuario actualizado correctamente.', [
            'user' => $model ? UserAdminMapper::user($model) : null,
        ]);
    }
}
