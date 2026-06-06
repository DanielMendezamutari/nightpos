<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_settlement_items', function (Blueprint $table) {
            $table->unsignedBigInteger('source_id')->nullable()->after('order_id');
            $table->unique(['source_id', 'source_type'], 'staff_settlement_items_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('staff_settlement_items', function (Blueprint $table) {
            $table->dropUnique('staff_settlement_items_source_unique');
            $table->dropColumn('source_id');
        });
    }
};
