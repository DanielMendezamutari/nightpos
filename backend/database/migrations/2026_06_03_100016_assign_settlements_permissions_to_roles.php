<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            ['name' => 'Acceso liquidaciones', 'slug' => 'settlements.access'],
            ['name' => 'Generar liquidaciones', 'slug' => 'settlements.generate'],
            ['name' => 'Pagar liquidaciones', 'slug' => 'settlements.pay'],
            ['name' => 'Historial liquidaciones', 'slug' => 'settlements.history'],
        ])->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $all = $permissions->pluck('id');

        foreach (['super_admin', 'tenant_owner'] as $slug) {
            $role = RoleModel::query()->where('slug', $slug)->first();
            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($all);
            }
        }

        $cashier = RoleModel::query()->where('slug', 'cashier')->first();
        if ($cashier !== null) {
            $cashier->permissions()->syncWithoutDetaching(
                $permissions->whereIn('slug', [
                    'settlements.access',
                    'settlements.generate',
                    'settlements.pay',
                    'settlements.history',
                ])->pluck('id')
            );
        }

        $waiter = RoleModel::query()->where('slug', 'waiter')->first();
        if ($waiter !== null) {
            $waiter->permissions()->syncWithoutDetaching(
                $permissions->where('slug', 'settlements.access')->pluck('id')
            );
        }
    }

    public function down(): void
    {
        // Permissions retained for data safety.
    }
};
