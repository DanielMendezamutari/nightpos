<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\SubcuentaVisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SepararCuentasTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private UserModel $cajero;
    private MesaModel $mesa;
    private VisitaModel $visita;
    private TurnoModel $turno;
    private ProductoModel $prod1;
    private ProductoModel $prod2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->cajero = UserModel::where('role', 'cajero')->first() ?? UserModel::first();

        $login = $this->postJson('/api/v1/auth/login-pin', [
            'pin' => '1234',
        ]);
        $this->token = $login->json('data.access_token') ?? '';

        // Abrir turno
        $resTurno = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/caja/abrir-turno', [
                'monto_inicial_bs' => 200.00,
            ]);
        $this->turno = TurnoModel::first();

        // Obtener mesa libre
        $this->mesa = MesaModel::first();
        // Mesa activa via visita

        $productos = ProductoModel::take(2)->get();
        $this->prod1 = $productos[0];
        $this->prod2 = $productos[1];

        // Crear visita con 2 items
        $this->visita = VisitaModel::create([
            'tenant_id' => $this->cajero->tenant_id,
            'branch_id' => $this->cajero->branch_id,
            'mesa_id' => $this->mesa->id,
            'mesero_id' => $this->cajero->id,
            'fecha_apertura' => now(),
            'estado' => 'ABIERTA',
            'total' => 150.00,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $this->visita->id,
            'producto_id' => $this->prod1->id,
            'producto_nombre' => $this->prod1->nombre,
            'cantidad' => 2,
            'precio_unitario' => 40.00,
            'subtotal' => 80.00,
            'estado' => 'SERVIDO',
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $this->visita->id,
            'producto_id' => $this->prod2->id,
            'producto_nombre' => $this->prod2->nombre,
            'cantidad' => 1,
            'precio_unitario' => 70.00,
            'subtotal' => 70.00,
            'estado' => 'SERVIDO',
        ]);
    }

    public function test_puede_obtener_subcuentas_e_inicializa_cuenta_principal(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.total_visita', 150);
        $this->assertCount(1, $response->json('data.subcuentas'));
        $this->assertEquals('Cuenta Principal', $response->json('data.subcuentas.0.nombre_comensal'));
    }

    public function test_puede_crear_subcuenta_y_mover_item_completo(): void
    {
        // 1. Inicializar subcuentas
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        // 2. Crear subcuenta 2
        $resSub = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/crear", [
                'nombre_comensal' => 'Amigo Carlos',
            ]);
        $resSub->assertStatus(201);
        $subcuenta2Id = $resSub->json('data.id');

        // 3. Obtener id del detalle de Pizza
        $detallePizza = VisitaDetalleModel::where('producto_id', $this->prod2->id)->first();

        // 4. Mover pizza completa a Subcuenta 2
        $resMover = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/mover-item", [
                'detalle_id' => $detallePizza->id,
                'subcuenta_destino_id' => $subcuenta2Id,
            ]);
        $resMover->assertStatus(200);

        // 5. Verificar subcuentas
        $resGet = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        $subcuentas = $resGet->json('data.subcuentas');
        $this->assertCount(2, $subcuentas);
        $this->assertEquals(80, $subcuentas[0]['total']);
        $this->assertEquals(70, $subcuentas[1]['total']);
    }

    public function test_puede_mover_cantidad_fraccionada_dividiendo_el_item(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        // 1. Crear subcuenta 2
        $resSub = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/crear", [
                'nombre_comensal' => 'Persona B',
            ]);
        $subcuenta2Id = $resSub->json('data.id');

        // Mover 1 de las 2 hamburguesas
        $detalleHam = VisitaDetalleModel::where('producto_id', $this->prod1->id)->first();

        $resMover = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/mover-item", [
                'detalle_id' => $detalleHam->id,
                'subcuenta_destino_id' => $subcuenta2Id,
                'cantidad' => 1,
            ]);
        $resMover->assertStatus(200);

        $resGet = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        $subcuentas = $resGet->json('data.subcuentas');
        $this->assertEquals(110, $subcuentas[0]['total']);
        $this->assertEquals(40, $subcuentas[1]['total']);
    }

    public function test_puede_dividir_en_partes_iguales_split_nx(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/dividir-iguales", [
                'personas' => 3,
            ]);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(50.00, $response->json('data.0.total'));
        $this->assertEquals(50.00, $response->json('data.1.total'));
        $this->assertEquals(50.00, $response->json('data.2.total'));
    }

    public function test_juntar_cuentas_restaura_todo_a_cuenta_principal(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        // 1. Crear subcuenta y mover pizza
        $resSub = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/crear", [
                'nombre_comensal' => 'Amigo',
            ]);
        $subId = $resSub->json('data.id');
        $detallePizza = VisitaDetalleModel::where('producto_id', $this->prod2->id)->first();
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/mover-item", [
                'detalle_id' => $detallePizza->id,
                'subcuenta_destino_id' => $subId,
            ]);

        // 2. Ejecutar juntar cuentas
        $resJuntar = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/juntar");
        $resJuntar->assertStatus(200);

        // 3. Verificar que solo queda Cuenta Principal con el total completo (150 Bs)
        $resGet = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");
        $this->assertCount(1, $resGet->json('data.subcuentas'));
        $this->assertEquals(150.00, $resGet->json('data.subcuentas.0.total'));
    }

    public function test_puede_registrar_pago_parcial_y_reducir_saldo(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/pago-parcial", [
                'monto' => 60.00,
                'metodo_pago' => 'EFECTIVO',
                'notas' => 'Abono anticipado comensal',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pagos_parciales_visita', [
            'visita_id' => $this->visita->id,
            'monto' => 60.00,
            'metodo_pago' => 'EFECTIVO',
        ]);

        $resGet = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");
        $this->assertEquals(90.00, $resGet->json('data.saldo_pendiente'));
    }

    public function test_puede_cobrar_subcuenta_individual_con_propina_y_libera_mesa_al_completar(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas");

        // 1. Dividir en 2 cuentas
        $resSub = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/crear", [
                'nombre_comensal' => 'Cuenta 2',
            ]);
        $sub2Id = $resSub->json('data.id');

        $detallePizza = VisitaDetalleModel::where('producto_id', $this->prod2->id)->first();
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa->id}/separar-cuentas/mover-item", [
                'detalle_id' => $detallePizza->id,
                'subcuenta_destino_id' => $sub2Id,
            ]);

        $subcuentas = SubcuentaVisitaModel::where('visita_id', $this->visita->id)->get();
        $sub1Id = $subcuentas[0]->id;

        // 2. Cobrar Subcuenta 1 (80 Bs) con 10 Bs de propina
        $resCobro1 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/subcuentas/{$sub1Id}/cobrar", [
                'tipo_comprobante' => 'FACTURA',
                'metodo_pago' => 'EFECTIVO',
                'monto_recibido' => 100,
                'propina_monto' => 10.00,
                'propina_porcentaje' => 12.5,
                'numero_documento' => '1234567',
                'razon_social' => 'Cliente Subcuenta 1',
            ]);

        $resCobro1->assertStatus(200);
        $this->assertFalse($resCobro1->json('data.mesa_liberada'));

        $this->assertDatabaseHas('propinas', [
            'subcuenta_id' => $sub1Id,
            'monto_propina' => 10.00,
            'mesero_id' => $this->visita->mesero_id,
        ]);

        // 3. Cobrar Subcuenta 2 (70 Bs) con Recibo
        $resCobro2 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/subcuentas/{$sub2Id}/cobrar", [
                'tipo_comprobante' => 'RECIBO',
                'metodo_pago' => 'QR',
            ]);

        $resCobro2->assertStatus(200);
        $this->assertTrue($resCobro2->json('data.mesa_liberada'));

        $this->assertNull($this->mesa->fresh()->visitaActiva);
        $this->assertEquals('COBRADA', $this->visita->fresh()->estado);
    }
}
