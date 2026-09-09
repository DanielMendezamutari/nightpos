<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\SalonModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Database\Seeder;

class SalonesMesasSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = TenantModel::first();
        $branch = BranchModel::first();
        $mesero = UserModel::where('role', 'mesero')->first() ?? UserModel::first();

        if (!$tenant || !$branch || !$mesero) {
            return;
        }

        // 1. Salón Principal
        $salonPrincipal = SalonModel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'codigo' => 'SAL-01'],
            [
                'nombre' => 'Salón Principal',
                'impresora_cuenta' => 'Termica-Salon-1',
                'impresora_factura' => 'Termica-Caja-Central',
                'orden' => 1,
                'activo' => true,
            ]
        );

        // 2. Terraza & Jardín
        $salonTerraza = SalonModel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'codigo' => 'TER-01'],
            [
                'nombre' => 'Terraza & Jardín',
                'impresora_cuenta' => 'Termica-Terraza',
                'impresora_factura' => 'Termica-Caja-Central',
                'orden' => 2,
                'activo' => true,
            ]
        );

        // 3. Área VIP / Bar
        $salonVip = SalonModel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'codigo' => 'VIP-01'],
            [
                'nombre' => 'Área VIP / Bar',
                'impresora_cuenta' => 'Termica-Bar',
                'impresora_factura' => 'Termica-Caja-Central',
                'orden' => 3,
                'activo' => true,
            ]
        );

        // 4. Barra & Delivery
        $salonBarra = SalonModel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'codigo' => 'BAR-01'],
            [
                'nombre' => 'Barra & Para Llevar',
                'impresora_cuenta' => 'Termica-Barra',
                'impresora_factura' => 'Termica-Caja-Central',
                'orden' => 4,
                'activo' => true,
            ]
        );

        // Mesas Salón Principal (12 mesas)
        for ($i = 1; $i <= 12; $i++) {
            $codigo = (string) $i;
            $mesa = MesaModel::updateOrCreate(
                ['salon_id' => $salonPrincipal->id, 'codigo' => $codigo],
                [
                    'nombre' => "Mesa {$codigo}",
                    'capacidad' => ($i % 4 == 0) ? 6 : (($i % 3 == 0) ? 2 : 4),
                    'posicion_x' => (($i - 1) % 4) * 140 + 20,
                    'posicion_y' => intdiv($i - 1, 4) * 140 + 20,
                    'ancho' => 110,
                    'alto' => 110,
                    'forma' => ($i % 2 == 0) ? 'cuadrada' : 'redonda',
                    'activo' => true,
                ]
            );

            // M-01 Ocupada con consumo
            if ($i === 1) {
                $this->crearVisita($tenant->id, $branch->id, $mesa->id, $mesero->id, 'ABIERTA', 4, [
                    ['producto' => 'Pique Macho Especial', 'cant' => 1, 'precio' => 95.00],
                    ['producto' => 'Jarra de Limonada 1.5L', 'cant' => 1, 'precio' => 30.00],
                    ['producto' => 'Cerveza Paceña Huari 620ml', 'cant' => 2, 'precio' => 30.00],
                ]);
            }

            // M-03 Pre-cuenta pedida
            if ($i === 3) {
                $this->crearVisita($tenant->id, $branch->id, $mesa->id, $mesero->id, 'PRECUENTA', 2, [
                    ['producto' => 'Bife de Chorizo a la Parrilla', 'cant' => 2, 'precio' => 120.00],
                    ['producto' => 'Vino Tinto Campos de Solana', 'cant' => 1, 'precio' => 100.00],
                ]);
            }

            // M-05 Ocupada
            if ($i === 5) {
                $this->crearVisita($tenant->id, $branch->id, $mesa->id, $mesero->id, 'ABIERTA', 3, [
                    ['producto' => 'Silpancho Cochabambino', 'cant' => 2, 'precio' => 70.00],
                    ['producto' => 'Gaseosa Coca Cola 2L', 'cant' => 1, 'precio' => 25.00],
                ]);
            }
        }

        // Mesas Terraza (8 mesas)
        for ($i = 1; $i <= 8; $i++) {
            $codigo = "T-{$i}";
            $mesa = MesaModel::updateOrCreate(
                ['salon_id' => $salonTerraza->id, 'codigo' => $codigo],
                [
                    'nombre' => "Terraza {$i}",
                    'capacidad' => 4,
                    'posicion_x' => (($i - 1) % 4) * 140 + 20,
                    'posicion_y' => intdiv($i - 1, 4) * 140 + 20,
                    'ancho' => 110,
                    'alto' => 110,
                    'forma' => 'cuadrada',
                    'activo' => true,
                ]
            );

            if ($i === 1) {
                $this->crearVisita($tenant->id, $branch->id, $mesa->id, $mesero->id, 'ABIERTA', 2, [
                    ['producto' => 'Hamburguesa Ribersoft Monster', 'cant' => 2, 'precio' => 90.00],
                    ['producto' => 'Mojito Clásico', 'cant' => 2, 'precio' => 50.00],
                ]);
            }
        }

        // Mesas VIP (4 mesas)
        for ($i = 1; $i <= 4; $i++) {
            $codigo = "VIP-{$i}";
            $mesa = MesaModel::updateOrCreate(
                ['salon_id' => $salonVip->id, 'codigo' => $codigo],
                [
                    'nombre' => "Box VIP {$i}",
                    'capacidad' => 8,
                    'posicion_x' => ($i - 1) * 160 + 20,
                    'posicion_y' => 40,
                    'ancho' => 140,
                    'alto' => 120,
                    'forma' => 'rectangular',
                    'activo' => true,
                ]
            );

            if ($i === 1) {
                $this->crearVisita($tenant->id, $branch->id, $mesa->id, $mesero->id, 'ABIERTA', 6, [
                    ['producto' => 'Parrillada Ribersoft Familiar', 'cant' => 1, 'precio' => 420.00],
                    ['producto' => 'Whisky Johnnie Walker Black', 'cant' => 1, 'precio' => 380.00],
                ]);
            }
        }

        // Barra (4 puestos)
        for ($i = 1; $i <= 4; $i++) {
            $codigo = "B-{$i}";
            MesaModel::updateOrCreate(
                ['salon_id' => $salonBarra->id, 'codigo' => $codigo],
                [
                    'nombre' => "Barra Puesto {$i}",
                    'capacidad' => 1,
                    'posicion_x' => ($i - 1) * 100 + 20,
                    'posicion_y' => 40,
                    'ancho' => 80,
                    'alto' => 80,
                    'forma' => 'redonda',
                    'activo' => true,
                ]
            );
        }
    }

    private function crearVisita(string $tenantId, string $branchId, int $mesaId, string $meseroId, string $estado, int $personas, array $items): void
    {
        VisitaModel::where('mesa_id', $mesaId)->delete();

        $total = array_reduce($items, fn ($sum, $item) => $sum + ($item['cant'] * $item['precio']), 0.0);

        $visita = VisitaModel::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'mesa_id' => $mesaId,
            'mesero_id' => $meseroId,
            'cliente_nombre' => 'Familia Morales',
            'personas' => $personas,
            'estado' => $estado,
            'imprimio_cuenta' => ($estado === 'PRECUENTA'),
            'fecha_apertura' => now()->subMinutes(rand(15, 60)),
            'total' => $total,
            'notas' => 'Mesa preferencial',
        ]);

        foreach ($items as $item) {
            VisitaDetalleModel::create([
                'visita_id' => $visita->id,
                'producto_nombre' => $item['producto'],
                'cantidad' => $item['cant'],
                'precio_unitario' => $item['precio'],
                'subtotal' => $item['cant'] * $item['precio'],
                'observaciones' => 'Sin picante',
                'estado' => 'SERVIDO',
            ]);
        }
    }
}