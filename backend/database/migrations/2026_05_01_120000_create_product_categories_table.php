<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('product_type', 16)->default('drink');
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['slug' => 'soft_drinks', 'name' => 'Bebidas sin alcohol', 'sort_order' => 10, 'product_type' => 'drink'],
            ['slug' => 'beer', 'name' => 'Cervezas', 'sort_order' => 20, 'product_type' => 'drink'],
            ['slug' => 'wine_sparkling', 'name' => 'Vinos y espumantes', 'sort_order' => 30, 'product_type' => 'drink'],
            ['slug' => 'spirits', 'name' => 'Destilados', 'sort_order' => 40, 'product_type' => 'drink'],
            ['slug' => 'cocktails', 'name' => 'Tragos y cócteles', 'sort_order' => 50, 'product_type' => 'drink'],
            ['slug' => 'shots', 'name' => 'Shots', 'sort_order' => 60, 'product_type' => 'drink'],
            ['slug' => 'food', 'name' => 'Comida', 'sort_order' => 70, 'product_type' => 'drink'],
            ['slug' => 'cover', 'name' => 'Entradas y cover', 'sort_order' => 80, 'product_type' => 'drink'],
            ['slug' => 'merchandising', 'name' => 'Merchandising', 'sort_order' => 90, 'product_type' => 'drink'],
            ['slug' => 'supplies', 'name' => 'Insumos y barra', 'sort_order' => 100, 'product_type' => 'supply'],
        ];

        foreach ($rows as $r) {
            DB::table('product_categories')->insert([
                ...$r,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('name')->constrained('product_categories');
        });

        $drinkId = DB::table('product_categories')->where('slug', 'soft_drinks')->value('id');
        $supplyId = DB::table('product_categories')->where('slug', 'supplies')->value('id');

        DB::table('products')->where('product_type', 'drink')->update(['category_id' => $drinkId]);
        DB::table('products')->where('product_type', 'supply')->update(['category_id' => $supplyId]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
        Schema::dropIfExists('product_categories');
    }
};
