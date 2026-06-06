# DIRECT_SALE_PRICING_FIX_REPORT.md

**Fase:** DSP — Solución precios en Venta directa
**Fecha:** 2026-06-05
**Archivo principal:** `frontend/src/pages/nightpos/cash/direct-sale.vue`
**Referencias:** `DIRECT_SALE_PRICING_AUDIT_AND_ROADMAP.md`, `backend/DIRECT_SALES_REPORT.md`, `backend/PHASE_6_REPORT.md`, `FRONTEND_GUIDELINES.md`

---

## 1. Causa del bug

En Venta directa aparecía:

> «Este producto no tiene precio activo para esa modalidad.»

aun cuando el producto **sí** tenía precios en `product_prices`.

**Raíz:** `direct-sale.vue` cargaba el catálogo con `fetchProducts()` **sin** `include=active_prices`. El helper `productActivePrice()` depende de `product.active_prices`, que no venía en la respuesta. Resultado: todos los productos parecían sin precio y el clic en la tarjeta (que agregaba `SOLO_CLIENTE` por defecto) disparaba el toast de error.

`GET /products` solo adjunta precios cuando se pide explícitamente:

```php
// GetProductsUseCase.php
$includeActivePrices = $input instanceof GetProductsListInput && $input->includeActivePrices;
```

Otras pantallas (garzón, catálogo, precios) ya usaban `include=active_prices`; venta directa era la excepción.

---

## 2. Cambio `include=active_prices` (DSP-1)

```diff
- products.value = await fetchProducts()
+ products.value = await fetchProducts({ include: 'active_prices' })
```

Con esto cada producto trae:

```json
{
  "active_prices": [
    { "sale_mode": "SOLO_CLIENTE", "price": "40.00", "status": "active" },
    { "sale_mode": "CON_ACOMPANANTE", "price": "80.00", "girl_amount": "40.00", "house_amount": "40.00", "status": "active" }
  ],
  "has_active_pricing": true
}
```

---

## 3. UX nueva (DSP-2 / DSP-3)

### 3.1 Tarjeta de producto

Antes: tarjeta completa clickeable → agregaba SOLO_CLIENTE sin validar; chips de precio solo si existían.

Ahora cada tarjeta muestra:

```
┌──────────────────────────┐
│ Paceña                   │
│ Solo              40 Bs   │
│ Con acompañante   80 Bs   │   (o "Sin precio" en gris)
│ [ Solo ]  [ +Acomp. ]     │   (deshabilitados si no hay precio)
│ Configurar precio         │   (si no hay ningún precio y hay permiso)
└──────────────────────────┘
```

Cambios concretos:

| Elemento | Antes | Ahora |
|---|---|---|
| Clic global en tarjeta | `@click="addToCart(SOLO_CLIENTE)"` | **Eliminado** |
| Modalidad | Chips solo si hay precio | Líneas Solo / Con acompañante siempre, con monto o «Sin precio» |
| Agregar | Clic ciego | Botones explícitos «Solo» / «+Acomp.» |
| Sin precio | No agregaba pero mostraba error | Botón **deshabilitado** (`:disabled="!hasPrice(...)"`) |
| Producto sin precio | — | Botón «Configurar precio» (si permiso) |

### 3.2 Configurar precio desde POS (DSP-3)

Se reutiliza `QuickProductPriceCreateDialog`:

```vue
<QuickProductPriceCreateDialog
  v-model="showPriceDialog"
  :product-id="priceDialogProduct?.id ?? null"
  :product-name="priceDialogProduct?.name ?? ''"
  :sale-mode="priceDialogMode"
  @created="onPriceCreated"
/>
```

Al guardar (`onPriceCreated`): cierra el diálogo, **recarga el catálogo** (`loadProducts()` con `active_prices`) y notifica. El producto queda vendible sin salir de Venta directa.

Visibilidad del botón: `canConfigurePrice = can('product_prices.quick_create') || can('products.update')` (mismo criterio que comandas).

---

## 4. Validaciones (DSP-4)

| Capa | Rol |
|---|---|
| **Backend** | Fuente de verdad. `POST /direct-sales` → `ProductPriceResolver` → 422 si no hay precio activo. |
| **Frontend** | Solo previene errores: deshabilita botones sin precio, no agrega modalidad sin precio. |
| **Error backend** | `confirmSale` captura con `getApiErrorMessage` y muestra notificación; **no rompe** la pantalla. |

El frontend nunca es la autoridad final del precio: aunque el catálogo esté desfasado, el backend revalida al cobrar.

---

## 5. Tests

### Backend (`tests/Feature/Api/V1/DirectSaleApiTest.php`) — 11/11

```
✓ cajera can create direct sale with open cash session
✓ direct sale creates sale record with order_id null
✓ direct sale creates sale_items with order_item_id null
✓ direct sale creates sale payment record
✓ direct sale creates INCOME cash movement
✓ rejects direct sale without open cash session
✓ denies waiter from creating direct sale
✓ rejects CON_ACOMPANANTE item without girl_user_id
✓ accepts CON_ACOMPANANTE item with girl and stores girl snapshots
✓ tenant isolation: direct sale not visible to other tenant
✓ rejects direct sale of product without active price   ← NUEVO (DSP-4)

Tests: 11 passed (59 assertions)
```

El test nuevo crea un producto **sin** `product_prices` y verifica:
- HTTP 422
- Mensaje: «Este producto no tiene precio configurado para la modalidad SOLO_CLIENTE.»
- No se crea ninguna venta.

---

## 6. Validación manual

| # | Paso | Resultado esperado |
|---|------|--------------------|
| 1 | Crear producto con precio SOLO | Producto en catálogo |
| 2 | Abrir Venta directa | Muestra precio Solo |
| 3 | Botón «Solo» | Agrega al carrito sin error |
| 4 | Cobrar | Venta `V-XXXX` + ingreso en Mi caja |
| 5 | Crear producto sin precio (vía `POST /products` o admin) | Producto en catálogo |
| 6 | Venta directa | Muestra «Sin precio» en gris |
| 7 | Botón «Solo» / «+Acomp.» | **Deshabilitados** |
| 8 | «Configurar precio» (con permiso) | Abre diálogo, guarda precio |
| 9 | Tras guardar | Catálogo se refresca; producto vendible |
| 10 | Producto CON_ACOMPANANTE | Exige chica antes de cobrar |

---

## 7. Archivos modificados

| Archivo | Cambio |
|---|---|
| `frontend/src/pages/nightpos/cash/direct-sale.vue` | DSP-1..4: include active_prices, UX por modalidad, configurar precio, validación |
| `backend/tests/Feature/Api/V1/DirectSaleApiTest.php` | Test 11: producto sin precio → 422 |
| `frontend/DIRECT_SALES_REPORT.md` | Sección de precios por modalidad |
| `frontend/DIRECT_SALE_PRICING_FIX_REPORT.md` | Este reporte |

---

## 8. Actualización — Pago mixto (jun 2026)

Venta directa ahora soporta **pago mixto** (Efectivo + QR + Tarjeta) mediante `MixedPaymentForm` y `useMixedPayments.js`. Ver `frontend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`.

---

## 9. Siguiente fase

**FASE POS-CAT — Catálogo vendible:**
- Pantalla «Productos sin precio» en Catálogo
- Filtro «vendibles» en POS (venta directa + comandas)
- Mejora de catálogo POS para 200+ productos (paginación / virtual scroll)
- Endpoint `GET /products/pos-catalog` si hace falta

---

*Fase DSP completada. Backend fuente de verdad; frontend previene errores y permite configurar precio en caja.*
