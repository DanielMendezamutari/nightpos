<?php

declare(strict_types=1);

namespace App\Application\Tenant\Support;

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;

final class TenantRoleProvisioner
{
    /**
     * @return array{tenant_owner: int, cashier: int, waiter: int, cleaning: int, girl: int}
     */
    public function provision(int $tenantId): array
    {
        $map = [
            'tenant_owner' => ['name' => 'Administrador', 'perms' => TenantDefaultRolePermissions::tenantOwner()],
            'cashier' => ['name' => 'Cajero', 'perms' => TenantDefaultRolePermissions::cashier()],
            'waiter' => ['name' => 'Garzón', 'perms' => TenantDefaultRolePermissions::waiter()],
            'cleaning' => ['name' => 'Limpieza', 'perms' => TenantDefaultRolePermissions::cleaning()],
            'girl' => ['name' => 'Chica', 'perms' => TenantDefaultRolePermissions::girl()],
        ];

        $roleIds = [];

        foreach ($map as $slug => $config) {
            $role = RoleModel::query()->create([
                'tenant_id' => $tenantId,
                'name' => $config['name'],
                'slug' => $slug,
            ]);

            $permissionIds = PermissionModel::query()
                ->whereIn('slug', $config['perms'])
                ->pluck('id')
                ->all();

            $role->permissions()->sync($permissionIds);
            $roleIds[$slug] = (int) $role->id;
        }

        return $roleIds;
    }
}
