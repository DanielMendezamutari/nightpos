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

        $this->assertStringContainsString('RIBERRESTO POS', $texto);
        $this->assertStringContainsString('RECIBO DE CAJA', $texto);
        $this->assertStringContainsString('REC-000042', $texto);
        $this->assertStringContainsString('CARLOS GUTIERREZ', $texto);
        $this->assertStringContainsString('MESA 5', $texto);
        $this->assertStringContainsString('Cerveza Huari', $texto);
        $this->assertStringContainsString('75.00', $texto);
        $this->assertStringContainsString('25.00', $texto);
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
