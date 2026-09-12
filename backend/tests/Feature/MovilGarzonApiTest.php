<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoriaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\SalonModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class MovilGarzonApiTest extends TestCase
{
    use RefreshDatabase;

    private TenantModel $tenant;
    private BranchModel $branch;
    private UserModel $garzon1;
    private UserModel $garzon2;
    private UserModel $admin;
    private SalonModel $salon;
    private MesaModel $mesa1;
    private MesaModel $mesa2;
    private ProductoModel $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantModel::create([
            'slug' => 'casa-demo',
            'name' => 'Casa Demo',
            'is_active' => true,
        ]);

        $this->branch = BranchModel::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CENTRO',
            'name' => 'Central',
            'address' => 'Calle Murillo',
            'is_active' => true,
        ]);

        $this->garzon1 = UserModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Garzon Juan',
            'username' => 'juan',
            'email' => 'juan@test.com',
            'pin_hash' => Hash::make('5678'),
            'role' => 'mesero',
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $this->garzon2 = UserModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Garzon Pedro',
            'username' => 'pedro',
            'email' => 'pedro@test.com',
            'pin_hash' => Hash::make('9999'),
            'role' => 'mesero',
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $this->admin = UserModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Admin Boss',
            'username' => 'boss',
            'email' => 'boss@test.com',
            'pin_hash' => Hash::make('2468'),
            'role' => 'admin',
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $this->salon = SalonModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'codigo' => 'SAL-01',
            'nombre' => 'Salon Principal',
            'orden' => 1,
            'activo' => true,
        ]);

        $this->mesa1 = MesaModel::create([
            'salon_id' => $this->salon->id,
            'codigo' => '1',
            'nombre' => 'Mesa 1',
            'capacidad' => 4,
            'activo' => true,
        ]);

        $this->mesa2 = MesaModel::create([
            'salon_id' => $this->salon->id,
            'codigo' => '2',
            'nombre' => 'Mesa 2',
            'capacidad' => 2,
            'activo' => true,
        ]);

        $categoria = CategoriaModel::create([
            'tenant_id' => $this->tenant->id,
            'codigo' => 'CAT-01',
            'nombre' => 'Platos Fuertes',
            'activo' => true,
        ]);

        $this->producto = ProductoModel::create([
            'tenant_id' => $this->tenant->id,
            'categoria_id' => $categoria->id,
            'codigo' => 'PLATO-01',
            'nombre' => 'Pique Macho Familiar',
            'precio' => 95.00,
            'activo' => true,
        ]);
    }

    public function test_login_movil_con_pin_exitoso(): void
    {
        $response = $this->postJson('/api/v1/movil/login', [
            'pin' => '5678',
            'tenant_slug' => 'casa-demo',
            'branch_code' => 'CENTRO',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'tiene_mesas_libres' => true,
                    'user' => [
                        'username' => 'juan',
                        'role' => 'mesero',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_garzon_solo_puede_operar_sus_mesas_ocupadas(): void
    {
        VisitaModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'mesa_id' => $this->mesa1->id,
            'mesero_id' => $this->garzon1->id,
            'cliente_nombre' => 'Cliente Mesa 1',
            'personas' => 4,
            'estado' => 'ABIERTA',
            'total' => 95.00,
            'fecha_apertura' => now(),
        ]);

        VisitaModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'mesa_id' => $this->mesa2->id,
            'mesero_id' => $this->garzon2->id,
            'cliente_nombre' => 'Cliente Mesa 2',
            'personas' => 2,
            'estado' => 'ABIERTA',
            'total' => 50.00,
            'fecha_apertura' => now(),
        ]);

        $tokenG1 = JWTAuth::fromUser($this->garzon1);
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenG1)
            ->getJson('/api/v1/movil/mesas?salon_id=' . $this->salon->id);

        $response->assertStatus(200);
        $mesas = collect($response->json('data'));
        
        $m1 = $mesas->firstWhere('id', $this->mesa1->id);
        $m2 = $mesas->firstWhere('id', $this->mesa2->id);

        $this->assertTrue($m1['es_mi_mesa']);
        $this->assertFalse($m2['es_mi_mesa']);
    }

    public function test_admin_puede_operar_todas_las_mesas(): void
    {
        VisitaModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'mesa_id' => $this->mesa1->id,
            'mesero_id' => $this->garzon1->id,
            'cliente_nombre' => 'Cliente Mesa 1',
            'personas' => 4,
            'estado' => 'ABIERTA',
            'total' => 95.00,
            'fecha_apertura' => now(),
        ]);

        VisitaModel::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'mesa_id' => $this->mesa2->id,
            'mesero_id' => $this->garzon2->id,
            'cliente_nombre' => 'Cliente Mesa 2',
            'personas' => 2,
            'estado' => 'ABIERTA',
            'total' => 50.00,
            'fecha_apertura' => now(),
        ]);

        $tokenAdmin = JWTAuth::fromUser($this->admin);
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenAdmin)
            ->getJson('/api/v1/movil/mesas?salon_id=' . $this->salon->id);

        $response->assertStatus(200);
        $mesas = collect($response->json('data'));
        $this->assertTrue($mesas->firstWhere('id', $this->mesa1->id)['es_mi_mesa']);
        $this->assertTrue($mesas->firstWhere('id', $this->mesa2->id)['es_mi_mesa']);
    }

    public function test_abrir_mesa_y_enviar_comanda_desde_movil(): void
    {
        $token = JWTAuth::fromUser($this->garzon1);

        // 1. Abrir mesa desde el movil
        $resAbrir = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/movil/mesas/{$this->mesa1->id}/abrir", [
                'personas' => 3,
                'cliente_nombre' => 'Familia Lopez',
                'notas' => 'Mesa ventana',
            ]);

        $resAbrir->assertStatus(200)
            ->assertJson(['success' => true]);

        $visitaId = $resAbrir->json('data.visita_id');
        $this->assertNotNull($visitaId);

        // 2. Enviar comanda desde el movil
        $resComanda = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/movil/comanda', [
                'mesa_id' => $this->mesa1->id,
                'visita_id' => $visitaId,
                'items' => [
                    [
                        'producto_id' => $this->producto->id,
                        'cantidad' => 2,
                        'observaciones' => 'Sin locoto bien cocido',
                    ],
                ],
            ]);

        $resComanda->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Ver estado de la cuenta desde el movil (VerOrder)
        $resCuenta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/movil/cuenta/{$this->mesa1->id}");
        $resCuenta->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'mesa_nombre' => 'Mesa 1',
                    'total' => 190.00,
                ],
            ]);

        // 4. Solicitar pre-cuenta desde el movil (PrintOrder)
        $resPrecuenta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/movil/imprimir-precuenta/{$this->mesa1->id}");
        $resPrecuenta->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('visitas', [
            'id' => $visitaId,
            'estado' => 'PRECUENTA',
            'imprimio_cuenta' => true,
        ]);
    }
}