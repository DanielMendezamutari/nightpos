<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('purchase_units_per_box')->nullable()->after('track_stock');
            $table->unsignedInteger('purchase_units_per_basket')->nullable()->after('purchase_units_per_box');
        });

        Schema::table('purchase_order_lines', function (Blueprint $table): void {
            $table->string('purchase_packaging', 16)->default('unit')->after('product_id');
            $table->unsignedInteger('units_per_pack')->default(1)->after('quantity');
            $table->unsignedInteger('packs_count')->nullable()->after('units_per_pack');
            $table->string('custom_pack_label', 48)->nullable()->after('packs_count');
            $table->unsignedInteger('cost_per_pack')->nullable()->after('unit_cost');
        });

        DB::table('purchase_order_lines')->whereNull('packs_count')->update([
            'packs_count' => DB::raw('quantity'),
            'units_per_pack' => 1,
            'purchase_packaging' => 'unit',
            'cost_per_pack' => DB::raw('unit_cost'),
        ]);
    }

    public function down(): void
    {
        Schema::table('purchase_order_lines', function (Blueprint $table): void {
            $table->dropColumn([
                'purchase_packaging',
                'units_per_pack',
                'packs_count',
                'custom_pack_label',
                'cost_per_pack',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['purchase_units_per_box', 'purchase_units_per_basket']);
        });
    }
};
