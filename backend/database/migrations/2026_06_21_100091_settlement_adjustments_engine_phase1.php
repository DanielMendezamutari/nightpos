<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('staff_settlements', 'gross_amount')) {
            Schema::table('staff_settlements', function (Blueprint $table) {
                $table->decimal('gross_amount', 12, 2)->default(0)->after('total_amount');
                $table->decimal('adjustments_total', 12, 2)->default(0)->after('gross_amount');
                $table->decimal('net_amount', 12, 2)->default(0)->after('adjustments_total');
            });

            DB::table('staff_settlements')->update([
                'gross_amount' => DB::raw('total_amount'),
                'net_amount' => DB::raw('total_amount'),
                'adjustments_total' => 0,
            ]);
        }

        if (! Schema::hasTable('staff_settlement_adjustments')) {
            Schema::create('staff_settlement_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('staff_settlement_id')->constrained('staff_settlements')->cascadeOnDelete();
                $table->string('adjustment_type', 40);
                $table->decimal('amount', 12, 2);
                $table->string('discount_mode', 20)->nullable();
                $table->decimal('discount_value', 12, 2)->nullable();
                $table->decimal('calculation_base', 12, 2)->nullable();
                $table->text('notes')->nullable();
                $table->string('dedup_key', 120)->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique('dedup_key', 'ssa_dedup_unique');
                $table->index(['staff_settlement_id', 'adjustment_type'], 'ssa_settlement_type_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_settlement_adjustments');

        Schema::table('staff_settlements', function (Blueprint $table) {
            $table->dropColumn(['gross_amount', 'adjustments_total', 'net_amount']);
        });
    }
};
