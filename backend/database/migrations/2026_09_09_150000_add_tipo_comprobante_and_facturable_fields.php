<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturas', 'tipo_comprobante')) {
                $table->string('tipo_comprobante', 20)->default('FACTURA')->after('visita_id');
            }
            if (!Schema::hasColumn('facturas', 'nro_comprobante')) {
                $table->string('nro_comprobante', 50)->nullable()->after('nro_factura');
            }
        });

        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'facturable')) {
                $table->boolean('facturable')->default(true)->after('descripcion');
            }
        });

        Schema::table('turnos', function (Blueprint $table) {
            if (!Schema::hasColumn('turnos', 'total_facturado')) {
                $table->decimal('total_facturado', 12, 2)->default(0)->after('total_ventas_tarjeta');
                $table->decimal('total_recibos', 12, 2)->default(0)->after('total_facturado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturas', 'tipo_comprobante')) {
                $table->dropColumn(['tipo_comprobante', 'nro_comprobante']);
            }
        });

        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'facturable')) {
                $table->dropColumn('facturable');
            }
        });

        Schema::table('turnos', function (Blueprint $table) {
            if (Schema::hasColumn('turnos', 'total_facturado')) {
                $table->dropColumn(['total_facturado', 'total_recibos']);
            }
        });
    }
};