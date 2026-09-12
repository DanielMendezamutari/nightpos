<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaOperacionHistorialModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MesaOperacionesTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;
    private string $token;
    private MesaModel $mesa1;
    private MesaModel $mesa2;
    private ProductoModel $producto1;
    private ProductoModel $producto2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = UserModel::first();

        // Autenticar via PIN
        $login = $this->postJson('/api/v1/auth/login-pin', [
            'pin' => '1234',
        ]);
        $this->token = $login->json('data.access_token') ?? '';

        $mesas = MesaModel::take(2)->get();
        $this->mesa1 = $mesas[0];
        $this->mesa2 = $mesas[1];

        // Limpiar cualquier visita previa en estas dos mesas para aislamiento limpio del test
        VisitaModel::whereIn('mesa_id', [$this->mesa1->id, $this->mesa2->id])->delete();

        $productos = ProductoModel::take(2)->get();
        $this->producto1 = $productos[0];
        $this->producto2 = $productos[1];
    }

    public function test_cambiar_mesa_traslada_visita_y_libera_origen(): void
    {
        $visita = VisitaModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'mesa_id' => $this->mesa1->id,
            'mesero_id' => $this->user->id,
            'personas' => 2,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 45.00,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $this->producto1->id,
            'producto_nombre' => $this->producto1->nombre,
            'cantidad' => 1,
            'precio_unitario' => 45.00,
            'subtotal' => 45.00,
            'estado' => 'CONFIRMADO',
        ]);

        // Cambiar a Mesa 2 que está libre
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa1->id}/cambiar-mesa", [
                'mesa_destino_id' => $this->mesa2->id,
                'motivo' => 'Cliente prefiere terraza',
            ]);

        if ($response->status() !== 200) { fwrite(STDERR, json_encode($response->json()) . "\n"); }
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'ticket_auditoria',
            'mesa_origen',
            'mesa_destino',
            'visita',
        ]);

        $this->assertStringContainsString('CAMBIO DE MESA', $response->json('ticket_auditoria'));

        // Verificar que la visita ahora pertenece a Mesa 2
        $this->assertEquals($this->mesa2->id, $visita->fresh()->mesa_id);

        // Verificar historial
        $this->assertDatabaseHas('mesa_operaciones_historial', [
            'tipo_operacion' => 'CAMBIO_MESA',
            'mesa_origen_id' => $this->mesa1->id,
            'mesa_destino_id' => $this->mesa2->id,
        ]);
    }

    public function test_cambiar_mesa_rechaza_si_destino_esta_ocupada(): void
    {
        // Mesa 1 ocupada
        VisitaModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'mesa_id' => $this->mesa1->id,
            'mesero_id' => $this->user->id,
            'personas' => 2,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 45.00,
        ]);

        // Mesa 2 también ocupada
        VisitaModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'mesa_id' => $this->mesa2->id,
            'mesero_id' => $this->user->id,
            'personas' => 3,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 50.00,
        ]);

        // Intentar cambiar Mesa 1 a Mesa 2
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa1->id}/cambiar-mesa", [
                'mesa_destino_id' => $this->mesa2->id,
            ]);

        $response->assertStatus(409);
        $this->assertTrue($response->json('es_ocupada'));
    }

    public function test_juntar_mesas_fusiona_detalles_y_libera_origen(): void
    {
        // Mesa 1 con producto 1 (45 Bs)
        $visita1 = VisitaModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'mesa_id' => $this->mesa1->id,
            'mesero_id' => $this->user->id,
            'personas' => 2,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 45.00,
        ]);
        VisitaDetalleModel::create([
            'visita_id' => $visita1->id,
            'producto_id' => $this->producto1->id,
            'producto_nombre' => $this->producto1->nombre,
            'cantidad' => 1,
            'precio_unitario' => 45.00,
            'subtotal' => 45.00,
            'estado' => 'CONFIRMADO',
        ]);

        // Mesa 2 con producto 2 (25 Bs)
        $visita2 = VisitaModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'mesa_id' => $this->mesa2->id,
            'mesero_id' => $this->user->id,
            'personas' => 2,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 25.00,
        ]);
        VisitaDetalleModel::create([
            'visita_id' => $visita2->id,
            'producto_id' => $this->producto2->id,
            'producto_nombre' => $this->producto2->nombre,
            'cantidad' => 1,
            'precio_unitario' => 25.00,
            'subtotal' => 25.00,
            'estado' => 'CONFIRMADO',
        ]);

        // Juntar Mesa 1 en Mesa 2
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa1->id}/juntar-mesa", [
                'mesa_destino_id' => $this->mesa2->id,
                'motivo' => 'Amigos se unen en la misma mesa',
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('JUNTANDO MESAS', $response->json('ticket_auditoria'));

        // Visita 1 debe estar ANULADA (Mesa 1 queda libre)
        $this->assertEquals('ANULADA', $visita1->fresh()->estado);

        // Visita 2 debe tener el total consolidado (45 + 25 = 70 Bs) y 2 detalles
        $visita2Fresh = $visita2->fresh();
        $this->assertEquals(70.00, (float) $visita2Fresh->total);
        $this->assertEquals(2, $visita2Fresh->detalles()->count());

        // Verificar historial
        $this->assertDatabaseHas('mesa_operaciones_historial', [
            'tipo_operacion' => 'JUNTAR_MESA',
            'mesa_origen_id' => $this->mesa1->id,
            'mesa_destino_id' => $this->mesa2->id,
        ]);
    }

    public function test_crear_pedido_sin_mesa_y_listar_activos(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/pedidos-sin-mesa', [
                'tipo_despacho' => 'LLEVAR',
                'cliente_nombre' => 'Gonzalo Mendez',
                'telefono_cliente' => '76543210',
                'direccion_envio' => 'Av. Ballivian #123',
                'notas' => 'Sin cebolla, bien cocido',
            ]);

        $response->assertStatus(201);
        $visitaId = $response->json('visita.id');
        $this->assertNull($response->json('visita.mesa_id'));
        $this->assertEquals('LLEVAR', $response->json('visita.tipo_despacho'));

        // Listar pedidos sin mesa
        $listResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/pedidos-sin-mesa');
        $listResponse->assertStatus(200);
        $this->assertTrue(collect($listResponse->json())->contains('id', $visitaId));
    }

    public function test_asignar_pedido_sin_mesa_a_mesa_fisica(): void
    {
        // Crear pedido sin mesa
        $crearResp = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/pedidos-sin-mesa', [
                'tipo_despacho' => 'BARRA',
                'cliente_nombre' => 'Ana Morales',
            ]);
        $visitaId = $crearResp->json('visita.id');

        // Asignar a Mesa 1 (que está libre)
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/pedidos-sin-mesa/{$visitaId}/asignar-mesa", [
                'mesa_destino_id' => $this->mesa1->id,
            ]);

        $response->assertStatus(200);
        $visita = VisitaModel::find($visitaId);
        $this->assertEquals($this->mesa1->id, $visita->mesa_id);
        $this->assertEquals('MESA', $visita->tipo_despacho);

        $this->assertDatabaseHas('mesa_operaciones_historial', [
            'tipo_operacion' => 'ASIGNAR_MESA',
            'mesa_destino_id' => $this->mesa1->id,
        ]);
    }

    public function test_reasignar_mesero_actualiza_visita(): void
    {
        // Crear mesero alternativo
        $mesero2 = UserModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'name' => 'Mesero Pedro',
            'username' => 'pedro',
            'email' => 'pedro@test.com',
            'password' => bcrypt('password'),
            'role' => 'MESERO',
            'pin' => '9999',
            'activo' => true,
        ]);

        $visita = VisitaModel::create([
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
            'mesa_id' => $this->mesa1->id,
            'mesero_id' => $this->user->id,
            'personas' => 2,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 0.00,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/{$this->mesa1->id}/reasignar-mesero", [
                'mesero_id' => $mesero2->id,
                'motivo' => 'Cambio de turno de personal',
            ]);

        $response->assertStatus(200);
        $this->assertEquals($mesero2->id, $visita->fresh()->mesero_id);
    }

    public function test_comanda_y_cobro_directo_sin_mesa(): void
    {
        // 1. Crear pedido sin mesa (Para Llevar)
        $respCrear = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/pedidos-sin-mesa', [
                'tipo_despacho' => 'LLEVAR',
                'cliente_nombre' => 'Enrique Cliente',
                'telefono_cliente' => '78901234',
            ]);

        $respCrear->assertStatus(201);
        $visitaId = $respCrear->json('visita.id');

        // 2. Enviar comanda con visitaId en URL
        $respComanda1 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/visitas/{$visitaId}/comanda", [
                'items' => [
                    [
                        'producto_id' => $this->producto1->id,
                        'cantidad' => 2,
                        'observaciones' => 'Sin mayonesa',
                    ],
                ],
            ]);

        $respComanda1->assertStatus(200);
        $this->assertTrue($respComanda1->json('success'));

        // 3. Enviar comanda adicional con sin_mesa_{id} en URL
        $respComanda2 = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/mesas/sin_mesa_{$visitaId}/comanda", [
                'items' => [
                    [
                        'producto_id' => $this->producto2->id,
                        'cantidad' => 1,
                    ],
                ],
            ]);

        $respComanda2->assertStatus(200);
        $this->assertTrue($respComanda2->json('success'));

        $expectedTotal = (2 * (float)$this->producto1->precio) + (1 * (float)$this->producto2->precio);
        $visita = VisitaModel::find($visitaId);
        $this->assertEquals($expectedTotal, (float)$visita->total);
        $this->assertEquals(2, $visita->detalles()->count());

        // 4. Cobrar y facturar el pedido sin mesa
        // Abrir turno primero si no hay uno
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/caja/abrir-turno', [
                'monto_inicial_bs' => 100.00,
            ]);

        $respCobro = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/visitas/{$visitaId}/cobrar-facturar", [
                'tipo_comprobante' => 'RECIBO',
                'metodo_pago' => 'EFECTIVO',
                'monto_recibido' => $expectedTotal + 10.00,
            ]);

        $respCobro->assertStatus(200);
        $this->assertTrue($respCobro->json('success'));

        // Visita debe quedar en estado COBRADA
        $this->assertEquals('COBRADA', $visita->fresh()->estado);
    }
}
