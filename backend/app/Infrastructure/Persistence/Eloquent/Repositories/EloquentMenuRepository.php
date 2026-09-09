<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Menu\Entities\Categoria;
use App\Domain\Menu\Entities\Producto;
use App\Domain\Menu\Repositories\MenuRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CategoriaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ObservacionCocinaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;

class EloquentMenuRepository implements MenuRepositoryInterface
{
    public function getCategorias(string $tenantId): array
    {
        $categorias = CategoriaModel::where('tenant_id', $tenantId)
            ->where('activo', true)
            ->withCount(['productos' => function ($q) {
                $q->where('activo', true);
            }])
            ->orderBy('orden')
            ->get();

        return $categorias->map(fn (CategoriaModel $m) => new Categoria(
            id: $m->id,
            tenantId: (string) $m->tenant_id,
            nombre: $m->nombre,
            codigo: $m->codigo,
            icono: $m->icono,
            color: $m->color,
            orden: (int) $m->orden,
            activo: (bool) $m->activo,
            totalProductos: (int) ($m->productos_count ?? 0),
        ))->all();
    }

    public function getProductos(string $tenantId, ?int $categoriaId = null, ?string $busqueda = null): array
    {
        $query = ProductoModel::where('tenant_id', $tenantId)
            ->where('activo', true)
            ->with('categoria');

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($busqueda && trim($busqueda) !== '') {
            $term = '%' . trim($busqueda) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('codigo', 'like', $term)
                    ->orWhere('descripcion', 'like', $term);
            });
        }

        $productos = $query->orderBy('orden')->orderBy('nombre')->get();

        return $productos->map(fn (ProductoModel $p) => new Producto(
            id: $p->id,
            tenantId: (string) $p->tenant_id,
            categoriaId: (int) $p->categoria_id,
            nombre: $p->nombre,
            codigo: $p->codigo,
            descripcion: $p->descripcion,
            precio: (float) $p->precio,
            costo: (float) $p->costo,
            tiempoPreparacion: (int) $p->tiempo_preparacion,
            destinoImpresion: (string) $p->destino_impresion,
            imagen: $p->imagen,
            activo: (bool) $p->activo,
            orden: (int) $p->orden,
            categoriaNombre: $p->categoria?->nombre,
        ))->all();
    }

    public function findProductoById(int $id): ?Producto
    {
        $p = ProductoModel::with('categoria')->find($id);
        if (!$p) return null;

        return new Producto(
            id: $p->id,
            tenantId: (string) $p->tenant_id,
            categoriaId: (int) $p->categoria_id,
            nombre: $p->nombre,
            codigo: $p->codigo,
            descripcion: $p->descripcion,
            precio: (float) $p->precio,
            costo: (float) $p->costo,
            tiempoPreparacion: (int) $p->tiempo_preparacion,
            destinoImpresion: (string) $p->destino_impresion,
            imagen: $p->imagen,
            activo: (bool) $p->activo,
            orden: (int) $p->orden,
            categoriaNombre: $p->categoria?->nombre,
        );
    }

    public function getObservacionesCocina(string $tenantId): array
    {
        return ObservacionCocinaModel::where('tenant_id', $tenantId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get(['id', 'descripcion'])
            ->toArray();
    }
}