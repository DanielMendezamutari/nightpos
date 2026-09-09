<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anticipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();
            $table->foreignId('visita_id')->nullable()->constrained('visitas')->nullOnDelete();
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('saldo_disponible', 10, 2);
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, APLICADO, DEVUELTO
            $table->string('concepto')->default('Anticipo de Reserva / Evento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anticipos');
    }
};