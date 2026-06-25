<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

/**
 * Hosting-safe repair: asegura permisos de fiscalización de caja y asignación a roles admin.
 *
 * Idempotente — no duplica slugs ni relaciones role_permissions.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        ['name' => 'Listar sesiones de caja admin', 'slug' => 'admin.cash_sessions.list'],
        ['name' => 'Ver detalle sesión de caja admin', 'slug' => 'admin.cash_sessions.view'],
        ['name' => 'Resumen fiscalización de cajas', 'slug' => 'admin.cash_sessions.summary'],
        ['name' => 'Cierre administrativo de caja', 'slug' => 'admin.cash_sessions.force_close'],
    ];

    /** @var list<string> */
    private const ROLE_SLUGS = [
        'super_admin',
        'tenant_owner',
        'cashier_senior',
    ];

    public function up(): void
    {
        $permissionIds = collect(self::PERMISSIONS)->map(
            fn (array $row) => PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            )->id,
        );

        foreach (self::ROLE_SLUGS as $roleSlug) {
            $role = RoleModel::query()->where('slug', $roleSlug)->first();

            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($permissionIds->all());
            }
        }
    }

    public function down(): void
    {
        // Permissions retained for data safety.
    }
};
