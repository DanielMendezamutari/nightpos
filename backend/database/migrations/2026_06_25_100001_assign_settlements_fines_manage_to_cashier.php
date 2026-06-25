<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = PermissionModel::query()->where('slug', 'settlements.fines.manage')->first();

        if ($permission === null) {
            $permission = PermissionModel::query()->create([
                'slug' => 'settlements.fines.manage',
                'name' => 'Gestionar multas de liquidación',
            ]);
        }

        $cashier = RoleModel::query()->where('slug', 'cashier')->first();

        if ($cashier !== null) {
            $cashier->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    public function down(): void
    {
        $permission = PermissionModel::query()->where('slug', 'settlements.fines.manage')->first();

        if ($permission === null) {
            return;
        }

        $cashier = RoleModel::query()->where('slug', 'cashier')->first();

        if ($cashier !== null) {
            $cashier->permissions()->detach($permission->id);
        }
    }
};
