<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $siteIds = DB::table('sites')
            ->leftJoin('saas_subscriptions', 'saas_subscriptions.site_id', '=', 'sites.id')
            ->whereNull('saas_subscriptions.id')
            ->pluck('sites.id');

        foreach ($siteIds as $siteId) {
            DB::table('saas_subscriptions')->insert([
                'site_id' => $siteId,
                'monthly_fee' => 700,
                'status' => 'active',
                'suspended_reason' => null,
                'last_paid_at' => null,
                'next_due_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: data backfill migration should not delete live subscription records.
    }
};
