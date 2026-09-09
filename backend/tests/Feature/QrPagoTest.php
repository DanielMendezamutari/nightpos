<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\PagoQrModel;

class QrPagoTest extends TestCase
{
    use RefreshDatabase;

    private $mesa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->mesa = MesaModel::first();
    }

    public function test_puede_generar_codigo_qr_dinamico_con_monto()
    {
        $response = $this->postJson('/api/v1/pagos/qr/generar', [
            'mesa_id' => $this->mesa->id,
            'monto' => 125.50,
            'glosa' => 'Consumo Mesa RiberResto',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'monto' => 125.50,
                    'moneda' => 'BOB',
                    'estado' => 'PENDIENTE',
                ],
            ]);

        $this->assertDatabaseHas('pagos_qr', [
            'mesa_id' => $this->mesa->id,
            'monto' => 125.50,
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_puede_consultar_estado_en_tiempo_real()
    {
        $pago = PagoQrModel::create([
            'mesa_id' => $this->mesa->id,
            'codigo_transaccion' => 'QR-TEST-POLL-1',
            'monto' => 80.00,
            'moneda' => 'BOB',
            'glosa' => 'Prueba Polling',
            'estado' => 'PENDIENTE',
            'vence_at' => now()->addMinutes(10),
        ]);

        $response = $this->getJson('/api/v1/pagos/qr/estado/' . $pago->codigo_transaccion);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'codigo_transaccion' => 'QR-TEST-POLL-1',
                    'estado' => 'PENDIENTE',
                    'monto' => 80.00,
                ],
            ]);
    }

    public function test_webhook_bancario_confirma_pago()
    {
        $pago = PagoQrModel::create([
            'mesa_id' => $this->mesa->id,
            'codigo_transaccion' => 'QR-WH-TEST-99',
            'monto' => 250.00,
            'moneda' => 'BOB',
            'glosa' => 'Prueba Webhook',
            'estado' => 'PENDIENTE',
            'vence_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/pagos/qr/webhook', [
            'codigo_transaccion' => 'QR-WH-TEST-99',
            'referencia_bancaria' => 'REF-BCP-987654321',
            'banco' => 'BANCO DE CREDITO BCP',
            'estado' => 'PAGADO',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $pago->refresh();
        $this->assertEquals('PAGADO', $pago->estado);
        $this->assertEquals('REF-BCP-987654321', $pago->referencia_bancaria);
        $this->assertNotNull($pago->pagado_at);
    }

    public function test_simulacion_de_pago_para_pruebas_en_caja()
    {
        $pago = PagoQrModel::create([
            'mesa_id' => $this->mesa->id,
            'codigo_transaccion' => 'QR-SIM-TEST-77',
            'monto' => 60.00,
            'moneda' => 'BOB',
            'glosa' => 'Prueba SimulaciÃ³n',
            'estado' => 'PENDIENTE',
            'vence_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/pagos/qr/simular/' . $pago->codigo_transaccion);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'codigo_transaccion' => 'QR-SIM-TEST-77',
                    'estado' => 'PAGADO',
                ],
            ]);

        $pago->refresh();
        $this->assertEquals('PAGADO', $pago->estado);
        $this->assertNotNull($pago->pagado_at);
    }
}