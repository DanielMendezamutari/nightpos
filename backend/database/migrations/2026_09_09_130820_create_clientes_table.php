<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('nombre');
            $table->string('apellidos')->nullable();
            $table->string('ci_nit')->nullable()->index();
            $table->string('tipo_documento', 20)->default('NIT');
            $table->string('razon_social')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('direccion')->nullable();
            $table->date('cumpleanos')->nullable();
            $table->decimal('descuento_porcentaje', 5, 2)->default(0.00);
            $table->decimal('limite_credito', 10, 2)->default(0.00);
            $table->decimal('saldo_deuda', 10, 2)->default(0.00);
            $table->boolean('permite_credito')->default(false);
            $table->text('comentarios')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};