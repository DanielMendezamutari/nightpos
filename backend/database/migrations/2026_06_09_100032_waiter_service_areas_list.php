<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

/**
 * Garzones: listar ambientes para abrir comanda (además del endpoint /waiter/service-areas).
 */
return new class extends Migration
{
    public function up(): void
    {
        $permission = PermissionModel::query()->where('slug', 'settings.service_areas')->first();

        if ($permission === null) {
            return;
        }

        foreach (RoleModel::query()->whereIn('slug', ['waiter'])->get() as $role) {
            $role->permissions()->syncWithoutDetaching([(int) $permission->id]);
        }
    }

    public function down(): void
    {
        $permission = PermissionModel::query()->where('slug', 'settings.service_areas')->first();

        if ($permission === null) {
            return;
        }

        foreach (RoleModel::query()->where('slug', 'waiter')->get() as $role) {
            $role->permissions()->detach((int) $permission->id);
        }
    }
};
