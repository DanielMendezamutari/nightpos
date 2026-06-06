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
            ['name' => 'Motivos de caja', 'slug' => 'settings.cash_reasons', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior']],
            ['name' => 'Gestionar motivos de caja', 'slug' => 'settings.cash_reasons.manage', 'roles' => ['tenant_owner']],
            ['name' => 'Métodos de pago config', 'slug' => 'settings.payment_methods', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior']],
            ['name' => 'Gestionar métodos de pago', 'slug' => 'settings.payment_methods.manage', 'roles' => ['tenant_owner']],
            ['name' => 'Ambientes / mesas', 'slug' => 'settings.service_areas', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior']],
            ['name' => 'Gestionar ambientes', 'slug' => 'settings.service_areas.manage', 'roles' => ['tenant_owner']],
            ['name' => 'Tipos de habitación', 'slug' => 'settings.room_types', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior']],
            ['name' => 'Gestionar tipos habitación', 'slug' => 'settings.room_types.manage', 'roles' => ['tenant_owner']],
            ['name' => 'Checklist primera noche', 'slug' => 'settings.checklist', 'roles' => ['tenant_owner', 'super_admin']],
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
            'settings.cash_reasons',
            'settings.cash_reasons.manage',
            'settings.payment_methods',
            'settings.payment_methods.manage',
            'settings.service_areas',
            'settings.service_areas.manage',
            'settings.room_types',
            'settings.room_types.manage',
            'settings.checklist',
        ] as $slug) {
            PermissionModel::query()->where('slug', $slug)->delete();
        }
    }
};
