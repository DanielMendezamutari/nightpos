<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salones', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->string('codigo', 20);
            $table->string('nombre', 100);
            $table->string('impresora_cuenta', 100)->nullable();
            $table->string('impresora_factura', 100)->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->index(['tenant_id', 'branch_id', 'activo']);
        });

        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salones')->cascadeOnDelete();
            $table->string('codigo', 20);
            $table->string('nombre', 50);
            $table->unsignedInteger('capacidad')->default(4);
            $table->integer('posicion_x')->default(0);
            $table->integer('posicion_y')->default(0);
            $table->integer('ancho')->default(100);
            $table->integer('alto')->default(100);
            $table->string('forma', 20)->default('cuadrada');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['salon_id', 'activo']);
            $table->unique(['salon_id', 'codigo']);
        });

        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->foreignId('mesa_id')->constrained('mesas')->cascadeOnDelete();
            $table->uuid('mesero_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('cliente_nombre', 150)->nullable();
            $table->unsignedInteger('personas')->default(2);
            $table->string('estado', 20)->default('ABIERTA'); // ABIERTA, PRECUENTA, COBRADA, ANULADA
            $table->boolean('imprimio_cuenta')->default(false);
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('total', 12, 2)->default(0.00);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('mesero_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['mesa_id', 'estado']);
            $table->index(['tenant_id', 'branch_id', 'estado']);
        });

        Schema::create('visita_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visita_id')->constrained('visitas')->cascadeOnDelete();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('producto_nombre', 150);
            $table->decimal('cantidad', 8, 2)->default(1.00);
            $table->decimal('precio_unitario', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->string('observaciones', 255)->nullable();
            $table->string('estado', 20)->default('EN_PREPARACION'); // EN_PREPARACION, SERVIDO, CANCELADO
            $table->timestamps();

            $table->index(['visita_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visita_detalles');
        Schema::dropIfExists('visitas');
        Schema::dropIfExists('mesas');
        Schema::dropIfExists('salones');
    }
};