<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\CategoriaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ObservacionCocinaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = TenantModel::first();
        if (!$tenant) return;

        // Categorías
        $cats = [
            [
                'codigo' => 'CAT-01',
                'nombre' => 'Platos Fuertes',
                'icono' => 'ri-restaurant-2-line',
                'color' => 'primary',
                'orden' => 1,
                'productos' => [
                    ['codigo' => 'PF-01', 'nombre' => 'Pique Macho Ribersoft', 'descripcion' => 'Lomo de res, salchichas, huevo, papas fritas, locoto y tomate', 'precio' => 95.00, 'costo' => 45.00, 'tiempo' => 20, 'impresion' => 'COCINA'],
                    ['codigo' => 'PF-02', 'nombre' => 'Silpancho Cochabambino', 'descripcion' => 'Carne apanada sobre arroz, papas doradas, huevo frito y salsa', 'precio' => 55.00, 'costo' => 22.00, 'tiempo' => 15, 'impresion' => 'COCINA'],
                    ['codigo' => 'PF-03', 'nombre' => 'Majadito Tostado de Charque', 'descripcion' => 'Arroz tostado con charque de res, huevo frito, plátano maduro y yuca', 'precio' => 48.00, 'costo' => 18.00, 'tiempo' => 12, 'impresion' => 'COCINA'],
                    ['codigo' => 'PF-04', 'nombre' => 'Sopa de Maní Tradicional', 'descripcion' => 'Sopa típica boliviana con costilla de res, fideo tostado y papas hilo', 'precio' => 35.00, 'costo' => 12.00, 'tiempo' => 10, 'impresion' => 'COCINA'],
                ],
            ],
            [
                'codigo' => 'CAT-02',
                'nombre' => 'Parrilladas & Carnes',
                'icono' => 'ri-fire-line',
                'color' => 'error',
                'orden' => 2,
                'productos' => [
                    ['codigo' => 'PAR-01', 'nombre' => 'Bife de Chorizo 400g', 'descripcion' => 'Corte premium a la parrilla con papas fritas y ensalada fresca', 'precio' => 110.00, 'costo' => 50.00, 'tiempo' => 25, 'impresion' => 'COCINA'],
                    ['codigo' => 'PAR-02', 'nombre' => 'Parrillada Familiar Ribersoft (4p)', 'descripcion' => 'Costillas de cerdo, bife, cuadril, chorizo parrillero, morcilla, yuca y papas', 'precio' => 280.00, 'costo' => 120.00, 'tiempo' => 30, 'impresion' => 'COCINA'],
                    ['codigo' => 'PAR-03', 'nombre' => 'Cuadril a la Parrilla 350g', 'descripcion' => 'Jugoso cuadril de primera con guarnición a elección', 'precio' => 85.00, 'costo' => 38.00, 'tiempo' => 20, 'impresion' => 'COCINA'],
                ],
            ],
            [
                'codigo' => 'CAT-03',
                'nombre' => 'Hamburguesas & Snacks',
                'icono' => 'ri-goblet-line',
                'color' => 'warning',
                'orden' => 3,
                'productos' => [
                    ['codigo' => 'HMB-01', 'nombre' => 'Hamburguesa Ribersoft Monster', 'descripcion' => 'Doble carne 180g, queso cheddar, tocino crocante, aros de cebolla y salsa especial', 'precio' => 55.00, 'costo' => 20.00, 'tiempo' => 15, 'impresion' => 'COCINA'],
                    ['codigo' => 'HMB-02', 'nombre' => 'Hamburguesa Clásica con Queso', 'descripcion' => 'Carne 150g, queso gouda, lechuga, tomate y papas rústicas', 'precio' => 38.00, 'costo' => 14.00, 'tiempo' => 12, 'impresion' => 'COCINA'],
                    ['codigo' => 'SNK-01', 'nombre' => 'Alitas BBQ Crujientes (12 uds)', 'descripcion' => 'Bañadas en salsa BBQ ahumada con bastones de apio y salsa tártara', 'precio' => 50.00, 'costo' => 20.00, 'tiempo' => 18, 'impresion' => 'COCINA'],
                ],
            ],
            [
                'codigo' => 'CAT-04',
                'nombre' => 'Cervezas & Tragos',
                'icono' => 'ri-beer-line',
                'color' => 'info',
                'orden' => 4,
                'productos' => [
                    ['codigo' => 'CER-01', 'nombre' => 'Paceña Huari 620ml', 'descripcion' => 'Cerveza premium nacional bien fría', 'precio' => 30.00, 'costo' => 15.00, 'tiempo' => 3, 'impresion' => 'BAR'],
                    ['codigo' => 'CER-02', 'nombre' => 'Paceña Pilsener 620ml', 'descripcion' => 'La clásica cerveza boliviana', 'precio' => 25.00, 'costo' => 12.00, 'tiempo' => 3, 'impresion' => 'BAR'],
                    ['codigo' => 'TRG-01', 'nombre' => 'Singani Chuflay Tradicional', 'descripcion' => 'Singani Casa Real Gran Singani con Canada Dry Ginger Ale y limón', 'precio' => 35.00, 'costo' => 10.00, 'tiempo' => 5, 'impresion' => 'BAR'],
                    ['codigo' => 'TRG-02', 'nombre' => 'Mojito Clásico Cubano', 'descripcion' => 'Ron Havana Club, hierbabuena fresca, azúcar rubia y soda', 'precio' => 40.00, 'costo' => 12.00, 'tiempo' => 5, 'impresion' => 'BAR'],
                    ['codigo' => 'VIN-01', 'nombre' => 'Vino Tinto Campos de Solana', 'descripcion' => 'Botella de Cabernet Sauvignon 750ml Valle de Tarija', 'precio' => 95.00, 'costo' => 45.00, 'tiempo' => 3, 'impresion' => 'BAR'],
                ],
            ],
            [
                'codigo' => 'CAT-05',
                'nombre' => 'Bebidas & Jugos',
                'icono' => 'ri-cup-line',
                'color' => 'secondary',
                'orden' => 5,
                'productos' => [
                    ['codigo' => 'BEB-01', 'nombre' => 'Coca Cola 2 Litros Retornable', 'descripcion' => 'Gaseosa para compartir', 'precio' => 22.00, 'costo' => 11.00, 'tiempo' => 2, 'impresion' => 'BAR'],
                    ['codigo' => 'BEB-02', 'nombre' => 'Jarra de Limonada con Menta 1.5L', 'descripcion' => 'Refrescante limonada natural con menta y hielo', 'precio' => 28.00, 'costo' => 8.00, 'tiempo' => 5, 'impresion' => 'BAR'],
                    ['codigo' => 'BEB-03', 'nombre' => 'Mocochinchi Tradicional', 'descripcion' => 'Refresco típico hervido con canela, clavo de olor y durazno deshidratado', 'precio' => 12.00, 'costo' => 4.00, 'tiempo' => 2, 'impresion' => 'BAR'],
                    ['codigo' => 'BEB-04', 'nombre' => 'Agua Vital Mineral 600ml', 'descripcion' => 'Con o sin gas', 'precio' => 10.00, 'costo' => 4.00, 'tiempo' => 2, 'impresion' => 'BAR'],
                ],
            ],
        ];

        foreach ($cats as $catData) {
            $cat = CategoriaModel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'codigo' => $catData['codigo']],
                [
                    'nombre' => $catData['nombre'],
                    'icono' => $catData['icono'],
                    'color' => $catData['color'],
                    'orden' => $catData['orden'],
                    'activo' => true,
                ]
            );

            $pOrden = 1;
            foreach ($catData['productos'] as $prod) {
                ProductoModel::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'codigo' => $prod['codigo']],
                    [
                        'categoria_id' => $cat->id,
                        'nombre' => $prod['nombre'],
                        'descripcion' => $prod['descripcion'],
                        'precio' => $prod['precio'],
                        'costo' => $prod['costo'],
                        'tiempo_preparacion' => $prod['tiempo'],
                        'destino_impresion' => $prod['impresion'],
                        'orden' => $pOrden++,
                        'activo' => true,
                    ]
                );
            }
        }

        // Observaciones de Cocina
        $obs = [
            'Sin picante / Llajwa aparte',
            'Término medio',
            'Bien cocido',
            'Poco cocido / Sellado',
            'Sin cebolla',
            'Sin sal / Bajo en sodio',
            'Para llevar / Empacar',
            'Extra papas fritas',
            'Urgente comanda rápida',
        ];

        $oOrden = 1;
        foreach ($obs as $desc) {
            ObservacionCocinaModel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'descripcion' => $desc],
                ['orden' => $oOrden++, 'activo' => true]
            );
        }
    }
}