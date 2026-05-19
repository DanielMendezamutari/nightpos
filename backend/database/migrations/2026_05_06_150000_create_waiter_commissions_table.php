<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiter_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('base_amount');
            $table->decimal('rate_pct', 5, 2);
            $table->unsignedInteger('commission_amount');
            $table->timestamps();
            $table->index(['waiter_user_id', 'payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiter_commissions');
    }
};

