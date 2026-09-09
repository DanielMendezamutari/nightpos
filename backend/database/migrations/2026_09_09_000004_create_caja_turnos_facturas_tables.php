<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('cajero_id');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->decimal('monto_inicial_bs', 12, 2)->default(0.00);
            $table->decimal('monto_inicial_usd', 12, 2)->default(0.00);
            $table->decimal('monto_final_bs', 12, 2)->nullable();
            $table->decimal('monto_final_usd', 12, 2)->nullable();
            $table->decimal('total_ventas_efectivo', 12, 2)->default(0.00);
            $table->decimal('total_ventas_qr', 12, 2)->default(0.00);
            $table->decimal('total_ventas_tarjeta', 12, 2)->default(0.00);
            $table->decimal('total_gastos', 12, 2)->default(0.00);
            $table->decimal('diferencia', 12, 2)->default(0.00);
            $table->string('estado', 20)->default('ABIERTO'); // ABIERTO, CERRADO
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('cajero_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['tenant_id', 'branch_id', 'estado']);
        });

        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->uuid('usuario_id');
            $table->string('tipo', 30)->default('EGRESO_GASTO'); // INGRESO, EGRESO_GASTO, RETIRO_CAJA
            $table->decimal('monto', 12, 2);
            $table->string('motivo', 200);
            $table->string('comprobante_nro', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['turno_id', 'tipo']);
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->foreignId('visita_id')->nullable()->constrained('visitas')->nullOnDelete();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->uuid('cajero_id');
            $table->unsignedBigInteger('nro_factura');
            $table->string('cuf', 100);
            $table->string('cufd', 100)->nullable();
            $table->string('tipo_documento', 20)->default('NIT'); // NIT, CI, CEX, PASAPORTE, OTRO
            $table->string('numero_documento', 30);
            $table->string('razon_social', 150);
            $table->string('correo', 150)->nullable();
            $table->string('metodo_pago', 30)->default('EFECTIVO'); // EFECTIVO, QR, TARJETA, MIXTO
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_efectivo', 12, 2)->default(0.00);
            $table->decimal('monto_cambio', 12, 2)->default(0.00);
            $table->string('estado', 20)->default('VALIDA'); // VALIDA, ANULADA
            $table->string('codigo_siat', 100)->nullable();
            $table->dateTime('fecha_emision');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('cajero_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['tenant_id', 'branch_id', 'nro_factura']);
            $table->index(['turno_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('movimientos_caja');
        Schema::dropIfExists('turnos');
    }
};