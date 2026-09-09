<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;

class ClienteAnticipoTest extends TestCase
{
    use RefreshDatabase;

    private $turnoId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $resAbrir = $this->postJson('/api/v1/caja/abrir-turno', [
            'monto_inicial_bs' => 300.00,
            'monto_inicial_usd' => 0.00,
            'observaciones' => 'Turno para pruebas de clientes',
        ]);
        $this->turnoId = $resAbrir->json('data.id');
    }

    public function test_puede_registrar_cliente_con_datos_fiscales_y_credito()
    {
        $response = $this->postJson('/api/v1/clientes', [
            'nombre' => 'Carlos',
            'apellidos' => 'Mendoza Roca',
            'ci_nit' => '4859231018',
            'tipo_documento' => 'NIT',
            'razon_social' => 'Mendoza Construcciones SRL',
            'celular' => '77348912',
            'correo' => 'carlos@mendoza.com',
            'cumpleanos' => '1988-09-15',
            'descuento_porcentaje' => 10.00,
            'limite_credito' => 1500.00,
            'permite_credito' => true,
            'comentarios' => 'Cliente VIP de gerencia',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nombre' => 'Carlos',
                    'ci_nit' => '4859231018',
                    'permite_credito' => true,
                    'descuento_porcentaje' => '10.00',
                ],
            ]);

        $this->assertDatabaseHas('clientes', [
            'ci_nit' => '4859231018',
            'razon_social' => 'Mendoza Construcciones SRL',
        ]);
    }

    public function test_puede_buscar_clientes_y_filtrar_deudores()
    {
        ClienteModel::create([
            'nombre' => 'Juan',
            'apellidos' => 'Perez',
            'ci_nit' => '112233',
            'saldo_deuda' => 250.00,
            'activo' => true,
        ]);

        ClienteModel::create([
            'nombre' => 'Maria',
            'apellidos' => 'Gomez',
            'ci_nit' => '445566',
            'saldo_deuda' => 0.00,
            'activo' => true,
        ]);

        // Busqueda por nombre
        $resSearch = $this->getJson('/api/v1/clientes?search=Juan');
        $resSearch->assertStatus(200);
        $this->assertCount(1, $resSearch->json('data'));
        $this->assertEquals('Juan', $resSearch->json('data.0.nombre'));

        // Filtro deudores
        $resDeudores = $this->getJson('/api/v1/clientes?solo_deudores=1');
        $resDeudores->assertStatus(200);
        $this->assertCount(1, $resDeudores->json('data'));
        $this->assertEquals('Juan', $resDeudores->json('data.0.nombre'));
    }

    public function test_puede_filtrar_cumpleaneros_del_mes()
    {
        ClienteModel::create([
            'nombre' => 'Roberto',
            'apellidos' => 'Suarez',
            'cumpleanos' => '1992-09-24',
            'activo' => true,
        ]);

        ClienteModel::create([
            'nombre' => 'Lucia',
            'apellidos' => 'Vaca',
            'cumpleanos' => '1995-12-10',
            'activo' => true,
        ]);

        $res = $this->getJson('/api/v1/clientes?cumpleaneros_mes=9');
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('data'));
        $this->assertEquals('Roberto', $res->json('data.0.nombre'));
    }

    public function test_puede_registrar_abono_a_cuenta_corriente_con_ingreso_a_caja()
    {
        $cliente = ClienteModel::create([
            'nombre' => 'Empresa Soluciones',
            'ci_nit' => '998877021',
            'saldo_deuda' => 500.00,
            'limite_credito' => 2000.00,
            'permite_credito' => true,
            'activo' => true,
        ]);

        $response = $this->postJson("/api/v1/clientes/{$cliente->id}/abono", [
            'monto' => 300.00,
            'metodo_pago' => 'EFECTIVO',
            'referencia' => 'Pago parcial cheque 1234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'monto_abonado' => 300.00,
                    'saldo_anterior' => 500.00,
                    'saldo_nuevo' => 200.00,
                ],
            ]);

        $cliente->refresh();
        $this->assertEquals(200.00, (float)$cliente->saldo_deuda);

        // Movimiento de cliente registrado
        $this->assertDatabaseHas('cliente_movimientos', [
            'cliente_id' => $cliente->id,
            'tipo' => 'ABONO_PAGO',
            'monto' => 300.00,
            'saldo_nuevo' => 200.00,
        ]);

        // Movimiento de caja registrado
        $this->assertDatabaseHas('movimientos_caja', [
            'turno_id' => $this->turnoId,
            'tipo' => 'INGRESO',
            'monto' => 300.00,
        ]);
    }

    public function test_puede_registrar_y_consultar_anticipos_para_reservas()
    {
        $cliente = ClienteModel::create([
            'nombre' => 'Ana Paula',
            'ci_nit' => '654321',
            'activo' => true,
        ]);

        $response = $this->postJson("/api/v1/clientes/{$cliente->id}/anticipos", [
            'monto' => 450.00,
            'concepto' => 'Anticipo Reserva CumpleaÃ±os 15 personas',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'monto_inicial' => '450.00',
                    'saldo_disponible' => '450.00',
                    'estado' => 'ACTIVO',
                ],
            ]);

        // Consultar anticipos disponibles
        $resDisp = $this->getJson("/api/v1/clientes/{$cliente->id}/anticipos-disponibles");
        $resDisp->assertStatus(200);
        $this->assertCount(1, $resDisp->json('data'));
        $this->assertEquals(450.00, (float)$resDisp->json('data.0.saldo_disponible'));
    }
}