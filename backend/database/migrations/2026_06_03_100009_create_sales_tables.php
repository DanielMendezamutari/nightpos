<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sale_number', 30);
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('BOB');
            $table->string('payment_mode', 20);
            $table->string('status', 20)->default('PAID');
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->unique(['branch_id', 'sale_number']);
            $table->unique('order_id');
            $table->index(['tenant_id', 'branch_id', 'cash_session_id']);
            $table->index(['paid_at']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name_snapshot');
            $table->string('sale_mode', 30);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price_snapshot', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->foreignId('girl_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('girl_amount_snapshot', 12, 2)->nullable();
            $table->decimal('house_amount_snapshot', 12, 2)->nullable();
            $table->decimal('waiter_commission_percent_snapshot', 5, 2)->nullable();
            $table->decimal('waiter_commission_amount_snapshot', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['sale_id']);
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method', 20);
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['sale_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
