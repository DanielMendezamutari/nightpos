<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Almacenes
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('es_interno')->default(false);
            $table->string('responsable', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'activo']);
        });

        // 2. Proveedores
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->string('nombre', 150);
            $table->string('razon_social', 150)->nullable();
            $table->string('nit', 30)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('celular', 50)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('banco', 100)->nullable();
            $table->string('nro_cuenta', 50)->nullable();
            $table->string('titular_cuenta', 150)->nullable();
            $table->decimal('saldo_deuda', 12, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'activo']);
        });

        // 3. Insumos / Materias Primas de Stock
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 150);
            $table->string('unidad_medida', 20)->default('UNID'); // KG, GR, LT, ML, UNID, LATA, BOTELLA, PORCION
            $table->decimal('costo_promedio', 12, 4)->default(0.0000);
            $table->decimal('ultimo_costo', 12, 4)->default(0.0000);
            $table->decimal('stock_actual', 12, 3)->default(0.000);
            $table->decimal('stock_minimo', 12, 3)->default(5.000);
            $table->decimal('stock_maximo', 12, 3)->default(100.000);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'activo']);
            $table->index(['almacen_id', 'activo']);
        });

        // 4. Fichas TÃ©cnicas / Recetas (Ingredientes por plato del menÃº)
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 4); // ej: 0.150 Kg
            $table->string('unidad_medida', 20)->default('UNID');
            $table->decimal('merma_porcentaje', 5, 2)->default(0.00); // ej: 5% merma
            $table->timestamps();

            $table->index(['producto_id', 'insumo_id']);
        });

        // 5. Compras a Proveedores
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();
            $table->string('nro_factura', 50)->nullable();
            $table->dateTime('fecha_compra');
            $table->decimal('monto_total', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('ice', 12, 2)->default(0.00);
            $table->string('metodo_pago', 30)->default('CONTADO'); // CONTADO, CREDITO
            $table->string('estado', 30)->default('COMPLETADA'); // COMPLETADA, ANULADA
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'proveedor_id']);
        });

        // 6. Detalle de Compras
        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->decimal('cantidad', 12, 3);
            $table->decimal('costo_unitario', 12, 4);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['compra_id', 'insumo_id']);
        });

        // 7. Kardex Inmutable de Movimientos de Stock
        Schema::create('kardex_movimientos', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->string('tipo', 30); // COMPRA, CONSUMO_VENTA, AJUSTE_POSITIVO, AJUSTE_NEGATIVO, MERMA, TRASPASO
            $table->decimal('cantidad', 12, 3);
            $table->decimal('costo_unitario', 12, 4)->default(0.0000);
            $table->decimal('stock_anterior', 12, 3);
            $table->decimal('stock_nuevo', 12, 3);
            $table->string('referencia', 255)->nullable(); // ej: "Compra Fact #102", "Comanda Mesa #4", "Ajuste fÃ­sico"
            $table->timestamps();

            $table->index(['tenant_id', 'insumo_id']);
            $table->index(['almacen_id', 'tipo']);
        });

        // 8. Pagos / Abonos a Proveedores
        Schema::create('proveedor_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();
            $table->decimal('monto', 12, 2);
            $table->decimal('saldo_anterior', 12, 2);
            $table->decimal('saldo_nuevo', 12, 2);
            $table->string('metodo_pago', 30)->default('EFECTIVO');
            $table->string('referencia', 255)->nullable();
            $table->timestamps();

            $table->index(['proveedor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_pagos');
        Schema::dropIfExists('kardex_movimientos');
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('recetas');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('almacenes');
    }
};