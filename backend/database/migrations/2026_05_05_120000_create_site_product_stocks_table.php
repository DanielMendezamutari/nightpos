<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_product_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['site_id', 'product_id']);
        });

        $siteIds = DB::table('sites')->orderBy('id')->pluck('id');
        $firstSiteId = $siteIds->first();
        $products = DB::table('products')->orderBy('id')->get(['id', 'base_stock']);

        foreach ($siteIds as $siteId) {
            foreach ($products as $product) {
                $qty = ($siteId === $firstSiteId) ? (int) $product->base_stock : 0;
                DB::table('site_product_stocks')->insert([
                    'site_id' => $siteId,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($products as $product) {
            $sum = (int) DB::table('site_product_stocks')
                ->where('product_id', $product->id)
                ->sum('quantity');
            DB::table('products')->where('id', $product->id)->update([
                'base_stock' => $sum,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_product_stocks');
    }
};
