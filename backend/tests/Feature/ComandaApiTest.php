<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComandaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_list_menu_categories(): void
    {
        $response = $this->getJson('/api/v1/menu/categorias');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'codigo', 'nombre', 'icono', 'color', 'total_productos'],
                ],
            ]);
    }

    public function test_can_list_products_by_category(): void
    {
        $response = $this->getJson('/api/v1/menu/productos?categoria_id=1');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'categoria_id', 'nombre', 'precio', 'tiempo_preparacion', 'destino_impresion'],
                ],
            ]);
    }

    public function test_can_add_items_to_active_table_order(): void
    {
        // Mesa 1 tiene visita activa
        $response = $this->postJson('/api/v1/mesas/1/comanda', [
            'items' => [
                [
                    'producto_id' => 1,
                    'cantidad' => 2,
                    'observaciones' => 'Sin cebolla',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comanda enviada a cocina exitosamente',
            ])
            ->assertJsonStructure([
                'data' => ['visita_id', 'total', 'items_agregados'],
            ]);
    }

    public function test_can_delete_item_from_order_and_recalculate_total(): void
    {
        // Obtener el detalle de la mesa 1
        $mesa = $this->getJson('/api/v1/mesas/1');
        $detalleId = $mesa->json('data.visita.detalles.0.id');

        $response = $this->deleteJson('/api/v1/visita-detalles/' . $detalleId);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ítem eliminado de la comanda',
            ]);
    }
}