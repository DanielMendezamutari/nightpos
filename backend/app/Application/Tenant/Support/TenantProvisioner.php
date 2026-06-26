<?php

declare(strict_types=1);

namespace App\Application\Tenant\Support;

use App\Application\Settings\Services\BranchOperationalBootstrapService;
use App\Application\Tenant\DTOs\TenantProvisionInput;
use App\Application\User\Support\UserAdminMapper;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Domain\Tenant\Exceptions\TenantDomainException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\DB;

/**
 * Provisiona un tenant completo: empresa + roles/permisos + sucursal + administrador.
 */
final class TenantProvisioner
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly BranchRepositoryInterface $branches,
        private readonly UserRepositoryInterface $users,
        private readonly TenantRoleProvisioner $roleProvisioner,
        private readonly BranchOperationalBootstrapService $operationalBootstrap,
    ) {
    }

    /**
     * @return array{
     *   tenant: array<string, mixed>,
     *   branch: array<string, mixed>,
     *   admin: array<string, mixed>|null,
     *   roles: list<string>
     * }
     */
    public function provision(TenantProvisionInput $input): array
    {
        if ($this->tenants->slugExists($input->tenantSlug)) {
            throw TenantDomainException::duplicateSlug();
        }

        $planId = $this->resolvePlanId($input->planId, $input->planName);
        $planName = $this->resolvePlanName($planId, $input->planName);

        return DB::transaction(function () use ($input, $planId, $planName) {
            $tenant = $this->tenants->create(
                name: $input->tenantName,
                slug: $input->tenantSlug,
                status: $input->tenantStatus,
                planId: $planId,
                planName: $planName,
                subscriptionStartsAt: $input->subscriptionStartsAt,
                subscriptionEndsAt: $input->subscriptionEndsAt,
            );

            $roleIds = $this->roleProvisioner->provision($tenant->id);
            $this->assertRolesProvisioned($tenant->id, array_keys($roleIds));

            $branch = $this->branches->create(
                tenantId: $tenant->id,
                name: $input->branchName,
                code: strtoupper(trim($input->branchCode)),
                address: $input->branchAddress,
                status: $input->branchStatus,
            );

            $bootstrapCreated = $this->operationalBootstrap->bootstrap($tenant->id, $branch->id);

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
                    'plan_id' => $tenant->planId,
                    'plan_name' => $tenant->planName,
                ],
                'branch' => [
                    'id' => $branch->id,
                    'tenant_id' => $branch->tenantId,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ],
                'admin' => $adminModel ? UserAdminMapper::user($adminModel) : null,
                'roles' => array_keys($roleIds),
                'bootstrap' => $bootstrapCreated,
            ];
        });
    }

    private function resolvePlanId(?int $planId, ?string $planName): ?int
    {
        if ($planId !== null) {
            return $planId;
        }

        if ($planName !== null && trim($planName) !== '') {
            $resolved = PlanModel::query()
                ->where('code', strtoupper(trim($planName)))
                ->value('id');

            if ($resolved !== null) {
                return (int) $resolved;
            }
        }

        $freeId = PlanModel::query()->where('code', 'FREE')->value('id');

        return $freeId !== null ? (int) $freeId : null;
    }

    private function resolvePlanName(?int $planId, ?string $planName): ?string
    {
        if ($planId !== null) {
            $plan = PlanModel::query()->find($planId);

            return $plan?->code ?? $planName;
        }

        return $planName;
    }

    /**
     * @param list<string> $expectedSlugs
     */
    private function assertRolesProvisioned(int $tenantId, array $expectedSlugs): void
    {
        foreach ($expectedSlugs as $slug) {
            $role = RoleModel::query()
                ->where('tenant_id', $tenantId)
                ->where('slug', $slug)
                ->first();

            if ($role === null) {
                throw new \RuntimeException(sprintf('Rol %s no provisionado.', $slug));
            }

            $permCount = $role->permissions()->count();
            if ($permCount === 0) {
                throw new \RuntimeException(sprintf('Rol %s sin permisos.', $slug));
            }
        }

        if (PermissionModel::query()->count() === 0) {
            throw new \RuntimeException('Catálogo de permisos vacío.');
        }
    }
}
