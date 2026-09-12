<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_gastos', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('codigo', 20)->nullable();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->string('icono', 50)->default('ri-money-dollar-circle-line');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'activo']);
        });

        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->foreignId('tipo_gasto_id')->constrained('tipos_gastos')->cascadeOnDelete();
            $table->uuid('usuario_id');
            $table->string('beneficiario', 150);
            $table->decimal('monto', 12, 2);
            $table->string('forma_pago', 30)->default('EFECTIVO'); // EFECTIVO, QR, TRANSFERENCIA
            $table->string('comprobante_nro', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, ANULADO
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['turno_id', 'estado']);
        });

        Schema::create('turnos_arqueos_ciegos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->uuid('usuario_id');
            $table->unsignedInteger('b200')->default(0);
            $table->unsignedInteger('b100')->default(0);
            $table->unsignedInteger('b50')->default(0);
            $table->unsignedInteger('b20')->default(0);
            $table->unsignedInteger('b10')->default(0);
            $table->unsignedInteger('m5')->default(0);
            $table->unsignedInteger('m2')->default(0);
            $table->unsignedInteger('m1')->default(0);
            $table->unsignedInteger('m050')->default(0);
            $table->unsignedInteger('m020')->default(0);
            $table->unsignedInteger('m010')->default(0);
            $table->decimal('total_billetes', 12, 2)->default(0.00);
            $table->decimal('total_monedas', 12, 2)->default(0.00);
            $table->decimal('total_declarado', 12, 2)->default(0.00);
            $table->decimal('total_esperado', 12, 2)->default(0.00);
            $table->decimal('diferencia', 12, 2)->default(0.00);
            $table->string('resultado', 20)->default('CUADRADO'); // CUADRADO, SOBRANTE, FALTANTE
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['turno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_arqueos_ciegos');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('tipos_gastos');
    }
};
