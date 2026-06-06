<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_closures', function (Blueprint $table) {
            $table->decimal('total_cleaning_payouts', 12, 2)->nullable()->after('total_waiter_payouts');
        });
    }

    public function down(): void
    {
        Schema::table('shift_closures', function (Blueprint $table) {
            $table->dropColumn('total_cleaning_payouts');
        });
    }
};
