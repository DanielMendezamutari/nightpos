<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            if (! Schema::hasColumn('room_services', 'gross_girl_amount')) {
                // Gross girl amount before cleaning deduction
                $table->decimal('gross_girl_amount', 12, 2)->nullable()->after('girl_percent');
            }
            if (! Schema::hasColumn('room_services', 'cleaning_amount')) {
                // Cleaning fee deducted from girl's gross amount (nullable = no cleaning deduction)
                $table->decimal('cleaning_amount', 12, 2)->nullable()->after('gross_girl_amount');
            }
        });

        // Backfill: existing records had girl_amount = gross (no cleaning deduction)
        DB::table('room_services')->whereNull('gross_girl_amount')->update([
            'gross_girl_amount' => DB::raw('girl_amount'),
            'cleaning_amount' => DB::raw('0.00'),
        ]);
    }

    public function down(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropColumn(['gross_girl_amount', 'cleaning_amount']);
        });
    }
};
