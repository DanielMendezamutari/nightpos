<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $slugs = [
            ['name' => 'Acceso habitaciones', 'slug' => 'rooms.access'],
            ['name' => 'Crear habitaciones', 'slug' => 'rooms.create'],
            ['name' => 'Actualizar habitaciones', 'slug' => 'rooms.update'],
            ['name' => 'Limpieza habitaciones', 'slug' => 'rooms.clean'],
            ['name' => 'Mantenimiento habitaciones', 'slug' => 'rooms.maintenance'],
        ];

        $ids = collect($slugs)->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        )->id);

        foreach (['tenant_owner', 'cashier'] as $roleSlug) {
            $role = RoleModel::query()->where('slug', $roleSlug)->first();
            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($ids->all());
            }
        }

        $cleaning = RoleModel::query()->where('slug', 'cleaning')->first();
        if ($cleaning !== null) {
            $cleaning->permissions()->syncWithoutDetaching(
                PermissionModel::query()->whereIn('slug', ['rooms.access', 'rooms.clean'])->pluck('id')->all()
            );
        }

        $super = RoleModel::query()->where('slug', 'super_admin')->first();
        if ($super !== null) {
            $super->permissions()->syncWithoutDetaching($ids->all());
        }
    }

    public function down(): void
    {
        PermissionModel::query()->whereIn('slug', [
            'rooms.access',
            'rooms.create',
            'rooms.update',
            'rooms.clean',
            'rooms.maintenance',
        ])->delete();
    }
};
