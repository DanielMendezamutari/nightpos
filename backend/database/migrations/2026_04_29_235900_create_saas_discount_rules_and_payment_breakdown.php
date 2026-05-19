<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_discount_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('months_covered')->unique();
            $table->unsignedInteger('discount_percent');
            $table->timestamps();
        });

        Schema::table('saas_subscription_payments', function (Blueprint $table) {
            $table->integer('base_amount')->default(0)->after('amount');
            $table->unsignedInteger('discount_percent')->default(0)->after('base_amount');
            $table->integer('discount_amount')->default(0)->after('discount_percent');
            $table->integer('final_amount')->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('saas_subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['base_amount', 'discount_percent', 'discount_amount', 'final_amount']);
        });

        Schema::dropIfExists('saas_discount_rules');
    }
};
