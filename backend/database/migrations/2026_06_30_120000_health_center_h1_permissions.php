<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $platform = PermissionModel::query()->firstOrCreate(
            ['slug' => 'health.platform.view'],
            ['name' => 'Ver Health Center plataforma'],
        );

        $tenant = PermissionModel::query()->firstOrCreate(
            ['slug' => 'health.tenant.view'],
            ['name' => 'Ver Health Center del local'],
        );

        RoleModel::query()
            ->whereNull('tenant_id')
            ->where('slug', 'super_admin')
            ->each(function (RoleModel $role) use ($platform): void {
                $role->permissions()->syncWithoutDetaching([$platform->id]);
            });

        $opsPermission = PermissionModel::query()->where('slug', 'platform.operations.view')->first();

        if ($opsPermission !== null) {
            RoleModel::query()
                ->whereHas('permissions', fn ($q) => $q->where('permissions.id', $opsPermission->id))
                ->each(function (RoleModel $role) use ($platform): void {
                    $role->permissions()->syncWithoutDetaching([$platform->id]);
                });
        }

        RoleModel::query()
            ->where('slug', 'tenant_owner')
            ->each(function (RoleModel $role) use ($tenant): void {
                $role->permissions()->syncWithoutDetaching([$tenant->id]);
            });
    }

    public function down(): void
    {
        foreach (['health.platform.view', 'health.tenant.view'] as $slug) {
            $permission = PermissionModel::query()->where('slug', $slug)->first();

            if ($permission === null) {
                continue;
            }

            RoleModel::query()->each(function (RoleModel $role) use ($permission): void {
                $role->permissions()->detach($permission->id);
            });

            $permission->delete();
        }
    }
};
