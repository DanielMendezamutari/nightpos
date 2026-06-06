<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = PermissionModel::query()->firstOrCreate(
            ['slug' => 'sales.direct_create'],
            ['name' => 'Venta directa desde caja'],
        );

        RoleModel::query()
            ->whereIn('slug', ['super_admin', 'tenant_owner', 'cashier', 'cashier_senior'])
            ->get()
            ->each(fn (RoleModel $role) => $role->permissions()->syncWithoutDetaching([$permission->id]));
    }

    public function down(): void
    {
        $permission = PermissionModel::query()->where('slug', 'sales.direct_create')->first();

        if ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
