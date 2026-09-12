<?php

declare(strict_types=1);

namespace App\Domain\Pedido\Repositories;

interface PedidoRepositoryInterface
{
    /**
     * @param array<array{producto_id: int, cantidad: float, observaciones: ?string}> $items
     */
    public function agregarItemsMesa(int $mesaId, array $items): array;

    /**
     * @param array<array{producto_id: int, cantidad: float, observaciones: ?string}> $items
     */
    public function agregarItemsVisita(int $visitaId, array $items): array;

    public function eliminarItemMesa(int $detalleId): bool;
}
