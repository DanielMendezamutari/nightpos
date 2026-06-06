<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            ['name' => 'Finalizar piezas', 'slug' => 'room_services.finish'],
            ['name' => 'Revisar piezas', 'slug' => 'room_services.check'],
            ['name' => 'Vista limpieza piezas', 'slug' => 'room_services.cleaning_view'],
            ['name' => 'Acceso notificaciones', 'slug' => 'notifications.access'],
            ['name' => 'Leer notificaciones', 'slug' => 'notifications.read'],
        ])->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $slugs = $permissions->pluck('slug')->all();

        foreach (['super_admin', 'tenant_owner', 'cashier'] as $roleSlug) {
            $role = RoleModel::query()->where('slug', $roleSlug)->first();
            if ($role === null) {
                continue;
            }

            $ids = PermissionModel::query()->whereIn('slug', $slugs)->pluck('id');
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }

    public function down(): void
    {
        PermissionModel::query()->whereIn('slug', [
            'room_services.finish',
            'room_services.check',
            'room_services.cleaning_view',
            'notifications.access',
            'notifications.read',
        ])->delete();
    }
};
