<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use App\Services\Printing\TicketFormatterService;
use App\Services\Printing\WindowsDirectPrinterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpresionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ticket_formatter_generates_formatted_80mm_receipt(): void
    {
        $formatter = new TicketFormatterService();

        $factura = [
            'tipo_comprobante' => 'RECIBO',
            'nro_comprobante' => 'REC-000042',
            'razon_social' => 'CARLOS GUTIERREZ',
            'numero_documento' => '4567890',
            'mesa_numero' => 'MESA 5',
            'cajero' => 'Cajero Test',
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 75.00,
            'monto_recibido' => 100.00,
            'cambio' => 25.00,
            'detalles' => [
                [
                    'cantidad' => 2,
                    'producto_nombre' => 'Cerveza Huari 620ml',
                    'subtotal' => 50.00,
                ],
                [
                    'cantidad' => 1,
                    'producto_nombre' => 'Porcion de Papas Fritas',
                    'subtotal' => 25.00,
                ],
            ],
        ];

        $texto = $formatter->formatTicket($factura);

        $this->assertTrue(str_contains($texto, 'RIBERESTO POS'));
        $this->assertTrue(str_contains($texto, 'RECIBO DE CAJA'));
        $this->assertTrue(str_contains($texto, 'REC-000042'));
        $this->assertTrue(str_contains($texto, 'CARLOS GUTIERREZ'));
        $this->assertTrue(str_contains($texto, 'MESA 5'));
        $this->assertTrue(str_contains($texto, 'Cerveza Huari'));
        $this->assertTrue(str_contains($texto, '75.00'));
        $this->assertTrue(str_contains($texto, '25.00'));
    }

    public function test_ticket_formatter_generates_formatted_precuenta_restotech(): void
    {
        $formatter = new TicketFormatterService();

        $cuenta = [
            'mesa_numero' => 'MESA 2',
            'mesero' => 'Juan Perez',
            'cliente' => 'Familia Mendez',
            'fecha' => '2026-09-28 00:30:00',
            'monto_total' => 150.00,
            'detalles' => [
                [
                    'cantidad' => 1,
                    'producto_nombre' => 'Pique Macho Ribersoft',
                    'precio_unitario' => 95.00,
                    'subtotal' => 95.00,
                ],
                [
                    'cantidad' => 1,
                    'producto_nombre' => 'Silpancho Cochabambino',
                    'precio_unitario' => 55.00,
                    'subtotal' => 55.00,
                ],
            ],
        ];

        $rawPrecuenta = $formatter->formatPrecuenta($cuenta);

        $this->assertTrue(str_contains($rawPrecuenta, 'RIBERESTO POS'));
        $this->assertTrue(str_contains($rawPrecuenta, 'CUENTA'));
        $this->assertTrue(str_contains($rawPrecuenta, 'En Mesa'));
        $this->assertTrue(str_contains($rawPrecuenta, 'MESA 2'));
        $this->assertTrue(str_contains($rawPrecuenta, 'Juan Perez'));
        $this->assertTrue(str_contains($rawPrecuenta, 'DESCRIPCION'));
        $this->assertTrue(str_contains($rawPrecuenta, 'CANT.'));
        $this->assertTrue(str_contains($rawPrecuenta, 'TOTAL'));
        $this->assertTrue(str_contains($rawPrecuenta, 'Pique Macho Ribersoft'));
        $this->assertTrue(str_contains($rawPrecuenta, 'Silpancho Cochabambino'));
        $this->assertTrue(str_contains($rawPrecuenta, 'TOTAL A PAGAR:'));
        $this->assertTrue(str_contains($rawPrecuenta, '150.00'));
        $this->assertTrue(str_contains($rawPrecuenta, 'Desarrollado por Ribersoft: 67369293'));
    }

    public function test_listar_impresoras_endpoint(): void
    {
        $res = $this->getJson('/api/v1/impresion/impresoras');
        $res->assertStatus(200);
        $this->assertTrue($res->json('success'));
        $this->assertArrayHasKey('data', $res->json());
        $this->assertEquals('CAJA', $res->json('default_pos_printer'));
    }

    public function test_imprimir_ticket_inexistente_retorna_404(): void
    {
        $res = $this->postJson('/api/v1/impresion/ticket/99999');
        $res->assertStatus(404);
        $this->assertFalse($res->json('success'));
    }

    public function test_imprimir_precuenta_endpoint(): void
    {
        $mesa = MesaModel::with('salon')->first();
        $cajero = UserModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'cliente_nombre' => 'Familia Mendez',
            'estado' => 'ABIERTA',
            'pax' => 2,
            'total' => 150.00,
            'fecha_apertura' => now(),
        ]);

        $mesa->update(['estado' => 'OCUPADA']);

        $producto = ProductoModel::first();
        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'cantidad' => 2,
            'precio_unitario' => 75.00,
            'subtotal' => 150.00,
            'estado' => 'SERVIDO',
            'created_at' => now(),
        ]);

        $mockPrinter = $this->createMock(WindowsDirectPrinterService::class);
        $mockPrinter->method('imprimirPrecuenta')->willReturn([
            'success' => true,
            'message' => 'Simulacion de precuenta en CAJA',
            'printer' => 'CAJA',
        ]);
        $this->app->instance(WindowsDirectPrinterService::class, $mockPrinter);

        $res = $this->postJson("/api/v1/impresion/precuenta/{$mesa->id}");
        $res->assertStatus(200);
        $this->assertTrue($res->json('success'));
        $this->assertArrayHasKey('impresion', $res->json('data'));
    }

    public function test_cobro_mesa_incluye_campo_impresion_directa(): void
    {
        // 1. Abrir caja
        $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 150.00,
        ]);

        // 2. Crear mesa y visita con consumos
        $mesa = MesaModel::with('salon')->first();
        $this->assertNotNull($mesa);
        $cajero = UserModel::first();

        $visita = VisitaModel::create([
            'tenant_id' => $mesa->salon->tenant_id,
            'branch_id' => $mesa->salon->branch_id,
            'mesa_id' => $mesa->id,
            'mesero_id' => $cajero->id,
            'cliente_nombre' => 'Cliente Bar',
            'estado' => 'ABIERTA',
            'pax' => 2,
            'total' => 45.00,
            'fecha_apertura' => now(),
        ]);

        $mesa->update(['estado' => 'OCUPADA']);

        $producto = ProductoModel::first();
        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'cantidad' => 1,
            'precio_unitario' => 45.00,
            'subtotal' => 45.00,
            'estado' => 'SERVIDO',
            'created_at' => now(),
        ]);

        // Mock printer service to avoid touching real hardware during unit tests
        $mockPrinter = $this->createMock(WindowsDirectPrinterService::class);
        $mockPrinter->method('imprimirFactura')->willReturn([
            'success' => true,
            'message' => 'Simulacion de impresion en CAJA',
            'printer' => 'CAJA',
        ]);
        $this->app->instance(WindowsDirectPrinterService::class, $mockPrinter);

        // 3. Cobrar y facturar
        $resCobro = $this->postJson("/api/v1/mesas/{$mesa->id}/cobrar-facturar", [
            'metodo_pago' => 'EFECTIVO',
            'tipo_comprobante' => 'RECIBO',
            'monto_recibido' => 50.00,
            'imprimir_directo' => true,
        ]);

        $resCobro->assertStatus(200);
        $this->assertTrue($resCobro->json('success'));
        $this->assertArrayHasKey('factura', $resCobro->json('data'));
        $this->assertArrayHasKey('impresion_directa', $resCobro->json('data'));
    }
}
