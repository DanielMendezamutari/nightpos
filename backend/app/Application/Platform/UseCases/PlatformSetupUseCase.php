<?php

declare(strict_types=1);

namespace App\Application\Platform\UseCases;

use App\Application\Platform\DTOs\PlatformSetupInput;
use App\Application\Tenant\Support\TenantRoleProvisioner;
use App\Application\User\Support\UserAdminMapper;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Domain\Tenant\Exceptions\TenantDomainException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class PlatformSetupUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly BranchRepositoryInterface $branches,
        private readonly UserRepositoryInterface $users,
        private readonly TenantRoleProvisioner $roleProvisioner,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof PlatformSetupInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if ($this->tenants->slugExists($input->tenantSlug)) {
            throw TenantDomainException::duplicateSlug();
        }

        $result = DB::transaction(function () use ($input) {
            $tenant = $this->tenants->create(
                name: $input->tenantName,
                slug: $input->tenantSlug,
                status: $input->tenantStatus,
                planName: $input->planName,
                subscriptionStartsAt: null,
                subscriptionEndsAt: null,
            );

            $roleIds = $this->roleProvisioner->provision($tenant->id);

            $branch = $this->branches->create(
                tenantId: $tenant->id,
                name: $input->branchName,
                code: $input->branchCode,
                address: $input->branchAddress,
                status: $input->branchStatus,
            );

            $this->users->createForTenant(
                tenantId: $tenant->id,
                branchId: $branch->id,
                roleId: $roleIds['tenant_owner'],
                name: $input->adminName,
                username: $input->adminUsername,
                email: $input->adminEmail,
                password: $input->adminPassword,
                pinPlain: $input->adminPin,
                status: 'active',
                staffRole: 'MANAGER',
                waiterCommissionPercent: null,
                canReceiveGirlCommissions: false,
                accessibleBranchIds: [$branch->id],
            );

            $adminModel = UserModel::query()
                ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])
                ->where('tenant_id', $tenant->id)
                ->where('username', $input->adminUsername)
                ->first();

            return [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'status' => $tenant->status,
                ],
                'branch' => [
                    'id' => $branch->id,
                    'tenant_id' => $branch->tenantId,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ],
                'admin' => $adminModel ? UserAdminMapper::user($adminModel) : null,
            ];
        });

        return OperationResult::ok('Empresa operativa creada correctamente.', $result);
    }
}
