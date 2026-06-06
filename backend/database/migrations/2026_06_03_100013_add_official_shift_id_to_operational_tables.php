<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->foreignId('official_shift_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('official_shifts')
                ->nullOnDelete();
            $table->index(['official_shift_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('official_shift_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('official_shifts')
                ->nullOnDelete();
            $table->index(['official_shift_id', 'status']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('official_shift_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('official_shifts')
                ->nullOnDelete();
            $table->index(['official_shift_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('official_shift_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('official_shift_id');
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('official_shift_id');
        });
    }
};
