<?php

use Database\Seeders\SpatieRoleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea Spatie con la columna users.role en despliegues que ya tenían usuarios
 * antes de integrar laravel-permission. Idempotente: puede ejecutarse varias veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('users')) {
            return;
        }

        SpatieRoleSeeder::ensureApplicationRolesExist();
        SpatieRoleSeeder::syncAllUsersFromRoleColumn();
    }

    public function down(): void
    {
        // No revertir pivotes: podría borrar asignaciones legítimas creadas después.
    }
};
