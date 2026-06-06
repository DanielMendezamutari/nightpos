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
            ['name' => 'Modo garzón — inicio', 'slug' => 'waiter.dashboard', 'roles' => ['waiter']],
            ['name' => 'Modo garzón — comandas', 'slug' => 'waiter.orders', 'roles' => ['waiter']],
            ['name' => 'Crear comanda', 'slug' => 'orders.create', 'roles' => ['waiter', 'cashier', 'cashier_senior', 'tenant_owner']],
            ['name' => 'Agregar ítems comanda', 'slug' => 'orders.add_items', 'roles' => ['waiter', 'cashier', 'cashier_senior', 'tenant_owner']],
            ['name' => 'Enviar comanda a barra', 'slug' => 'orders.send_to_bar', 'roles' => ['waiter', 'cashier', 'cashier_senior', 'tenant_owner']],
        ];

        foreach ($rows as $row) {
            $permission = PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            );

            foreach ($row['roles'] as $roleSlug) {
                foreach (RoleModel::query()->where('slug', $roleSlug)->get() as $role) {
                    $role->permissions()->syncWithoutDetaching([(int) $permission->id]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'waiter.dashboard',
            'waiter.orders',
            'orders.create',
            'orders.add_items',
            'orders.send_to_bar',
        ] as $slug) {
            PermissionModel::query()->where('slug', $slug)->delete();
        }
    }
};
