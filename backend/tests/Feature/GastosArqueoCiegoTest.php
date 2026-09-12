<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\GastoModel;
use App\Infrastructure\Persistence\Eloquent\Models\TipoGastoModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastosArqueoCiegoTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $cajero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->cajero = UserModel::where('role', 'cajero')->first() ?? UserModel::first();
        $this->getJson('/api/v1/caja/gastos/tipos');
    }

    public function test_puede_listar_tipos_de_gastos_con_defaults(): void
    {
        $response = $this->getJson('/api/v1/caja/gastos/tipos');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertGreaterThanOrEqual(8, count($data));
        
        $nombres = collect($data)->pluck('nombre')->toArray();
        $this->assertContains('Compras Menores de Mercado', $nombres);
        $this->assertContains('Hielo y Bebidas Urgentes', $nombres);
    }

    public function test_puede_registrar_gasto_y_descontar_de_caja_activa(): void
    {
        // Abrir turno
        $resTurno = $this->postJson('/api/v1/caja/abrir-turno', [
            'cajero_id' => $this->cajero->id,
            'monto_inicial_bs' => 500.00,
        ]);
        $resTurno->assertStatus(201);
        $turnoId = $resTurno->json('data.id');

        $tipo = TipoGastoModel::first();

        // Registrar gasto de 120 Bs en efectivo
        $resGasto = $this->postJson('/api/v1/caja/gastos', [
            'tipo_gasto_id' => $tipo->id,
            'monto' => 120.00,
            'beneficiario' => 'Distribuidora Don Hielo',
            'forma_pago' => 'EFECTIVO',
            'comprobante_nro' => 'REC-0099',
            'observaciones' => '5 bolsas de hielo para barra',
        ]);

        $resGasto->assertStatus(201)
            ->assertJson([
                'success' => true,
                'turno_total_gastos' => 120.00,
            ]);

        $this->assertDatabaseHas('gastos', [
            'turno_id' => $turnoId,
            'beneficiario' => 'Distribuidora Don Hielo',
            'monto' => 120.00,
            'estado' => 'ACTIVO',
        ]);

        // Verificar que el turno tiene total_gastos = 120
        $turno = TurnoModel::find($turnoId);
        $this->assertEquals(120.00, (float) $turno->total_gastos);

        // Consultar lista de gastos
        $resList = $this->getJson("/api/v1/caja/gastos?turno_id={$turnoId}");
        $resList->assertStatus(200)
            ->assertJson([
                'success' => true,
                'summary' => [
                    'total_gastos' => 120.00,
                    'total_efectivo' => 120.00,
                ],
            ]);
    }

    public function test_puede_anular_gasto_y_recalcular_balance_turno(): void
    {
        $resTurno = $this->postJson('/api/v1/caja/abrir-turno', [
            'cajero_id' => $this->cajero->id,
            'monto_inicial_bs' => 300.00,
        ]);
        $turnoId = $resTurno->json('data.id');

        $tipo = TipoGastoModel::first();

        $resGasto = $this->postJson('/api/v1/caja/gastos', [
            'tipo_gasto_id' => $tipo->id,
            'monto' => 80.00,
            'beneficiario' => 'Taxi Repuestos',
            'forma_pago' => 'EFECTIVO',
        ]);
        $gastoId = $resGasto->json('data.id');

        // Anular gasto
        $resAnular = $this->deleteJson("/api/v1/caja/gastos/{$gastoId}");
        $resAnular->assertStatus(200)
            ->assertJson(['success' => true]);

        $turno = TurnoModel::find($turnoId);
        $this->assertEquals(0.00, (float) $turno->total_gastos);
    }

    public function test_arqueo_ciego_exacto_cuadrado(): void
    {
        $resTurno = $this->postJson('/api/v1/caja/abrir-turno', [
            'cajero_id' => $this->cajero->id,
            'monto_inicial_bs' => 100.00,
        ]);
        $turnoId = $resTurno->json('data.id');

        // Simular que el turno tiene 100 inicial y sin ventas = esperado 100
        // Cajero cuenta: 1 billete de 100
        $resArqueo = $this->postJson('/api/v1/caja/arqueo-ciego', [
            'turno_id' => $turnoId,
            'b100' => 1,
        ]);

        $resArqueo->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_declarado' => 100.00,
                    'total_esperado' => 100.00,
                    'diferencia' => 0.00,
                    'resultado' => 'CUADRADO',
                ],
            ]);
    }

    public function test_arqueo_ciego_sobrante_y_faltante(): void
    {
        $resTurno = $this->postJson('/api/v1/caja/abrir-turno', [
            'cajero_id' => $this->cajero->id,
            'monto_inicial_bs' => 200.00,
        ]);
        $turnoId = $resTurno->json('data.id');

        // Sobrante: declara 250 (1 billete de 200 + 1 de 50)
        $resSobrante = $this->postJson('/api/v1/caja/arqueo-ciego', [
            'turno_id' => $turnoId,
            'b200' => 1,
            'b50' => 1,
        ]);
        $resSobrante->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'diferencia' => 50.00,
                    'resultado' => 'SOBRANTE',
                ],
            ]);

        // Faltante: declara 180 (1 billete de 100 + 4 de 20)
        $resFaltante = $this->postJson('/api/v1/caja/arqueo-ciego', [
            'turno_id' => $turnoId,
            'b100' => 1,
            'b20' => 4,
        ]);
        $resFaltante->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'diferencia' => -20.00,
                    'resultado' => 'FALTANTE',
                ],
            ]);
    }

    public function test_cierre_z_con_reporte_termico(): void
    {
        $resTurno = $this->postJson('/api/v1/caja/abrir-turno', [
            'cajero_id' => $this->cajero->id,
            'monto_inicial_bs' => 200.00,
        ]);
        $turnoId = $resTurno->json('data.id');

        // Cerrar con arqueo ciego
        $this->postJson('/api/v1/caja/arqueo-ciego', [
            'turno_id' => $turnoId,
            'b200' => 1,
            'cerrar_turno' => true,
            'notas' => 'Cierre de turno nocturno',
        ]);

        $turno = TurnoModel::find($turnoId);
        $this->assertEquals('CERRADO', $turno->estado);

        // Obtener Reporte Z
        $resReporte = $this->getJson("/api/v1/caja/reporte-cierre/{$turnoId}");
        $resReporte->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'turno' => [
                        'id' => $turnoId,
                        'estado' => 'CERRADO',
                    ],
                    'fondos' => [
                        'monto_inicial_bs' => 200.00,
                        'monto_final_declarado' => 200.00,
                    ],
                ],
            ]);
    }
}
