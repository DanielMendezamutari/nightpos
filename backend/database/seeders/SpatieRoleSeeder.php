<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Crea los roles de aplicación (guard api) y sincroniza model_has_roles desde users.role.
 * Usado por DatabaseSeeder y por la migración que alinea instalaciones antiguas.
 */
class SpatieRoleSeeder extends Seeder
{
    /** @var list<string> */
    public const APPLICATION_ROLES = [
        'owner',
        'super_admin',
        'admin',
        'manager',
        'cashier',
        'waiter',
    ];

    public static function ensureApplicationRolesExist(): void
    {
        foreach (self::APPLICATION_ROLES as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
        }
    }

    public static function syncAllUsersFromRoleColumn(): void
    {
        User::query()->each(function (User $user): void {
            if ($user->role) {
                Role::firstOrCreate(['name' => $user->role, 'guard_name' => 'api']);
                $user->syncRoles([$user->role]);
            }
        });
    }

    public function run(): void
    {
        self::ensureApplicationRolesExist();
        self::syncAllUsersFromRoleColumn();
    }
}
