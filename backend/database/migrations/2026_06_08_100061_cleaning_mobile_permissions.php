<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $definitions = [
            ['name' => 'Modo limpieza — inicio', 'slug' => 'cleaning.dashboard', 'roles' => ['cleaning']],
            ['name' => 'Modo limpieza — piezas', 'slug' => 'cleaning.room_services', 'roles' => ['cleaning']],
            ['name' => 'Modo limpieza — revisar pieza', 'slug' => 'cleaning.check', 'roles' => ['cleaning']],
            ['name' => 'Modo limpieza — finalizar pieza', 'slug' => 'cleaning.finish', 'roles' => ['cleaning']],
            ['name' => 'Modo limpieza — marcar limpia', 'slug' => 'cleaning.mark_clean', 'roles' => ['cleaning']],
        ];

        foreach ($definitions as $row) {
            $permission = PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            );

            foreach ($row['roles'] as $roleSlug) {
                $role = RoleModel::query()->where('slug', $roleSlug)->first();

                if ($role !== null) {
                    $role->permissions()->syncWithoutDetaching([$permission->id]);
                }
            }
        }
    }

    public function down(): void
    {
        $slugs = [
            'cleaning.dashboard',
            'cleaning.room_services',
            'cleaning.check',
            'cleaning.finish',
            'cleaning.mark_clean',
        ];

        $ids = PermissionModel::query()->whereIn('slug', $slugs)->pluck('id');

        foreach (RoleModel::query()->where('slug', 'cleaning')->get() as $role) {
            $role->permissions()->detach($ids);
        }

        PermissionModel::query()->whereIn('slug', $slugs)->delete();
    }
};
