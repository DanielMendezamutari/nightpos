<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->string('source_type', 30)->nullable()->after('notes');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });

        foreach (['bracelets', 'room_services', 'shows'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('cash_session_id')->nullable()->after('official_shift_id')->constrained('cash_sessions')->nullOnDelete();
                $table->string('payment_method', 20)->nullable()->after('total_amount');
                $table->foreignId('cash_movement_id')->nullable()->after('payment_method')->constrained('cash_movements')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['bracelets', 'room_services', 'shows'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('cash_movement_id');
                $table->dropConstrainedForeignId('cash_session_id');
                $table->dropColumn('payment_method');
            });
        }

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
