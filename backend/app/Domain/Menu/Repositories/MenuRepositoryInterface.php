<?php

declare(strict_types=1);

namespace App\Domain\Menu\Repositories;

use App\Domain\Menu\Entities\Categoria;
use App\Domain\Menu\Entities\Producto;

interface MenuRepositoryInterface
{
    /**
     * @return Categoria[]
     */
    public function getCategorias(string $tenantId): array;

    /**
     * @return Producto[]
     */
    public function getProductos(string $tenantId, ?int $categoriaId = null, ?string $busqueda = null): array;

    public function findProductoById(int $id): ?Producto;

    /**
     * @return array<array{id: int, descripcion: string}>
     */
    public function getObservacionesCocina(string $tenantId): array;
}