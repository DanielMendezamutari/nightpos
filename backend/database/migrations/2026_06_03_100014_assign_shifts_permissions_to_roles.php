<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            ['name' => 'Acceso turnos', 'slug' => 'shifts.access'],
            ['name' => 'Abrir turno', 'slug' => 'shifts.open'],
            ['name' => 'Cerrar turno', 'slug' => 'shifts.close'],
            ['name' => 'Listar turnos', 'slug' => 'shifts.list'],
        ])->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $super = RoleModel::query()->where('slug', 'super_admin')->first();
        if ($super !== null) {
            $super->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        }

        $owner = RoleModel::query()->where('slug', 'tenant_owner')->first();
        if ($owner !== null) {
            $owner->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        }

        $cashier = RoleModel::query()->where('slug', 'cashier')->first();
        if ($cashier !== null) {
            $cashier->permissions()->syncWithoutDetaching(
                $permissions->whereIn('slug', ['shifts.access', 'shifts.close'])->pluck('id')
            );
        }

        $waiter = RoleModel::query()->where('slug', 'waiter')->first();
        if ($waiter !== null) {
            $waiter->permissions()->syncWithoutDetaching(
                $permissions->where('slug', 'shifts.access')->pluck('id')
            );
        }
    }

    public function down(): void
    {
        // Permissions retained for data safety.
    }
};
