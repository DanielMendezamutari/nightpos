<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visita_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('visita_detalles', 'estacion_cocina')) {
                $table->string('estacion_cocina', 30)->default('COCINA')->after('observaciones');
            }
            if (!Schema::hasColumn('visita_detalles', 'iniciado_at')) {
                $table->dateTime('iniciado_at')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('visita_detalles', 'terminado_at')) {
                $table->dateTime('terminado_at')->nullable()->after('iniciado_at');
            }
            $table->index(['terminado_at', 'estacion_cocina']);
        });

        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'estacion_cocina')) {
                $table->string('estacion_cocina', 30)->default('COCINA')->after('destino_impresion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'estacion_cocina')) {
                $table->dropColumn('estacion_cocina');
            }
        });

        Schema::table('visita_detalles', function (Blueprint $table) {
            $table->dropIndex(['terminado_at', 'estacion_cocina']);
            $table->dropColumn(['estacion_cocina', 'iniciado_at', 'terminado_at']);
        });
    }
};
