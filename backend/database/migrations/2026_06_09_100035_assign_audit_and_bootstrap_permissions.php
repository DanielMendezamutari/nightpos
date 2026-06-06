<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $audit = PermissionModel::query()->firstOrCreate(
            ['slug' => 'audits.list'],
            ['name' => 'Ver bitácora de auditoría'],
        );

        $bootstrap = PermissionModel::query()->firstOrCreate(
            ['slug' => 'settings.bootstrap'],
            ['name' => 'Cargar datos operativos iniciales'],
        );

        foreach (['tenant_owner'] as $slug) {
            $role = RoleModel::query()->where('slug', $slug)->first();

            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching([
                    $audit->id,
                    $bootstrap->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Permissions kept for data safety.
    }
};
