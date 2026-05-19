<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shift_turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites');
            $table->foreignId('cashier_user_id')->constrained('users');
            $table->enum('period', ['day', 'night']);
            $table->integer('opening_cash');
            $table->integer('closing_cash')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });

        Schema::create('companions', function (Blueprint $table) {
            $table->id();
            $table->string('stage_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->enum('product_type', ['drink', 'supply'])->default('drink');
            $table->integer('price_solo')->default(0);
            $table->integer('price_with_companion')->default(0);
            $table->integer('base_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites');
            $table->string('table_code')->nullable();
            $table->string('zone_code')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_turn_id')->constrained('shift_turns');
            $table->foreignId('customer_session_id')->constrained('customer_sessions');
            $table->foreignId('waiter_user_id')->constrained('users');
            $table->enum('status', ['pending', 'served', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('ordered_at');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('waiter_user_id')->constrained('users');
            $table->foreignId('companion_id')->nullable()->constrained('companions');
            $table->enum('consumption_type', ['solo', 'with_companion']);
            $table->unsignedInteger('quantity');
            $table->integer('unit_price');
            $table->integer('subtotal');
            $table->timestamp('registered_at');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('shift_turn_id')->constrained('shift_turns');
            $table->enum('method', ['cash', 'qr', 'card']);
            $table->integer('amount');
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('site_id')->constrained('sites');
            $table->enum('movement_type', ['sale_out', 'transfer_in', 'transfer_out', 'adjustment']);
            $table->integer('quantity');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('moved_at');
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('is_locked')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('companions');
        Schema::dropIfExists('shift_turns');
        Schema::dropIfExists('sites');
    }
};
