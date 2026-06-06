# VENTA DIRECTA DESDE CAJA — FRONTEND REPORT

**Fecha:** 2026-06-05  
**Ruta:** `/nightpos/cash/direct-sale`  
**Nombre de ruta:** `nightpos-cash-direct-sale`  
**Permiso:** `sales.direct_create`

---

## ¿Qué es?

La pantalla de venta directa permite a la cajera vender productos inmediatamente desde caja **sin crear una comanda**. Es una pantalla tipo POS simplificada.

## Diferencia con comanda

| Flujo | Acción |
|---|---|
| Venta directa | Seleccionar producto → carrito → cobrar → listo |
| Comanda | Crear comanda → agregar ítems → enviar a barra → cobrar |

---

## Archivos creados / modificados

| Archivo | Tipo |
|---|---|
| `src/pages/nightpos/cash/direct-sale.vue` | **Nuevo** — página principal |
| `src/api/sales.js` | Modificado — se agregó `createDirectSale()` |
| `src/composables/useNightPosPermissions.js` | Modificado — se agregó `canDirectSale` |
| `src/navigation/vertical/nightpos-r4.js` | Modificado — se agregó entrada de menú |

---

## Pantalla

### Estado 1: Sin caja abierta
- Muestra un `VAlert` de advertencia
- Botón "Abrir caja ahora" abre `QuickOpenCashDialog`
- Al abrir caja, habilita el formulario POS

### Estado 2: POS activo
Layout de dos columnas:

**Columna izquierda (Catálogo):**
- Campo de búsqueda de producto por nombre
- Chips de categorías para filtrar
- Grilla de productos (2-4 columnas responsivas)
- Cada producto muestra nombre y precios disponibles
- Click en chip de precio agrega el ítem al carrito con esa modalidad
- Si el producto tiene precio `CON_ACOMPANANTE`, muestra un chip separado `+C precio`

**Columna derecha (Carrito):**
- Lista de ítems agregados
- Controles de cantidad +/-
- Botón de borrar ítem
- Para ítems `CON_ACOMPANANTE`: selector de chica
- Total calculado en tiempo real
- Selector de método de pago (Efectivo / QR / Tarjeta)
- Botón grande "Cobrar Bs. X.XX"

### Estado 3: Venta completada
- Card verde con ícono de check
- Número de venta, total cobrado, método de pago
- Botón "Nueva venta" que resetea el formulario

---

## Flujo de pago

1. La cajera selecciona uno o más productos del catálogo
2. Ajusta cantidades en el carrito
3. Asigna chica si hay ítems `CON_ACOMPANANTE`
4. Elige método de pago
5. Hace click en "Cobrar"
6. Se llama `POST /api/v1/direct-sales`
7. El backend resuelve los precios, crea la venta y el movimiento de caja
8. Se muestra la pantalla de éxito con el número de venta

---

## Navegación

La entrada en el menú queda bajo "Caja":
```
Operación NightPOS
  ├── ...
  ├── Caja
  │   ├── Mi caja            (cash.access)
  │   └── Venta directa      (sales.direct_create)
  └── ...
```

Solo visible para roles con `sales.direct_create` (cajera, cajera senior, tenant_owner, super_admin).

---

## Composable / permiso

```js
const { canDirectSale } = useNightPosPermissions()
// canDirectSale.value = true si tiene 'sales.direct_create'
```

---

## Validaciones frontend

| Condición | Acción |
|---|---|
| Sin caja abierta | Muestra alerta + botón abrir caja |
| Carrito vacío al cobrar | Notificación de advertencia |
| Ítem CON_ACOMPANANTE sin chica | Notificación de advertencia |
| **Modalidad sin precio activo** | Botón deshabilitado; no agrega al carrito |
| Error de API | Notificación de error con mensaje del servidor |

---

## Precios por modalidad (Fase DSP, jun 2026)

> Detalle completo en `frontend/DIRECT_SALE_PRICING_FIX_REPORT.md`.

El catálogo del POS ahora carga `active_prices`:

```js
products.value = await fetchProducts({ include: 'active_prices' })
```

Cada tarjeta de producto muestra el precio por modalidad y botones explícitos:

| Elemento | Comportamiento |
|---|---|
| Línea «Solo» | Precio SOLO_CLIENTE o «Sin precio» |
| Línea «Con acompañante» | Precio CON_ACOMPANANTE o «Sin precio» |
| Botón «Solo» | Deshabilitado si no hay precio SOLO_CLIENTE |
| Botón «+Acomp.» | Deshabilitado si no hay precio CON_ACOMPANANTE |
| «Configurar precio» | Visible si el producto no tiene ningún precio y el usuario tiene `product_prices.quick_create` o `products.update` |

Ya **no** existe el clic global en la tarjeta que agregaba SOLO_CLIENTE sin validar. Solo se agrega por botón de modalidad con precio activo.

Al configurar un precio desde el POS (`QuickProductPriceCreateDialog`), se recarga el catálogo y el producto queda vendible sin salir de Venta directa.

---

## Impacto en liquidaciones

Los ítems con `sale_mode = CON_ACOMPANANTE` y `girl_user_id` asignado alimentan el sistema de liquidación de chicas, exactamente igual que los ítems de comanda cobrada. No hay cambios en el frontend de liquidaciones para soportar esto: el backend lo maneja automáticamente.

---

## Pago mixto (jun 2026)

> Detalle en `frontend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`.

Venta directa usa `MixedPaymentForm` (variant `inline`) en el panel carrito:

- Campos Efectivo / QR / Tarjeta
- Atajos: Todo efectivo, Todo QR, Todo tarjeta, Limpiar
- Total ingresado, faltante, cambio (efectivo)
- Envía `payments[]` con uno o más métodos (igual que cobro de comanda)

Componente compartido con `ChargeOrderModal` vía `useMixedPayments.js`.

---

## Limitaciones actuales

- No hay impresión de ticket (pendiente de fase de impresión)
- Los precios se muestran desde `active_prices` cargados al listar. El backend vuelve a resolver el precio oficial al momento de cobrar (fuente de verdad).
- Solo acepta una chica por ítem (igual que en comandas)
- La grilla no pagina aún (mejora prevista en Fase POS-CAT para 200+ productos)
