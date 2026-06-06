# POS-CAT — Catálogo vendible para caja y garzón

## Objetivo

Mejorar el selector de productos cuando el catálogo supera ~200 ítems: sin scroll masivo inicial, búsqueda rápida, categorías, favoritos/recientes y límite de resultados.

## Decisión de API

`GET /products?include=active_prices` carga **todo** el catálogo en memoria. Para POS se creó un endpoint dedicado:

```
GET /api/v1/products/pos-catalog
```

Permiso: `products.list` (mismo que listado de productos).

## Parámetros

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `search` | string | — | Búsqueda en nombre, SKU, barcode y categoría (mín. 2 caracteres) |
| `category_id` | int | — | Filtra por categoría |
| `sellable_only` | bool | `true` | Solo productos con `has_active_pricing` |
| `unpriced_only` | bool | `false` | Solo activos sin precio vigente |
| `product_ids` | csv | — | IDs explícitos (favoritos/recientes) |
| `limit` | int | `20` | Máximo 50 |
| `grouped` | bool | `false` | Agrupa resultados por categoría |

## Regla de productos vacíos

Sin filtros activos, la respuesta devuelve **categorías + meta** pero `products: []`.

Productos solo cuando:

- `search` ≥ 2 letras, o
- `category_id`, o
- `product_ids`, o
- `unpriced_only=1`

## Respuesta

```json
{
  "categories": [
    { "id": 1, "name": "Bebidas", "product_count": 45, "sellable_count": 40 }
  ],
  "products": [
    {
      "id": 10,
      "name": "Paceña",
      "category_id": 1,
      "category_name": "Bebidas",
      "active_prices": [...],
      "has_active_pricing": true
    }
  ],
  "grouped": null,
  "meta": {
    "total_active": 200,
    "sellable_count": 180,
    "unpriced_count": 20,
    "result_count": 20,
    "matched_count": 45,
    "limit": 20,
    "has_more": true
  }
}
```

## Archivos

| Archivo | Rol |
|---------|-----|
| `app/Application/Product/DTOs/GetPosCatalogInput.php` | Input del caso de uso |
| `app/Application/Product/UseCases/GetPosCatalogUseCase.php` | Lógica de filtrado y meta |
| `app/Http/Controllers/Api/V1/ProductController.php` | `posCatalog()` |
| `routes/api.php` | Ruta antes de `products/{id}` |
| `tests/Feature/Api/V1/PosCatalogApiTest.php` | Tests de regresión |

## Tests (9/9)

- Categorías + meta sin productos sin filtro
- `sellable_only` por defecto
- `unpriced_only`
- Filtro por categoría
- Búsqueda (≥ 2 caracteres)
- `product_ids`
- Límite y `has_more`
- Aislamiento tenant
- Garzón solo ve activos

## Ejecutar tests

```bash
php artisan test tests/Feature/Api/V1/PosCatalogApiTest.php
```

## Próximo paso

Continuar con **SSE-1** y **SSE-2** (fuera de alcance de POS-CAT).
