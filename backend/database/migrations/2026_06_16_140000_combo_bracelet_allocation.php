<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('settlement_behavior', 40)->default('GIRL_LINE')->after('product_type');
            $table->unsignedInteger('bracelet_units_per_line')->default(1)->after('settlement_behavior');
            $table->boolean('requires_allocation')->default(false)->after('bracelet_units_per_line');
            $table->string('allocation_type', 40)->nullable()->after('requires_allocation');
        });

        Schema::create('order_item_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('girl_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('units');
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('allocation_type', 40);
            $table->timestamps();

            $table->index(['order_item_id']);
            $table->index(['girl_user_id']);
        });

        Schema::create('sale_item_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('girl_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('units');
            $table->decimal('unit_amount_snapshot', 12, 2);
            $table->decimal('total_amount_snapshot', 12, 2);
            $table->foreignId('source_order_item_allocation_id')->nullable()->constrained('order_item_allocations')->nullOnDelete();
            $table->string('allocation_type', 40);
            $table->timestamps();

            $table->index(['sale_item_id']);
            $table->index(['girl_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_allocations');
        Schema::dropIfExists('order_item_allocations');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_behavior',
                'bracelet_units_per_line',
                'requires_allocation',
                'allocation_type',
            ]);
        });
    }
};
