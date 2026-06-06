<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('staff_role', 20);
            $table->string('settlement_type', 20);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('PENDING');
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['official_shift_id', 'staff_user_id', 'settlement_type'], 'staff_settlements_shift_staff_type_unique');
            $table->index(['tenant_id', 'branch_id', 'status']);
        });

        Schema::create('staff_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_settlement_id')->constrained('staff_settlements')->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 30);
            $table->string('description');
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('percent', 5, 2)->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['sale_item_id', 'source_type'], 'staff_settlement_items_sale_source_unique');
            $table->index(['staff_settlement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_settlement_items');
        Schema::dropIfExists('staff_settlements');
    }
};
