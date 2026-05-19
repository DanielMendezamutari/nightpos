<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('purchase_price')->default(0)->after('base_stock');
            $table->unsignedInteger('stock_min')->default(0)->after('purchase_price');
            $table->unsignedInteger('stock_max')->nullable()->after('stock_min');
            $table->boolean('track_stock')->default(true)->after('stock_max');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['purchase_price', 'stock_min', 'stock_max', 'track_stock']);
        });
    }
};
