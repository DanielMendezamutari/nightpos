<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoriaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KdsApiTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $mesero;
    private MesaModel $mesa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->mesero = UserModel::where('role', 'mesero')->first() ?? UserModel::first();
        $this->mesa = MesaModel::first();
    }

    public function test_puede_obtener_tickets_kds_con_semaforo_de_tiempo(): void
    {
        $visita = VisitaModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'branch_id' => $this->mesero->branch_id,
            'mesa_id' => $this->mesa->id,
            'mesero_id' => $this->mesero->id,
            'personas' => 4,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now()->subMinutes(12),
            'total' => 85.00,
        ]);

        $categoria = CategoriaModel::first();

        $producto = ProductoModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'categoria_id' => $categoria->id,
            'codigo' => 'P-KDS-01',
            'nombre' => 'Bife de Chorizo Premium',
            'precio' => 85.00,
            'costo' => 40.00,
            'destino_impresion' => 'COCINA',
            'estacion_cocina' => 'COCINA',
            'activo' => true,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'cantidad' => 2,
            'precio_unitario' => 85.00,
            'subtotal' => 170.00,
            'observaciones' => 'Término medio, sin sal',
            'estacion_cocina' => 'COCINA',
            'estado' => 'EN_PREPARACION',
            'iniciado_at' => now()->subMinutes(12),
            'terminado_at' => null,
        ]);

        $response = $this->getJson('/api/v1/kds/tickets');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        
        $ticket = collect($data)->firstWhere('visita_id', $visita->id);
        $this->assertNotNull($ticket);
        $this->assertEquals('ALERTA', $ticket['categoria_urgencia']); // 12 mins >= 10 mins
        $this->assertGreaterThanOrEqual(12, $ticket['minutos_transcurridos']);
        $this->assertEquals('Término medio, sin sal', $ticket['items'][0]['observaciones']);
    }

    public function test_puede_filtrar_tickets_por_estacion_cocina_y_barra(): void
    {
        $visita = VisitaModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'branch_id' => $this->mesero->branch_id,
            'mesa_id' => $this->mesa->id,
            'mesero_id' => $this->mesero->id,
            'personas' => 2,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now()->subMinutes(5),
            'total' => 50.00,
        ]);

        $cat = CategoriaModel::first();

        $plato = ProductoModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'categoria_id' => $cat->id,
            'codigo' => 'PL-KDS',
            'nombre' => 'Hamburguesa Clásica',
            'precio' => 35.00,
            'estacion_cocina' => 'COCINA',
            'activo' => true,
        ]);

        $trago = ProductoModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'categoria_id' => $cat->id,
            'codigo' => 'TR-KDS',
            'nombre' => 'Mojito Cubano',
            'precio' => 25.00,
            'estacion_cocina' => 'BARRA',
            'activo' => true,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $plato->id,
            'producto_nombre' => $plato->nombre,
            'cantidad' => 1,
            'precio_unitario' => 35.00,
            'subtotal' => 35.00,
            'estacion_cocina' => 'COCINA',
            'estado' => 'EN_PREPARACION',
            'iniciado_at' => now(),
            'terminado_at' => null,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_id' => $trago->id,
            'producto_nombre' => $trago->nombre,
            'cantidad' => 2,
            'precio_unitario' => 25.00,
            'subtotal' => 50.00,
            'estacion_cocina' => 'BARRA',
            'estado' => 'EN_PREPARACION',
            'iniciado_at' => now(),
            'terminado_at' => null,
        ]);

        // Filtrar solo BARRA
        $resBarra = $this->getJson('/api/v1/kds/tickets?estacion=BARRA');
        $resBarra->assertStatus(200);
        $ticketBarra = collect($resBarra->json('data'))->firstWhere('visita_id', $visita->id);
        $this->assertNotNull($ticketBarra);
        $this->assertCount(1, $ticketBarra['items']);
        $this->assertEquals('Mojito Cubano', $ticketBarra['items'][0]['producto_nombre']);

        // Filtrar solo COCINA
        $resCocina = $this->getJson('/api/v1/kds/tickets?estacion=COCINA');
        $resCocina->assertStatus(200);
        $ticketCocina = collect($resCocina->json('data'))->firstWhere('visita_id', $visita->id);
        $this->assertNotNull($ticketCocina);
        $this->assertCount(1, $ticketCocina['items']);
        $this->assertEquals('Hamburguesa Clásica', $ticketCocina['items'][0]['producto_nombre']);
    }

    public function test_puede_marcar_item_individual_como_listo(): void
    {
        $visita = VisitaModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'branch_id' => $this->mesero->branch_id,
            'mesa_id' => $this->mesa->id,
            'mesero_id' => $this->mesero->id,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 40.00,
        ]);

        $item = VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_nombre' => 'Pizza Napolitana',
            'cantidad' => 1,
            'precio_unitario' => 40.00,
            'subtotal' => 40.00,
            'estacion_cocina' => 'COCINA',
            'estado' => 'EN_PREPARACION',
            'iniciado_at' => now(),
            'terminado_at' => null,
        ]);

        $response = $this->patchJson("/api/v1/kds/items/{$item->id}/estado", [
            'estado' => 'LISTO',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'estado' => 'LISTO',
                    'es_terminado' => true,
                ],
            ]);

        $this->assertNotNull($response->json('data.terminado_at'));
        $this->assertDatabaseHas('visita_detalles', [
            'id' => $item->id,
            'estado' => 'LISTO',
        ]);
    }

    public function test_despacho_masivo_ticket_y_salida_del_monitor(): void
    {
        $visita = VisitaModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'branch_id' => $this->mesero->branch_id,
            'mesa_id' => $this->mesa->id,
            'mesero_id' => $this->mesero->id,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now(),
            'total' => 70.00,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_nombre' => 'Plato 1',
            'cantidad' => 1,
            'precio_unitario' => 35.00,
            'subtotal' => 35.00,
            'estacion_cocina' => 'COCINA',
            'estado' => 'EN_PREPARACION',
            'iniciado_at' => now(),
            'terminado_at' => null,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_nombre' => 'Plato 2',
            'cantidad' => 1,
            'precio_unitario' => 35.00,
            'subtotal' => 35.00,
            'estacion_cocina' => 'COCINA',
            'estado' => 'EN_PREPARACION',
            'iniciado_at' => now(),
            'terminado_at' => null,
        ]);

        // Despachar todo
        $resDespacho = $this->postJson("/api/v1/kds/tickets/{$visita->id}/despachar");
        $resDespacho->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'items_despachados' => 2,
                ],
            ]);

        // Al consultar tickets activos, no debe aparecer esta visita porque Terminado is not null
        $resTickets = $this->getJson('/api/v1/kds/tickets');
        $resTickets->assertStatus(200);
        $activos = collect($resTickets->json('data'))->where('visita_id', $visita->id);
        $this->assertEmpty($activos);
    }

    public function test_historial_despachos_y_revertir_despacho(): void
    {
        $visita = VisitaModel::create([
            'tenant_id' => $this->mesero->tenant_id,
            'branch_id' => $this->mesero->branch_id,
            'mesa_id' => $this->mesa->id,
            'mesero_id' => $this->mesero->id,
            'estado' => 'ABIERTA',
            'fecha_apertura' => now()->subMinutes(15),
            'total' => 45.00,
        ]);

        VisitaDetalleModel::create([
            'visita_id' => $visita->id,
            'producto_nombre' => 'Lasaña Bolognesa',
            'cantidad' => 1,
            'precio_unitario' => 45.00,
            'subtotal' => 45.00,
            'estacion_cocina' => 'COCINA',
            'estado' => 'LISTO',
            'iniciado_at' => now()->subMinutes(15),
            'terminado_at' => now(),
        ]);

        // Historial
        $resHistorial = $this->getJson('/api/v1/kds/historial');
        $resHistorial->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $historial = collect($resHistorial->json('data'))->firstWhere('visita_id', $visita->id);
        $this->assertNotNull($historial);
        $this->assertEquals(15, $historial['tiempo_entrega_min']);

        // Revertir despacho
        $resRevertir = $this->postJson("/api/v1/kds/tickets/{$visita->id}/revertir");
        $resRevertir->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'items_restaurados' => 1,
                ],
            ]);

        // Ahora debe volver al monitor activo
        $resActivos = $this->getJson('/api/v1/kds/tickets');
        $resActivos->assertStatus(200);
        $activo = collect($resActivos->json('data'))->firstWhere('visita_id', $visita->id);
        $this->assertNotNull($activo);
    }
}
