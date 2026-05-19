<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->after('site_id')->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('received')->after('notes');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['created_by', 'status', 'cancelled_at', 'cancelled_by']);
        });
    }
};
