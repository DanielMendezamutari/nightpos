<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_stock_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('to_site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('document_ref', 64)->nullable();
            $table->string('notes', 400)->nullable();
            $table->timestamp('transferred_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['from_site_id', 'transferred_at']);
            $table->index(['to_site_id', 'transferred_at']);
        });

        Schema::create('site_stock_transfer_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_stock_transfer_id')->constrained('site_stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->index(['site_stock_transfer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_stock_transfer_lines');
        Schema::dropIfExists('site_stock_transfers');
    }
};
