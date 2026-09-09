<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_qr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visita_id')->nullable()->constrained('visitas')->nullOnDelete();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->string('codigo_transaccion', 64)->unique();
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 10)->default('BOB');
            $table->string('glosa')->default('Consumo Restaurante - RiberResto POS');
            $table->string('estado', 20)->default('PENDIENTE'); // PENDIENTE, PAGADO, EXPIRADO, CANCELADO
            $table->text('qr_payload')->nullable();
            $table->string('banco')->nullable();
            $table->string('referencia_bancaria')->nullable();
            $table->timestamp('vence_at')->nullable();
            $table->timestamp('pagado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_qr');
    }
};
