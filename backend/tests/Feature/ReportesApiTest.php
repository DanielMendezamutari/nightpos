<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\AlmacenModel;
use App\Infrastructure\Persistence\Eloquent\Models\CompraModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\PropinaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function abrirTurno(): int
    {
        $res = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 100.0,
            'monto_inicial_usd' => 0.0,
            'observaciones' => 'Turno reporte',
        ]);
        return (int)$res->json('data.id');
    }

    public function test_puede_obtener_resumen_de_ventas(): void
    {
        $this->abrirTurno();
        $mesa1 = MesaModel::find(1);
        $cajero = UserModel::where('role', 'cajero')->first();
        $prod = ProductoModel::first();

        // Visita 1
        $visita1 = VisitaModel::create([
            'tenant_id' => $mesa1->salon->tenant_id,
            'branch_id' => $mesa1->salon->branch_id,
            'mesa_id' => $mesa1->id,
            'mesero_id' => $cajero->id,
            'pax' => 2,
            'fecha_apertura' => now(),
            'total' => 100.0,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita1->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 2,
            'precio_unitario' => 50.0,
            'subtotal' => 100.0,
            'estado' => 'MARCHADO',
        ]);

        $this->postJson("/api/v1/mesas/{$mesa1->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '1234567',
            'razon_social' => 'Cliente Test',
            'metodo_pago' => 'EFECTIVO',
            'monto_recibido' => 100.0,
        ])->assertStatus(200);

        // Visita 2
        $mesa2 = MesaModel::find(2);
        $visita2 = VisitaModel::create([
            'tenant_id' => $mesa2->salon->tenant_id,
            'branch_id' => $mesa2->salon->branch_id,
            'mesa_id' => $mesa2->id,
            'mesero_id' => $cajero->id,
            'pax' => 2,
            'fecha_apertura' => now(),
            'monto_total' => 150.0,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita2->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 3,
            'precio_unitario' => 50.0,
            'subtotal' => 150.0,
            'estado' => 'MARCHADO',
        ]);

        $this->postJson("/api/v1/mesas/{$mesa2->id}/cobrar-facturar", [
            'tipo_comprobante' => 'RECIBO',
            'tipo_documento' => 'CI',
            'numero_documento' => '7654321',
            'razon_social' => 'Cliente QR',
            'metodo_pago' => 'QR',
            'monto_recibido' => 150.0,
        ])->assertStatus(200);

        $res = $this->getJson('/api/v1/reportes/resumen-ventas?fecha_desde=' . now()->toDateString() . '&fecha_hasta=' . now()->toDateString());
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $this->assertEquals(250.0, (float)$res->json('data.total_ventas'));
        $this->assertEquals(2, $res->json('data.total_transacciones'));
        $this->assertEquals(100.0, (float)$res->json('data.desglose_pagos.efectivo'));
        $this->assertEquals(150.0, (float)$res->json('data.desglose_pagos.qr'));
    }

    public function test_puede_obtener_top_productos(): void
    {
        $this->abrirTurno();
        $mesa = MesaModel::first();
        $cajero = UserModel::where('role', 'cajero')->first();
        $prod1 = ProductoModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'pax' => 2,
            'fecha_apertura' => now(),
            'total' => (float)$prod1->precio * 5,
            'estado' => 'CERRADA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $prod1->id,
            'producto_nombre' => $prod1->nombre,
            'cantidad' => 5,
            'precio_unitario' => (float)$prod1->precio,
            'subtotal' => (float)$prod1->precio * 5,
            'estado' => 'MARCHADO',
        ]);

        $res = $this->getJson('/api/v1/reportes/top-productos?fecha_desde=' . now()->toDateString() . '&fecha_hasta=' . now()->toDateString());
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $this->assertNotEmpty($res->json('data'));
        $this->assertEquals($prod1->nombre, $res->json('data.0.nombre'));
        $this->assertEquals(5, $res->json('data.0.total_cantidad'));
    }

    public function test_puede_obtener_rendimiento_meseros_y_propinas(): void
    {
        $turnoId = $this->abrirTurno();
        $mesa = MesaModel::first();
        $user = UserModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $user->id,
            'pax' => 2,
            'fecha_apertura' => now(),
            'total' => 250.0,
            'estado' => 'CERRADA',
        ]);

        PropinaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'visita_id' => $visita->id,
            'turno_id' => $turnoId,
            'mesero_id' => $user->id,
            'monto_propina' => 25.0,
            'metodo_pago' => 'efectivo',
        ]);

        $res = $this->getJson('/api/v1/reportes/rendimiento-meseros?fecha_desde=' . now()->toDateString() . '&fecha_hasta=' . now()->toDateString());
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $mesero = collect($res->json('data'))->firstWhere('id', $user->id);
        $this->assertNotNull($mesero);
        $this->assertEquals(25.0, (float)$mesero['total_propinas']);
    }

    public function test_puede_obtener_balance_financiero(): void
    {
        $this->abrirTurno();
        $mesa = MesaModel::first();
        $cajero = UserModel::where('role', 'cajero')->first();
        $prod = ProductoModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'pax' => 2,
            'fecha_apertura' => now(),
            'total' => 500.0,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 5,
            'precio_unitario' => 100.0,
            'subtotal' => 500.0,
            'estado' => 'MARCHADO',
        ]);

        $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '1234567',
            'razon_social' => 'Cliente Test',
            'metodo_pago' => 'EFECTIVO',
            'monto_recibido' => 500.0,
        ])->assertStatus(200);

        // Obtener tipos de gastos y registrar un gasto vÃƒÂ­a API
        $resTipos = $this->getJson('/api/v1/caja/gastos/tipos');
        $tipoId = $resTipos->json('data.0.id');

        $this->postJson('/api/v1/caja/gastos', [
            'tipo_gasto_id' => $tipoId,
            'beneficiario' => 'Distribuidora Hielo',
            'motivo' => 'Compra de hielo urgente',
            'monto' => 50.0,
            'forma_pago' => 'EFECTIVO',
        ])->assertStatus(201);

        $almacen = AlmacenModel::create([
            'nombre' => 'AlmacÃƒÂ©n Central',
            'es_interno' => true,
            'activo' => true,
        ]);

        $proveedor = ProveedorModel::create([
            'nombre' => 'Distribuidora Carnes SA',
            'nit' => '123456789',
            'activo' => true,
        ]);

        CompraModel::create([
            'proveedor_id' => $proveedor->id,
            'almacen_id' => $almacen->id,
            'usuario_id' => $cajero->id,
            'nro_comprobante' => 'F-001',
            'fecha_compra' => now(),
            'monto_total' => 150.0,
            'tipo_pago' => 'contado',
            'monto_pagado' => 150.0,
            'estado' => 'completado',
        ]);

        $res = $this->getJson('/api/v1/reportes/balance-financiero?fecha_desde=' . now()->toDateString() . '&fecha_hasta=' . now()->toDateString());
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $this->assertEquals(500.0, (float)$res->json('data.total_ingresos_ventas'));
        $this->assertEquals(50.0, (float)$res->json('data.total_gastos_caja'));
        $this->assertEquals(150.0, (float)$res->json('data.total_compras_insumos'));
        $this->assertEquals(300.0, (float)$res->json('data.utilidad_operativa_bruta'));
    }
}