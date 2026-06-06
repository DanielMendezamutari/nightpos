<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            ['name' => 'Listar sesiones de caja admin', 'slug' => 'admin.cash_sessions.list'],
            ['name' => 'Ver detalle sesión de caja admin', 'slug' => 'admin.cash_sessions.view'],
            ['name' => 'Resumen fiscalización de cajas', 'slug' => 'admin.cash_sessions.summary'],
        ])->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $all = $permissions->pluck('id');

        $super = RoleModel::query()->where('slug', 'super_admin')->first();
        if ($super !== null) {
            $super->permissions()->syncWithoutDetaching($all);
        }

        foreach (['tenant_owner', 'cashier_senior'] as $slug) {
            $role = RoleModel::query()->where('slug', $slug)->first();
            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($all);
            }
        }
    }

    public function down(): void
    {
        // Permissions retained for data safety.
    }
};
