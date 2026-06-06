<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['name' => 'Setup plataforma SaaS', 'slug' => 'platform.setup', 'roles' => ['super_admin']],
            ['name' => 'Alta rápida garzón', 'slug' => 'staff.quick_create_waiter', 'roles' => ['tenant_owner']],
            ['name' => 'Acceso tipos de show', 'slug' => 'show_types.access', 'roles' => ['tenant_owner', 'cashier']],
            ['name' => 'Crear tipos de show', 'slug' => 'show_types.create', 'roles' => ['tenant_owner', 'cashier']],
            ['name' => 'Actualizar tipos de show', 'slug' => 'show_types.update', 'roles' => ['tenant_owner']],
            ['name' => 'Alta rápida precio producto', 'slug' => 'product_prices.quick_create', 'roles' => ['tenant_owner']],
            ['name' => 'Fuentes pendientes liquidación', 'slug' => 'settlements.pending_sources', 'roles' => ['tenant_owner', 'cashier']],
        ];

        foreach ($rows as $row) {
            $permission = PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            );

            foreach ($row['roles'] as $roleSlug) {
                $roles = RoleModel::query()->where('slug', $roleSlug)->get();

                foreach ($roles as $role) {
                    $role->permissions()->syncWithoutDetaching([(int) $permission->id]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'platform.setup',
            'staff.quick_create_waiter',
            'show_types.access',
            'show_types.create',
            'show_types.update',
            'product_prices.quick_create',
            'settlements.pending_sources',
        ] as $slug) {
            PermissionModel::query()->where('slug', $slug)->delete();
        }
    }
};
