<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('room_services')
            ->where('status', 'ACTIVE')
            ->whereNotNull('expected_ends_at')
            ->where('expected_ends_at', '<=', now())
            ->update(['status' => 'DUE']);
    }

    public function down(): void
    {
        DB::table('room_services')
            ->where('status', 'DUE')
            ->update(['status' => 'ACTIVE']);
    }
};
