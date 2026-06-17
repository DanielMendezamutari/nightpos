<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $reasons = [
            ['type' => 'EXPENSE', 'name' => 'Pago cajera'],
            ['type' => 'EXPENSE', 'name' => 'Adelanto personal'],
        ];

        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            foreach ($reasons as $reason) {
                DB::table('cash_movement_reasons')->insertOrIgnore([
                    'tenant_id' => $tenantId,
                    'branch_id' => null,
                    'type' => $reason['type'],
                    'name' => $reason['name'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('cash_movement_reasons')
            ->whereIn('name', ['Pago cajera', 'Adelanto personal'])
            ->delete();
    }
};
