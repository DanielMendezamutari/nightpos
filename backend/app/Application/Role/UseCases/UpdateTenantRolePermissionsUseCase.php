<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\DTOs\UpdateRolePermissionsInput;
use App\Application\Role\Support\ManageablePermissionCatalog;
use App\Application\Role\Support\RoleAdminGuard;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateTenantRolePermissionsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly RoleAdminGuard $guard,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        if (! is_array($input) || ! isset($input['role_id'], $input['data']) || ! $input['data'] instanceof UpdateRolePermissionsInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $role = $this->guard->resolveTenantRole((int) $input['role_id'], $tenant->id);
        $requested = array_values(array_unique($input['data']->permissionSlugs));

        $this->guard->assertAssignablePermissions($requested);

        $actingUser = null;
        $userId = $this->staffContext->userId();
        if ($userId !== null) {
            $actingUser = UserModel::query()->find($userId);
        }

        $this->guard->assertTenantKeepsRoleAdmin($role, $requested, $actingUser);

        $currentSlugs = $role->permissions()->pluck('slug')->all();
        $preserved = array_values(array_filter(
            $currentSlugs,
            static fn (string $slug): bool => ! ManageablePermissionCatalog::isAssignable($slug),
        ));

        $finalSlugs = array_values(array_unique(array_merge($preserved, $requested)));

        $permissionIds = PermissionModel::query()
            ->whereIn('slug', $finalSlugs)
            ->pluck('id')
            ->all();

        $before = array_values(array_intersect($currentSlugs, ManageablePermissionCatalog::assignableSlugs()));
        $after = $requested;

        $role->permissions()->sync($permissionIds);

        $this->audit->record('role.permissions.updated', 'role', (int) $role->id, [
            'role_slug' => $role->slug,
            'before' => $before,
            'after' => $after,
        ]);

        return OperationResult::ok('Permisos del rol actualizados.', ['role' => $this->guard->mapRole($role->fresh())]);
    }
}
