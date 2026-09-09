<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalonMesaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_list_salones_with_real_metrics(): void
    {
        $response = $this->getJson('/api/v1/salones');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'codigo', 'nombre', 'total_mesas', 'mesas_ocupadas', 'porcentaje_ocupacion',
                    ],
                ],
            ]);
    }

    public function test_can_list_mesas_of_a_salon(): void
    {
        $response = $this->getJson('/api/v1/salones/1/mesas');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'salon_id', 'codigo', 'nombre', 'capacidad', 'estado', 'estado_color', 'total_consumo',
                    ],
                ],
            ]);
    }

    public function test_can_open_table_and_change_status_to_ocupada(): void
    {
        $mesero = \App\Infrastructure\Persistence\Eloquent\Models\UserModel::where('role', 'mesero')->first();

        // Mesa 2 estÃƒÂ¡ libre
        $response = $this->postJson('/api/v1/mesas/2/abrir', [
            'mesero_id' => $mesero->id,
            'personas' => 3,
            'cliente_nombre' => 'Juan PÃƒÂ©rez',
            'notas' => 'Cliente VIP',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Mesa abierta exitosamente',
            ]);

        // Verificar que la mesa 2 ahora estÃƒÂ¡ OCUPADA
        $mesaResponse = $this->getJson('/api/v1/mesas/2');
        $mesaResponse->assertStatus(200)
            ->assertJsonPath('data.estado', 'ABIERTA');
    }

    public function test_can_request_precuenta_for_occupied_table(): void
    {
        // Mesa 1 estÃƒÂ¡ ocupada
        $response = $this->postJson('/api/v1/mesas/1/precuenta');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pre-cuenta solicitada e impresa',
            ]);

        $mesaResponse = $this->getJson('/api/v1/mesas/1');
        $mesaResponse->assertStatus(200)
            ->assertJsonPath('data.estado', 'PRECUENTA');
    }

    public function test_can_free_table(): void
    {
        // Mesa 1 estÃƒÂ¡ ocupada -> liberar
        $response = $this->postJson('/api/v1/mesas/1/liberar');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Mesa liberada correctamente',
            ]);

        $mesaResponse = $this->getJson('/api/v1/mesas/1');
        $mesaResponse->assertStatus(200)
            ->assertJsonPath('data.estado', 'LIBRE');
    }
}