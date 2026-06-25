<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Restaurar apertura real cuando MySQL igualó opened_at con closed_at al cerrar.
        DB::statement('
            UPDATE cash_sessions
            SET opened_at = created_at
            WHERE closed_at IS NOT NULL
              AND created_at IS NOT NULL
              AND opened_at = closed_at
              AND created_at < closed_at
        ');

        // Evitar ON UPDATE CURRENT_TIMESTAMP en opened_at (bug clásico de TIMESTAMP en MySQL).
        DB::statement('ALTER TABLE cash_sessions MODIFY opened_at DATETIME NOT NULL');
        DB::statement('ALTER TABLE cash_sessions MODIFY closed_at DATETIME NULL');

        if (Schema::hasColumn('cash_sessions', 'forced_closed_at')) {
            DB::statement('ALTER TABLE cash_sessions MODIFY forced_closed_at DATETIME NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE cash_sessions MODIFY opened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE cash_sessions MODIFY closed_at TIMESTAMP NULL DEFAULT NULL');

        if (Schema::hasColumn('cash_sessions', 'forced_closed_at')) {
            DB::statement('ALTER TABLE cash_sessions MODIFY forced_closed_at TIMESTAMP NULL DEFAULT NULL');
        }
    }
};
