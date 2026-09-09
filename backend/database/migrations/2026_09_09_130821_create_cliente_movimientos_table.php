<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();
            $table->string('tipo', 30); // CARGO_CONSUMO, ABONO_PAGO, AJUSTE
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo_anterior', 10, 2);
            $table->decimal('saldo_nuevo', 10, 2);
            $table->foreignId('visita_id')->nullable()->constrained('visitas')->nullOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            $table->string('referencia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_movimientos');
    }
};