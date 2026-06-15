<?php

declare(strict_types=1);

namespace App\Application\Role\Support;

use App\Domain\Role\Exceptions\RoleAdminException;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\DB;

final class RoleAdminGuard
{
    public function resolveTenantRole(int $roleId, int $tenantId): RoleModel
    {
        $role = RoleModel::query()
            ->where('id', $roleId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($role === null) {
            throw RoleAdminException::roleNotFound();
        }

        if ($role->tenant_id === null) {
            throw RoleAdminException::globalRoleNotManageable();
        }

        if ($role->slug === ManageablePermissionCatalog::GLOBAL_SUPER_ADMIN_SLUG) {
            throw RoleAdminException::protectedRole();
        }

        return $role;
    }

    public function assertCanDelete(RoleModel $role): void
    {
        if (ManageablePermissionCatalog::isProtectedRoleSlug($role->slug)) {
            throw RoleAdminException::protectedRole();
        }

        $usersCount = UserModel::query()->where('role_id', $role->id)->count();
        if ($usersCount > 0) {
            throw RoleAdminException::roleHasUsers();
        }
    }

    public function assertSlugAvailable(int $tenantId, string $slug, ?int $ignoreRoleId = null): void
    {
        if (ManageablePermissionCatalog::isProtectedRoleSlug($slug)
            || $slug === ManageablePermissionCatalog::GLOBAL_SUPER_ADMIN_SLUG) {
            throw RoleAdminException::reservedSlug();
        }

        $query = RoleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug);

        if ($ignoreRoleId !== null) {
            $query->where('id', '!=', $ignoreRoleId);
        }

        if ($query->exists()) {
            throw RoleAdminException::slugTaken();
        }
    }

    /**
     * @param list<string> $permissionSlugs
     */
    public function assertAssignablePermissions(array $permissionSlugs): void
    {
        foreach ($permissionSlugs as $slug) {
            if (! ManageablePermissionCatalog::isAssignable($slug)) {
                throw RoleAdminException::forbiddenPermission($slug);
            }
        }
    }

    /**
     * @param list<string> $permissionSlugs
     */
    public function assertTenantKeepsRoleAdmin(
        RoleModel $role,
        array $permissionSlugs,
        ?UserModel $actingUser = null,
    ): void {
        $adminPermId = PermissionModel::query()
            ->where('slug', ManageablePermissionCatalog::ROLE_PERMISSION_ADMIN_SLUG)
            ->value('id');

        if ($adminPermId === null) {
            return;
        }

        $currentlyHasAdmin = $role->permissions()
            ->where('permissions.id', $adminPermId)
            ->exists();

        $willHaveAdmin = in_array(ManageablePermissionCatalog::ROLE_PERMISSION_ADMIN_SLUG, $permissionSlugs, true);

        if ($currentlyHasAdmin && $willHaveAdmin) {
            return;
        }

        if (! $currentlyHasAdmin && $willHaveAdmin) {
            return;
        }

        $tenantId = (int) $role->tenant_id;
        $otherRolesWithAdmin = RoleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $role->id)
            ->whereHas('permissions', static fn ($q) => $q->where('permissions.id', $adminPermId))
            ->exists();

        if (! $otherRolesWithAdmin) {
            throw RoleAdminException::lastRolePermissionAdmin();
        }

        if ($actingUser !== null && (int) $actingUser->role_id === (int) $role->id && ! $willHaveAdmin) {
            $userStillHasAdminElsewhere = UserModel::query()
                ->where('tenant_id', $tenantId)
                ->where('id', '!=', $actingUser->id)
                ->whereHas('role.permissions', static fn ($q) => $q->where('permissions.id', $adminPermId))
                ->exists();

            if (! $userStillHasAdminElsewhere) {
                throw RoleAdminException::selfRevokeRoleAdmin();
            }
        }
    }

    public function mapRole(RoleModel $role): array
    {
        $permissionSlugs = $role->permissions()
            ->orderBy('slug')
            ->pluck('slug')
            ->all();

        $manageableCount = count(array_filter(
            $permissionSlugs,
            static fn (string $slug): bool => ManageablePermissionCatalog::isAssignable($slug),
        ));

        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'tenant_id' => $role->tenant_id,
            'users_count' => UserModel::query()->where('role_id', $role->id)->count(),
            'permissions_count' => $manageableCount,
            'is_protected' => ManageablePermissionCatalog::isProtectedRoleSlug($role->slug),
            'permissions' => $permissionSlugs,
        ];
    }
}
