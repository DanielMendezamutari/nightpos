<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $expenseReasons = [
            'Limpieza',
            'Taxi',
            'Compra hielo',
            'Compra comida',
            'Multa',
            'Comisión garzón',
            'Pago chicas',
        ];

        $incomeReasons = [
            'Otros',
        ];

        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            foreach ($expenseReasons as $name) {
                DB::table('cash_movement_reasons')->insertOrIgnore([
                    'tenant_id' => $tenantId,
                    'branch_id' => null,
                    'type'      => 'EXPENSE',
                    'name'      => $name,
                    'status'    => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($incomeReasons as $name) {
                DB::table('cash_movement_reasons')->insertOrIgnore([
                    'tenant_id' => $tenantId,
                    'branch_id' => null,
                    'type'      => 'INCOME',
                    'name'      => $name,
                    'status'    => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('cash_movement_reasons')
            ->whereIn('name', ['Comisión garzón', 'Pago chicas'])
            ->delete();
    }
};
