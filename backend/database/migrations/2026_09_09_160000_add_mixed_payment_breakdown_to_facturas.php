<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturas', 'monto_tarjeta')) {
                $table->decimal('monto_tarjeta', 12, 2)->default(0)->after('monto_efectivo');
            }
            if (!Schema::hasColumn('facturas', 'monto_qr')) {
                $table->decimal('monto_qr', 12, 2)->default(0)->after('monto_tarjeta');
            }
            if (!Schema::hasColumn('facturas', 'segundo_metodo_pago')) {
                $table->string('segundo_metodo_pago', 30)->nullable()->after('metodo_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['monto_tarjeta', 'monto_qr', 'segundo_metodo_pago']);
        });
    }
};
