<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subcuentas_visita')) {
            Schema::create('subcuentas_visita', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id');
                $table->uuid('branch_id');
                $table->foreignId('visita_id')->constrained('visitas')->cascadeOnDelete();
                $table->unsignedInteger('numero_subcuenta')->default(1);
                $table->string('nombre_comensal', 100)->default('Cuenta Principal');
                $table->string('estado', 20)->default('PENDIENTE'); // PENDIENTE, COBRADA, ANULADA
                $table->decimal('total', 12, 2)->default(0.00);
                $table->decimal('monto_pagado', 12, 2)->default(0.00);
                $table->unsignedBigInteger('factura_id')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
                $table->index(['visita_id', 'estado']);
            });
        }

        if (Schema::hasTable('visita_detalles') && !Schema::hasColumn('visita_detalles', 'subcuenta_id')) {
            Schema::table('visita_detalles', function (Blueprint $table) {
                $table->unsignedBigInteger('subcuenta_id')->nullable()->after('visita_id');
                $table->index('subcuenta_id');
            });
        }

        if (!Schema::hasTable('pagos_parciales_visita')) {
            Schema::create('pagos_parciales_visita', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id');
                $table->uuid('branch_id');
                $table->foreignId('visita_id')->constrained('visitas')->cascadeOnDelete();
                $table->unsignedBigInteger('subcuenta_id')->nullable();
                $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
                $table->uuid('cajero_id');
                $table->decimal('monto', 12, 2);
                $table->string('metodo_pago', 30)->default('EFECTIVO'); // EFECTIVO, QR, TARJETA
                $table->string('comprobante_nro', 50)->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
                $table->foreign('cajero_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['visita_id', 'turno_id']);
            });
        }

        if (!Schema::hasTable('propinas')) {
            Schema::create('propinas', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id');
                $table->uuid('branch_id');
                $table->foreignId('visita_id')->constrained('visitas')->cascadeOnDelete();
                $table->unsignedBigInteger('subcuenta_id')->nullable();
                $table->unsignedBigInteger('factura_id')->nullable();
                $table->uuid('mesero_id');
                $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
                $table->decimal('monto_propina', 12, 2)->default(0.00);
                $table->decimal('porcentaje', 5, 2)->default(0.00);
                $table->string('metodo_pago', 30)->default('EFECTIVO');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
                $table->foreign('mesero_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['turno_id', 'mesero_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('propinas');
        Schema::dropIfExists('pagos_parciales_visita');
        if (Schema::hasColumn('visita_detalles', 'subcuenta_id')) {
            Schema::table('visita_detalles', function (Blueprint $table) {
                $table->dropColumn('subcuenta_id');
            });
        }
        Schema::dropIfExists('subcuentas_visita');
    }
};
