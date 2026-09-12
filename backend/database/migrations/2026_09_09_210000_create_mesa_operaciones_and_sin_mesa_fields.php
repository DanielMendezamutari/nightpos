<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->unsignedBigInteger('mesa_id')->nullable()->change();

            if (!Schema::hasColumn('visitas', 'tipo_despacho')) {
                $table->string('tipo_despacho', 30)->default('MESA')->after('mesa_id');
            }
            if (!Schema::hasColumn('visitas', 'telefono_cliente')) {
                $table->string('telefono_cliente', 30)->nullable()->after('cliente_nombre');
            }
            if (!Schema::hasColumn('visitas', 'direccion_envio')) {
                $table->text('direccion_envio')->nullable()->after('telefono_cliente');
            }
            if (!Schema::hasColumn('visitas', 'nombre_para_llevar')) {
                $table->string('nombre_para_llevar', 150)->nullable()->after('direccion_envio');
            }
        });

        if (!Schema::hasTable('mesa_operaciones_historial')) {
            Schema::create('mesa_operaciones_historial', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id');
                $table->uuid('branch_id');
                $table->string('tipo_operacion', 30);
                $table->foreignId('visita_id')->constrained('visitas')->cascadeOnDelete();
                $table->foreignId('mesa_origen_id')->nullable()->constrained('mesas')->nullOnDelete();
                $table->foreignId('mesa_destino_id')->nullable()->constrained('mesas')->nullOnDelete();
                $table->uuid('usuario_id');
                $table->string('motivo', 255)->nullable();
                $table->json('detalles_json')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'branch_id', 'tipo_operacion']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa_operaciones_historial');

        Schema::table('visitas', function (Blueprint $table) {
            if (Schema::hasColumn('visitas', 'nombre_para_llevar')) {
                $table->dropColumn('nombre_para_llevar');
            }
            if (Schema::hasColumn('visitas', 'direccion_envio')) {
                $table->dropColumn('direccion_envio');
            }
            if (Schema::hasColumn('visitas', 'telefono_cliente')) {
                $table->dropColumn('telefono_cliente');
            }
            if (Schema::hasColumn('visitas', 'tipo_despacho')) {
                $table->dropColumn('tipo_despacho');
            }
        });
    }
};
