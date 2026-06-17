<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_settlements', function (Blueprint $table) {
            $table->index(
                ['official_shift_id', 'staff_user_id', 'settlement_type'],
                'staff_settlements_shift_staff_lookup_idx',
            );
        });

        Schema::table('staff_settlements', function (Blueprint $table) {
            $table->dropUnique('staff_settlements_shift_staff_type_unique');
            $table->index(
                ['official_shift_id', 'staff_user_id', 'settlement_type', 'status'],
                'staff_settlements_shift_staff_type_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('staff_settlements', function (Blueprint $table) {
            $table->dropIndex('staff_settlements_shift_staff_type_status_idx');
            $table->unique(
                ['official_shift_id', 'staff_user_id', 'settlement_type'],
                'staff_settlements_shift_staff_type_unique',
            );
            $table->dropIndex('staff_settlements_shift_staff_lookup_idx');
        });
    }
};
