<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Fase 9: permisos de ventas para instalaciones ya sembradas antes de sales.list.
     */
    public function up(): void
    {
        $salesPermissions = [
            ['name' => 'Listar ventas', 'slug' => 'sales.list'],
            ['name' => 'Cobrar comandas', 'slug' => 'sales.charge'],
        ];

        $permissionIds = [];

        foreach ($salesPermissions as $row) {
            $permissionIds[] = PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            )->id;
        }

        $roleSlugs = ['cashier', 'tenant_owner', 'super_admin'];

        RoleModel::query()
            ->whereIn('slug', $roleSlugs)
            ->each(function (RoleModel $role) use ($permissionIds): void {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            });
    }

    public function down(): void
    {
        $slugs = ['sales.list', 'sales.charge'];
        $permissionIds = PermissionModel::query()->whereIn('slug', $slugs)->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        RoleModel::query()
            ->whereIn('slug', ['cashier', 'tenant_owner', 'super_admin'])
            ->each(function (RoleModel $role) use ($permissionIds): void {
                $role->permissions()->detach($permissionIds);
            });
    }
};
