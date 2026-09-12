<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaFacturaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_flujo_completo_caja_turno_facturacion_siat(): void
    {
        // 1. Initially no active shift
        $res = $this->getJson('/api/v1/caja/turno-activo');
        $res->assertStatus(200);
        $this->assertNull($res->json('data'));

        // 2. Open shift with initial cash Bs. 200.00
        $resAbrir = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 200.00,
            'monto_inicial_usd' => 0.00,
            'observaciones' => 'Apertura de turno manana',
        ]);
        $resAbrir->assertStatus(201);
        $this->assertTrue($resAbrir->json('success'));
        $turnoId = $resAbrir->json('data.id');
        $this->assertNotNull($turnoId);

        // Cannot open a second shift when one is already open
        $resAbrir2 = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 100.00,
        ]);
        $resAbrir2->assertStatus(422);

        // 3. Shift is now active
        $resActivo = $this->getJson('/api/v1/caja/turno-activo');
        $resActivo->assertStatus(200);
        $this->assertEquals(200.00, $resActivo->json('data.monto_inicial_bs'));
        $this->assertEquals('ABIERTO', $resActivo->json('data.estado'));

        // 4. Register an expense movement (e.g. buying ice Bs. 25.00)
        $resGasto = $this->postJson('/api/v1/caja/movimientos', [
            'tipo' => 'EGRESO_GASTO',
            'monto' => 25.00,
            'motivo' => 'Compra de hielo urgente',
            'comprobante_nro' => 'REC-001',
        ]);
        $resGasto->assertStatus(201);

        // Verify movement listed
        $resMovs = $this->getJson('/api/v1/caja/movimientos');
        $resMovs->assertStatus(200);
        $this->assertCount(1, $resMovs->json('data'));

        // 5. Open table and add order items
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
            'total' => (float)$prod->precio,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 1,
            'precio_unitario' => (float)$prod->precio,
            'subtotal' => (float)$prod->precio,
            'notas' => 'Sin cebolla',
            'estado' => 'MARCHADO',
        ]);

        $montoPagar = (float)$prod->precio;

        // Cash validation: paying less than total must fail
        $resCobroFail = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '0',
            'razon_social' => 'Sin Nombre',
            'metodo_pago' => 'EFECTIVO',
            'monto_recibido' => $montoPagar - 10.00,
        ]);
        $resCobroFail->assertStatus(422);

        // 6. Charge and generate SIAT invoice (frmFacturacion1)
        $montoRecibido = $montoPagar + 20.00;
        $resCobro = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '54321098',
            'razon_social' => 'Empresa Los Andes SRL',
            'correo' => 'facturacion@losandes.com',
            'metodo_pago' => 'EFECTIVO',
            'monto_recibido' => $montoRecibido,
        ]);

        $resCobro->assertStatus(200);
        $this->assertTrue($resCobro->json('success'));
        $this->assertEquals('FACTURA', $resCobro->json('data.factura.tipo_comprobante'));
        $this->assertEquals($montoPagar, $resCobro->json('data.factura.monto_total'));
        $this->assertEquals(20.00, $resCobro->json('data.factura.cambio'));
        $this->assertNotEmpty($resCobro->json('data.factura.cuf'));
        $this->assertStringContainsString('https://siat.impuestos.gob.bo', $resCobro->json('data.factura.qr_url'));
        $this->assertNull($mesa->fresh()->visitaActiva);

        // 7. Verify Turno totals (Facturado)
        $resActivoDespues = $this->getJson('/api/v1/caja/turno-activo');
        $esperado = 200.00 + $montoPagar - 25.00;
        $this->assertEquals($montoPagar, $resActivoDespues->json('data.total_ventas_efectivo'));
        $this->assertEquals($montoPagar, $resActivoDespues->json('data.total_facturado'));
        $this->assertEquals(0.00, $resActivoDespues->json('data.total_recibos'));
        $this->assertEquals(25.00, $resActivoDespues->json('data.total_gastos'));
        $this->assertEquals($esperado, $resActivoDespues->json('data.efectivo_esperado'));

        // 8. List facturas
        $resFacturas = $this->getJson('/api/v1/facturas');
        $resFacturas->assertStatus(200);
        $this->assertCount(1, $resFacturas->json('data'));

        // 9. Close Shift (Arqueo de Turno)
        $resCerrar = $this->postJson('/api/v1/caja/cerrar-turno', [
            'turno_id' => $turnoId,
            'monto_final_bs' => $esperado,
            'monto_final_usd' => 0.00,
            'observaciones' => 'Cierre cuadrado perfecto',
        ]);
        $resCerrar->assertStatus(200);
        $this->assertEquals(0.00, $resCerrar->json('data.diferencia'));
        $this->assertEquals('CERRADO', $resCerrar->json('data.estado'));
    }

    public function test_cobro_mesa_con_recibo_sin_factura(): void
    {
        // 1. Open shift
        $resAbrir = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 150.00,
        ]);
        $this->assertTrue($resAbrir->json('success'));

        // 2. Open table and add items
        $mesa = MesaModel::first();
        $cajero = UserModel::where('role', 'cajero')->first();
        $prod = ProductoModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'pax' => 1,
            'fecha_apertura' => now(),
            'total' => (float)$prod->precio,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 1,
            'precio_unitario' => (float)$prod->precio,
            'subtotal' => (float)$prod->precio,
            'estado' => 'MARCHADO',
        ]);

        // 3. Charge with RECIBO (Sin Factura)
        $resCobro = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'RECIBO',
            'metodo_pago' => 'EFECTIVO',
            'monto_recibido' => (float)$prod->precio,
        ]);

        $resCobro->assertStatus(200);
        $this->assertTrue($resCobro->json('success'));
        $this->assertEquals('RECIBO', $resCobro->json('data.factura.tipo_comprobante'));
        $this->assertStringStartsWith('REC-', $resCobro->json('data.factura.nro_comprobante'));
        $this->assertNull($resCobro->json('data.factura.qr_url'));
        $this->assertNull($mesa->fresh()->visitaActiva);

        // 4. Verify Turno totals (Recibo increments total_recibos and cash)
        $resTurno = $this->getJson('/api/v1/caja/turno-activo');
        $this->assertEquals((float)$prod->precio, $resTurno->json('data.total_ventas_efectivo'));
        $this->assertEquals(0.00, $resTurno->json('data.total_facturado'));
        $this->assertEquals((float)$prod->precio, $resTurno->json('data.total_recibos'));
    }

    public function test_anular_factura_recalcula_turno(): void
    {
        // Open shift
        $resAbrir = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 100.00,
        ]);
        $turnoId = $resAbrir->json('data.id');

        $mesa = MesaModel::first();
        $cajero = UserModel::where('role', 'cajero')->first();
        $prod = ProductoModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'pax' => 1,
            'fecha_apertura' => now(),
            'total' => (float)$prod->precio,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 1,
            'precio_unitario' => (float)$prod->precio,
            'subtotal' => (float)$prod->precio,
            'estado' => 'MARCHADO',
        ]);

        $resCobro = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '123456',
            'razon_social' => 'Cliente Test',
            'metodo_pago' => 'EFECTIVO',
            'monto_recibido' => (float)$prod->precio,
        ]);
        $facturaId = $resCobro->json('data.factura.id');

        // Verify sales recorded
        $resActivo = $this->getJson('/api/v1/caja/turno-activo');
        $this->assertEquals((float)$prod->precio, $resActivo->json('data.total_ventas_efectivo'));

        // Anular factura
        $resAnular = $this->postJson("/api/v1/facturas/{$facturaId}/anular", [
            'motivo' => 'Error de digitacion en Razon Social solicitado por cliente',
        ]);
        $resAnular->assertStatus(200);
        $this->assertEquals('ANULADA', $resAnular->json('data.estado'));

        // Turno sales should now be 0
        $resActivo2 = $this->getJson('/api/v1/caja/turno-activo');
        $this->assertEquals(0.00, $resActivo2->json('data.total_ventas_efectivo'));
    }

    public function test_cobro_mesa_con_pago_mixto_efectivo_y_tarjeta(): void
    {
        // 1. Open shift
        $resAbrir = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 100.00,
        ]);
        $resAbrir->assertStatus(201);

        $mesa = MesaModel::first();
        $cajero = UserModel::where('role', 'cajero')->first();
        $prod = ProductoModel::first();
        $totalCuenta = (float)$prod->precio * 2;

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'pax' => 2,
            'fecha_apertura' => now(),
            'total' => $totalCuenta,
            'estado' => 'ABIERTA',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'cantidad' => 2,
            'precio_unitario' => (float)$prod->precio,
            'subtotal' => $totalCuenta,
            'estado' => 'MARCHADO',
        ]);

        $montoEf = round($totalCuenta * 0.6, 2);
        $montoTar = round($totalCuenta - $montoEf, 2);

        // Test validation: rejection if difference between amounts
        $resDiff = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '44556677',
            'razon_social' => 'EMPRESA TEST MIXTO',
            'metodo_pago' => 'MIXTO',
            'monto_mixto_efectivo' => $montoEf,
            'monto_mixto_digital' => $montoTar + 10.00,
            'segundo_metodo_pago' => 'TARJETA',
        ]);
        $resDiff->assertStatus(422);
        $resDiff->assertJsonPath('message', 'Hay diferencia entre el monto para pagar y los montos seleccionados');

        // Test valid RestoTech payment with card mask 4568000000001234
        $resCobro = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'tipo_comprobante' => 'FACTURA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '44556677',
            'razon_social' => 'EMPRESA TEST MIXTO',
            'metodo_pago' => 'MIXTO',
            'monto_mixto_efectivo' => $montoEf,
            'monto_mixto_digital' => $montoTar,
            'segundo_metodo_pago' => 'TARJETA',
            'datos_adicionales' => [
                'tarjeta_ini' => '4568',
                'tarjeta_fin' => '1234',
            ],
        ]);

        $resCobro->assertStatus(200);
        $resCobro->assertJsonPath('data.factura.metodo_pago', 'MIXTO');
        $resCobro->assertJsonPath('data.factura.nro_tarjeta', '4568000000001234');

        // Check DB
        $this->assertDatabaseHas('facturas', [
            'metodo_pago' => 'MIXTO',
            'segundo_metodo_pago' => 'TARJETA',
            'nro_tarjeta' => '4568000000001234',
            'monto_total' => $totalCuenta,
            'monto_efectivo' => $montoEf,
            'monto_tarjeta' => $montoTar,
        ]);

        // Check Turno recalculation
        $resActivo = $this->getJson('/api/v1/caja/turno-activo');
        $resActivo->assertStatus(200);
        $this->assertEquals($montoEf, (float)$resActivo->json('data.total_ventas_efectivo'));
        $this->assertEquals($montoTar, (float)$resActivo->json('data.total_ventas_tarjeta'));
        $this->assertEquals(0.00, (float)$resActivo->json('data.total_ventas_qr'));
        $this->assertEquals($totalCuenta, (float)$resActivo->json('data.total_facturado'));
    }
}
