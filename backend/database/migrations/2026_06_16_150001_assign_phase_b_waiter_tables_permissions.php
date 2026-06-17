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
            ['name' => 'Mesas de servicio', 'slug' => 'settings.service_tables', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior']],
            ['name' => 'Gestionar mesas', 'slug' => 'settings.service_tables.manage', 'roles' => ['tenant_owner', 'cashier_senior']],
            ['name' => 'Asignar mesas a garzones', 'slug' => 'settings.waiter_assignments', 'roles' => ['tenant_owner', 'cashier_senior']],
            ['name' => 'Gestionar asignación mesas', 'slug' => 'settings.waiter_assignments.manage', 'roles' => ['tenant_owner', 'cashier_senior']],
            ['name' => 'Mis mesas garzón', 'slug' => 'waiter.my_tables', 'roles' => ['waiter']],
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
            'settings.service_tables',
            'settings.service_tables.manage',
            'settings.waiter_assignments',
            'settings.waiter_assignments.manage',
            'waiter.my_tables',
        ] as $slug) {
            PermissionModel::query()->where('slug', $slug)->delete();
        }
    }
};
