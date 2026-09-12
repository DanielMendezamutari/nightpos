<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Pedido\Repositories\PedidoRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Support\Facades\DB;

class EloquentPedidoRepository implements PedidoRepositoryInterface
{
    public function agregarItemsVisita(int $visitaId, array $items): array
    {
        return DB::transaction(function () use ($visitaId, $items) {
            $visita = VisitaModel::where('id', $visitaId)
                ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
                ->first();

            if (!$visita) {
                throw new \RuntimeException('La cuenta no está activa para agregar pedidos');
            }

            $creados = [];

            foreach ($items as $item) {
                $producto = ProductoModel::findOrFail($item['producto_id']);
                $cantidad = max(0.25, (float) ($item['cantidad'] ?? 1));
                $precio = (float) $producto->precio;
                $subtotal = round($cantidad * $precio, 2);

                $detalle = VisitaDetalleModel::create([
                    'visita_id' => $visita->id,
                    'producto_id' => $producto->id,
                    'producto_nombre' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                    'observaciones' => $item['observaciones'] ?? null,
                    'estacion_cocina' => $producto->estacion_cocina ?? $producto->destino_impresion ?? 'COCINA',
                    'estado' => 'EN_PREPARACION',
                    'iniciado_at' => now(),
                    'terminado_at' => null,
                ]);

                $creados[] = $detalle;
            }

            // Recalcular total de la visita
            $totalActualizado = VisitaDetalleModel::where('visita_id', $visita->id)
                ->where('estado', '!=', 'CANCELADO')
                ->sum('subtotal');

            $visita->total = $totalActualizado;
            $visita->save();

            return [
                'visita_id' => $visita->id,
                'total' => (float) $totalActualizado,
                'items_agregados' => count($creados),
            ];
        });
    }

    public function agregarItemsMesa(int $mesaId, array $items): array
    {
        $visita = VisitaModel::where('mesa_id', $mesaId)
            ->whereRaw("UPPER(estado) IN ('ABIERTA', 'PRECUENTA')")
            ->latest('id')
            ->first();

        if (!$visita) {
            throw new \RuntimeException('La mesa no tiene una cuenta abierta para agregar pedidos');
        }

        return $this->agregarItemsVisita($visita->id, $items);
    }

    public function eliminarItemMesa(int $detalleId): bool
    {
        return DB::transaction(function () use ($detalleId) {
            $detalle = VisitaDetalleModel::with('visita')->findOrFail($detalleId);
            $visita = $detalle->visita;

            $detalle->delete();

            $totalActualizado = VisitaDetalleModel::where('visita_id', $visita->id)
                ->where('estado', '!=', 'CANCELADO')
                ->sum('subtotal');

            $visita->total = $totalActualizado;
            $visita->save();

            return true;
        });
    }
}
