<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained('sites');
            $table->integer('monthly_fee');
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('suspended_reason')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamp('next_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('saas_subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites');
            $table->integer('amount');
            $table->unsignedInteger('months_covered')->default(1);
            $table->timestamp('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_subscription_payments');
        Schema::dropIfExists('saas_subscriptions');
    }
};
