<?php

declare(strict_types=1);

use App\Application\Cash\Services\CashMovementTaxonomyBackfillService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_movement_reasons', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_movement_reasons', 'default_movement_family')) {
                $table->string('default_movement_family', 20)->nullable()->after('type');
            }

            if (! Schema::hasColumn('cash_movement_reasons', 'default_movement_category')) {
                $table->string('default_movement_category', 60)->nullable()->after('default_movement_family');
            }
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_movements', 'movement_family')) {
                $table->string('movement_family', 20)->nullable()->after('source_id');
            }

            if (! Schema::hasColumn('cash_movements', 'movement_category')) {
                $table->string('movement_category', 60)->nullable()->after('movement_family');
            }

            $table->index(['tenant_id', 'branch_id', 'movement_family'], 'cash_movements_tenant_branch_family_idx');
            $table->index(['tenant_id', 'branch_id', 'movement_category'], 'cash_movements_tenant_branch_category_idx');
            $table->index(['cash_session_id', 'movement_category'], 'cash_movements_session_category_idx');
        });

        DB::table('cash_movement_reasons')
            ->whereRaw('LOWER(name) in (?, ?)', ['otros ingresos', 'otros'])
            ->update([
                'default_movement_family' => 'MANUAL',
                'default_movement_category' => 'MANUAL_INCOME',
            ]);

        DB::table('cash_movement_reasons')
            ->whereRaw('LOWER(name) in (?, ?, ?, ?, ?, ?, ?)', [
                'cena',
                'taxi cena',
                'taxi chica',
                'rotacion personal',
                'pago de almuerzos',
                'comision de taxi',
                'gastos mantenimiento',
            ])
            ->update([
                'default_movement_family' => 'EXPENSE',
                'default_movement_category' => 'OPERATING_EXPENSE',
            ]);

        DB::table('cash_movement_reasons')
            ->whereRaw('LOWER(name) in (?, ?, ?, ?)', [
                'pedido de punto frio',
                'compra de hielo',
                'pedido de casa belen al jefe',
                'pedidos de corona al jefe',
            ])
            ->update([
                'default_movement_family' => 'EXPENSE',
                'default_movement_category' => 'PURCHASE',
            ]);

        DB::table('cash_movement_reasons')
            ->whereRaw('LOWER(name) = ?', ['pago chicas'])
            ->update([
                'default_movement_family' => 'SETTLEMENT',
                'default_movement_category' => 'SETTLEMENT_GIRL_PAYMENT',
            ]);

        DB::table('cash_movement_reasons')
            ->whereRaw('LOWER(name) = ?', ['pago limpieza'])
            ->update([
                'default_movement_family' => 'SETTLEMENT',
                'default_movement_category' => 'SETTLEMENT_CLEANING_PAYMENT',
            ]);

        DB::table('cash_movement_reasons')
            ->whereRaw('LOWER(name) in (?, ?)', ['pago dj', 'reposición caja chica'])
            ->update([
                'default_movement_family' => 'EXPENSE',
                'default_movement_category' => 'OTHER_EXPENSE',
            ]);

        app(CashMovementTaxonomyBackfillService::class)->backfill();
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndex('cash_movements_tenant_branch_family_idx');
            $table->dropIndex('cash_movements_tenant_branch_category_idx');
            $table->dropIndex('cash_movements_session_category_idx');
            $table->dropColumn(['movement_family', 'movement_category']);
        });

        Schema::table('cash_movement_reasons', function (Blueprint $table) {
            $table->dropColumn(['default_movement_family', 'default_movement_category']);
        });
    }
};