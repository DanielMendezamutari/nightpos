<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\RecetaController;
use App\Infrastructure\Persistence\Eloquent\Models\AlmacenModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoriaModel;
use App\Infrastructure\Persistence\Eloquent\Models\CompraModel;
use App\Infrastructure\Persistence\Eloquent\Models\InsumoModel;
use App\Infrastructure\Persistence\Eloquent\Models\KardexMovimientoModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioComprasTest extends TestCase
{
    use RefreshDatabase;

    private $almacen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->almacen = AlmacenModel::create([
            'nombre' => 'Cocina Principal',
            'descripcion' => 'AlmacÃ©n operativo de cocina',
            'es_interno' => true,
            'responsable' => 'Chef Ejecutivo',
            'activo' => true,
        ]);
    }

    public function test_crear_almacen_e_insumo_con_kardex_inicial(): void
    {
        $response = $this->postJson('/api/v1/insumos', [
            'almacen_id' => $this->almacen->id,
            'codigo' => 'INS-001',
            'nombre' => 'Carne Molida Especial',
            'unidad_medida' => 'KG',
            'costo_promedio' => 35.00,
            'ultimo_costo' => 35.00,
            'stock_actual' => 10.000,
            'stock_minimo' => 3.000,
            'stock_maximo' => 50.000,
        ]);

        $response->assertStatus(201);
        $insumoId = $response->json('id');

        $this->assertDatabaseHas('insumos', [
            'id' => $insumoId,
            'nombre' => 'Carne Molida Especial',
            'stock_actual' => 10.000,
        ]);

        // Verificar que se registrÃ³ el Kardex inicial
        $this->assertDatabaseHas('kardex_movimientos', [
            'insumo_id' => $insumoId,
            'tipo' => 'AJUSTE_POSITIVO',
            'cantidad' => 10.000,
            'referencia' => 'Inventario Inicial',
        ]);
    }

    public function test_compra_a_proveedor_incrementa_stock_y_recalcula_costo_promedio(): void
    {
        $proveedor = ProveedorModel::create([
            'nombre' => 'FrigorÃ­fico Bermejo',
            'nit' => '888999000',
            'telefono' => '76543210',
            'saldo_deuda' => 0,
            'activo' => true,
        ]);

        $insumo = InsumoModel::create([
            'almacen_id' => $this->almacen->id,
            'codigo' => 'INS-002',
            'nombre' => 'Pechuga de Pollo',
            'unidad_medida' => 'KG',
            'costo_promedio' => 20.0000,
            'ultimo_costo' => 20.0000,
            'stock_actual' => 10.000,
            'stock_minimo' => 5.000,
            'stock_maximo' => 100.000,
            'activo' => true,
        ]);

        // Compramos 20 Kg a 26.00 Bs/Kg al contado
        // Costo promedio ponderado esperado: ((10 * 20) + (20 * 26)) / 30 = (200 + 520) / 30 = 720 / 30 = 24.0000 Bs/Kg
        $response = $this->postJson('/api/v1/compras', [
            'proveedor_id' => $proveedor->id,
            'almacen_id' => $this->almacen->id,
            'nro_factura' => 'FAC-901',
            'metodo_pago' => 'CONTADO',
            'items' => [
                [
                    'insumo_id' => $insumo->id,
                    'cantidad' => 20.000,
                    'costo_unitario' => 26.00,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $insumo->refresh();
        $this->assertEquals(30.000, (float) $insumo->stock_actual);
        $this->assertEquals(24.0000, (float) $insumo->costo_promedio);
        $this->assertEquals(26.0000, (float) $insumo->ultimo_costo);

        // Movimiento en Kardex
        $this->assertDatabaseHas('kardex_movimientos', [
            'insumo_id' => $insumo->id,
            'tipo' => 'COMPRA',
            'cantidad' => 20.000,
            'stock_anterior' => 10.000,
            'stock_nuevo' => 30.000,
        ]);
    }

    public function test_compra_a_credito_y_pago_a_proveedor(): void
    {
        $proveedor = ProveedorModel::create([
            'nombre' => 'LÃ¡cteos del Valle',
            'nit' => '554433221',
            'saldo_deuda' => 0.00,
            'activo' => true,
        ]);

        $insumo = InsumoModel::create([
            'almacen_id' => $this->almacen->id,
            'nombre' => 'Queso Mozzarella',
            'unidad_medida' => 'KG',
            'costo_promedio' => 40.00,
            'stock_actual' => 5.000,
            'activo' => true,
        ]);

        // Compra a crÃ©dito de 10 Kg a 40 Bs = 400 Bs
        $resCompra = $this->postJson('/api/v1/compras', [
            'proveedor_id' => $proveedor->id,
            'almacen_id' => $this->almacen->id,
            'nro_factura' => 'CR-101',
            'metodo_pago' => 'CREDITO',
            'items' => [
                [
                    'insumo_id' => $insumo->id,
                    'cantidad' => 10.000,
                    'costo_unitario' => 40.00,
                ],
            ],
        ]);

        $resCompra->assertStatus(201);
        $proveedor->refresh();
        $this->assertEquals(400.00, (float) $proveedor->saldo_deuda);

        // Registrar pago a proveedor de 250 Bs
        $resPago = $this->postJson("/api/v1/proveedores/{$proveedor->id}/pago", [
            'monto' => 250.00,
            'metodo_pago' => 'TRANSFERENCIA',
            'referencia' => 'Pago parcial factura CR-101',
        ]);

        $resPago->assertStatus(200);
        $proveedor->refresh();
        $this->assertEquals(150.00, (float) $proveedor->saldo_deuda);

        $this->assertDatabaseHas('proveedor_pagos', [
            'proveedor_id' => $proveedor->id,
            'monto' => 250.00,
            'saldo_nuevo' => 150.00,
        ]);
    }

    public function test_ficha_tecnica_receta_y_descuento_automatico_por_venta(): void
    {
        $categoria = CategoriaModel::first() ?? CategoriaModel::create([
            'tenant_id' => TenantModel::first()->id,
            'nombre' => 'Hamburguesas',
            'activo' => true,
        ]);

        $producto = ProductoModel::create([
            'tenant_id' => TenantModel::first()->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Hamburguesa Especial',
            'precio' => 35.00,
            'costo' => 0.00,
            'activo' => true,
        ]);

        $pan = InsumoModel::create([
            'almacen_id' => $this->almacen->id,
            'nombre' => 'Pan Hamburguesa',
            'unidad_medida' => 'UNID',
            'costo_promedio' => 2.00,
            'stock_actual' => 50.000,
            'activo' => true,
        ]);

        $carne = InsumoModel::create([
            'almacen_id' => $this->almacen->id,
            'nombre' => 'Carne Molida',
            'unidad_medida' => 'KG',
            'costo_promedio' => 30.00,
            'stock_actual' => 10.000,
            'activo' => true,
        ]);

        // Guardar Ficha TÃ©cnica / Receta
        // Hamburguesa Especial requiere:
        // - 1 Pan (merma 0%)
        // - 0.200 Kg Carne Molida (merma 10%) -> 0.200 * 1.10 = 0.220 Kg reales
        $resReceta = $this->postJson("/api/v1/recetas/producto/{$producto->id}", [
            'ingredientes' => [
                [
                    'insumo_id' => $pan->id,
                    'cantidad' => 1.000,
                    'unidad_medida' => 'UNID',
                    'merma_porcentaje' => 0.00,
                ],
                [
                    'insumo_id' => $carne->id,
                    'cantidad' => 0.200,
                    'unidad_medida' => 'KG',
                    'merma_porcentaje' => 10.00,
                ],
            ],
        ]);

        $resReceta->assertStatus(200);

        // Costo teÃ³rico esperado: (1 * 2) + (0.220 * 30) = 2 + 6.60 = 8.60 Bs
        $producto->refresh();
        $this->assertEquals(8.60, (float) $producto->costo);

        // Descontar por venta de 3 Hamburguesas
        // Descuento pan: 3 * 1 = 3 unidades -> stock pan nuevo = 47
        // Descuento carne: 3 * 0.220 = 0.660 Kg -> stock carne nuevo = 9.340 Kg
        RecetaController::descontarInsumosPorVenta($producto->id, 3, 'Comanda Mesa #2');

        $pan->refresh();
        $carne->refresh();
        $this->assertEquals(47.000, (float) $pan->stock_actual);
        $this->assertEquals(9.340, (float) $carne->stock_actual);

        $this->assertDatabaseHas('kardex_movimientos', [
            'insumo_id' => $pan->id,
            'tipo' => 'CONSUMO_VENTA',
            'cantidad' => 3.000,
            'stock_nuevo' => 47.000,
        ]);

        $this->assertDatabaseHas('kardex_movimientos', [
            'insumo_id' => $carne->id,
            'tipo' => 'CONSUMO_VENTA',
            'cantidad' => 0.660,
            'stock_nuevo' => 9.340,
        ]);
    }

    public function test_ajuste_manual_de_stock_y_filtro_stock_bajo(): void
    {
        $insumo = InsumoModel::create([
            'almacen_id' => $this->almacen->id,
            'nombre' => 'Cerveza PaceÃ±a 620cc',
            'unidad_medida' => 'BOTELLA',
            'costo_promedio' => 12.00,
            'stock_actual' => 3.000,
            'stock_minimo' => 10.000,
            'activo' => true,
        ]);

        // Consultar con filtro de stock bajo
        $resBajoStock = $this->getJson('/api/v1/insumos?solo_bajo_stock=1');
        $resBajoStock->assertStatus(200);
        $this->assertTrue(collect($resBajoStock->json())->contains('id', $insumo->id));

        // Realizar ajuste manual por rotura de botella (MERMA)
        $resAjuste = $this->postJson("/api/v1/insumos/{$insumo->id}/ajuste", [
            'nuevo_stock' => 2.000,
            'tipo' => 'MERMA',
            'motivo' => 'Botella quebrada durante traslado',
        ]);

        $resAjuste->assertStatus(200);
        $insumo->refresh();
        $this->assertEquals(2.000, (float) $insumo->stock_actual);

        $this->assertDatabaseHas('kardex_movimientos', [
            'insumo_id' => $insumo->id,
            'tipo' => 'MERMA',
            'cantidad' => 1.000,
            'referencia' => 'Botella quebrada durante traslado',
        ]);
    }
}