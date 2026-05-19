<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('saas_discount_rules')->upsert([
            ['months_covered' => 3, 'discount_percent' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['months_covered' => 6, 'discount_percent' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['months_covered' => 12, 'discount_percent' => 20, 'created_at' => now(), 'updated_at' => now()],
        ], ['months_covered'], ['discount_percent', 'updated_at']);
    }

    public function down(): void
    {
        DB::table('saas_discount_rules')
            ->whereIn('months_covered', [3, 6, 12])
            ->delete();
    }
};
