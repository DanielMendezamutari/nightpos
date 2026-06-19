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
            ['name' => 'Impresoras', 'slug' => 'settings.printers', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior']],
            ['name' => 'Gestionar impresoras', 'slug' => 'settings.printers.manage', 'roles' => ['tenant_owner', 'cashier_senior']],
            ['name' => 'Reimprimir tickets', 'slug' => 'printing.reprint', 'roles' => ['tenant_owner', 'cashier', 'cashier_senior', 'waiter']],
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
            'settings.printers',
            'settings.printers.manage',
            'printing.reprint',
        ] as $slug) {
            PermissionModel::query()->where('slug', $slug)->delete();
        }
    }
};
