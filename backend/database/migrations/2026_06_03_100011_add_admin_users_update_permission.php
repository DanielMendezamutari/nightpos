<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = PermissionModel::query()->firstOrCreate(
            ['slug' => 'admin.users.update'],
            ['name' => 'Actualizar usuarios admin'],
        );

        $roleSlugs = ['super_admin', 'tenant_owner'];

        RoleModel::query()
            ->whereIn('slug', $roleSlugs)
            ->each(function (RoleModel $role) use ($permission): void {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            });
    }

    public function down(): void
    {
        $permission = PermissionModel::query()->where('slug', 'admin.users.update')->first();

        if ($permission === null) {
            return;
        }

        RoleModel::query()
            ->whereIn('slug', ['super_admin', 'tenant_owner'])
            ->each(function (RoleModel $role) use ($permission): void {
                $role->permissions()->detach($permission->id);
            });

        $permission->delete();
    }
};
