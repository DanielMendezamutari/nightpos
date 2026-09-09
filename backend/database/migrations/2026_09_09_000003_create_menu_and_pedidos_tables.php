<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('codigo', 20)->nullable();
            $table->string('nombre', 100);
            $table->string('icono', 50)->default('ri-restaurant-line');
            $table->string('color', 20)->default('primary');
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'activo', 'orden']);
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 12, 2)->default(0.00);
            $table->decimal('costo', 12, 2)->default(0.00);
            $table->unsignedInteger('tiempo_preparacion')->default(15); // minutos
            $table->string('destino_impresion', 30)->default('COCINA'); // COCINA, BAR, POSTRES
            $table->string('imagen', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['categoria_id', 'activo']);
            $table->index(['tenant_id', 'activo']);
        });

        Schema::create('observaciones_cocina', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('descripcion', 100);
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observaciones_cocina');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
    }
};