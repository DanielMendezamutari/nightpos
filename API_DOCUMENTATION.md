# API_DOCUMENTATION.md

# OBJETIVO

Definir los endpoints objetivo para el nuevo backend API REST.

Base URL:

```text
/api/v1
```

Auth:

```text
Authorization: Bearer {token}
```

---

# AUTH

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| POST | `/auth/login` | Login usuario/contraseña |
| POST | `/auth/login-pin` | Login por código/PIN |
| POST | `/auth/logout` | Cerrar sesión |
| GET | `/auth/me` | Usuario autenticado |
| POST | `/auth/refresh` | Renovar token |

---

# TENANTS / SaaS

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/tenants` | Listar empresas SaaS |
| POST | `/tenants` | Crear empresa |
| GET | `/tenants/{id}` | Ver empresa |
| PUT | `/tenants/{id}` | Actualizar empresa |
| POST | `/tenants/{id}/activate` | Activar |
| POST | `/tenants/{id}/suspend` | Suspender |
| GET | `/tenants/{id}/subscription` | Ver suscripción |
| PUT | `/tenants/{id}/subscription` | Actualizar membresía |

---

# SUCURSALES / CASAS

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/branches` | Listar casas/sucursales |
| POST | `/branches` | Crear casa |
| GET | `/branches/{id}` | Ver casa |
| PUT | `/branches/{id}` | Actualizar casa |
| DELETE | `/branches/{id}` | Desactivar casa |

---

# PRODUCTOS Y PRECIOS

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/products` | Listar productos |
| POST | `/products` | Crear producto |
| GET | `/products/{id}` | Ver producto |
| PUT | `/products/{id}` | Actualizar producto |
| DELETE | `/products/{id}` | Desactivar producto |
| GET | `/products/{id}/prices` | Ver precios del producto |
| POST | `/products/{id}/prices` | Crear precio |
| PUT | `/product-prices/{id}` | Actualizar precio |

## Crear precio

```json
{
  "branch_id": 1,
  "price_type": "CON_ACOMPANANTE",
  "amount": 1200,
  "currency": "BOB"
}
```

---

# MESAS / SALAS

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/rooms` | Listar salas/ambientes |
| POST | `/rooms` | Crear sala |
| GET | `/tables` | Listar mesas |
| POST | `/tables` | Crear mesa/privado |
| PUT | `/tables/{id}/status` | Cambiar estado |

Estados sugeridos:

* `AVAILABLE`
* `OCCUPIED`
* `RESERVED`
* `CLOSED`

---

# PEDIDOS / COMANDAS

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| POST | `/orders` | Abrir comanda |
| GET | `/orders` | Listar comandas |
| GET | `/orders/{id}` | Ver comanda |
| POST | `/orders/{id}/items` | Agregar producto |
| PUT | `/orders/{id}/items/{itemId}` | Editar item |
| DELETE | `/orders/{id}/items/{itemId}` | Quitar item |
| POST | `/orders/{id}/send-to-bar` | Enviar a barra |
| POST | `/orders/{id}/cancel` | Cancelar comanda |
| POST | `/orders/{id}/charge` | Cobrar comanda |

## Agregar producto a comanda

```json
{
  "product_id": 15,
  "quantity": 1,
  "price_type": "SOLO",
  "notes": "fría"
}
```

Respuesta esperada:

```json
{
  "data": {
    "product_id": 15,
    "price_type": "SOLO",
    "unit_price": 30,
    "quantity": 1,
    "total": 30
  }
}
```

---

# VENTAS

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| POST | `/sales` | Venta directa |
| GET | `/sales` | Listar ventas |
| GET | `/sales/{id}` | Ver venta |
| POST | `/sales/{id}/void` | Anular venta |
| GET | `/sales/reports/by-date` | Ventas por fecha |
| GET | `/sales/reports/by-cash-session` | Ventas por caja |

---

# CAJA

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| POST | `/cash-sessions/open` | Abrir caja |
| GET | `/cash-sessions/current` | Caja actual |
| POST | `/cash-sessions/{id}/movements` | Ingreso/egreso |
| POST | `/cash-sessions/{id}/close` | Cerrar caja |
| GET | `/cash-sessions/{id}/summary` | Resumen caja |

---

# INVENTARIO

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/inventory/stock` | Stock actual |
| POST | `/inventory/adjustments` | Ajuste de stock |
| GET | `/inventory/kardex` | Kardex |
| POST | `/transfers` | Traspaso entre sucursales |
| POST | `/purchases` | Registrar compra |

---

# REPORTES

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/reports/dashboard` | Dashboard |
| GET | `/reports/products/top-selling` | Más vendidos |
| GET | `/reports/sales/by-waiter` | Ventas por garzón |
| GET | `/reports/commissions` | Comisiones |
| GET | `/reports/cash/by-date` | Cajas por fecha |
| GET | `/reports/profit/by-date` | Ganancias |

---

# REGLA IMPORTANTE

Todo endpoint debe responder JSON uniforme:

```json
{
  "success": true,
  "message": "Operación realizada correctamente.",
  "data": {}
}
```

Errores:

```json
{
  "success": false,
  "message": "No existe caja abierta para este usuario.",
  "errors": {}
}
```

---

# PRINTING / IMPRESIÓN AUTOMÁTICA

Endpoints para el Print Agent local y para reimpresiones.

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/print-jobs/pending` | Listar trabajos pendientes para la sucursal |
| POST | `/print-jobs/{id}/printing` | Marcar trabajo como en impresión |
| POST | `/print-jobs/{id}/printed` | Marcar trabajo como impreso |
| POST | `/print-jobs/{id}/failed` | Marcar trabajo como fallido |
| POST | `/orders/{id}/reprint` | Crear reimpresión de una comanda |
| GET | `/printers` | Listar impresoras configuradas |
| POST | `/printers` | Crear impresora |
| PUT | `/printers/{id}` | Actualizar impresora |

## GET `/print-jobs/pending`

Uso: Print Agent local.

Parámetros sugeridos:

```text
branch_id
agent_token
```

Respuesta:

```json
{
  "data": [
    {
      "id": 1,
      "document_type": "ORDER_TICKET",
      "destination": "BAR",
      "copies": 1,
      "payload": {
        "order_number": "150",
        "table": "Mesa 4",
        "waiter": "Carlos",
        "items": [
          { "name": "Havana", "quantity": 1, "price_type": "CON_ACOMPANANTE" }
        ]
      }
    }
  ]
}
```

## POST `/orders/{id}/reprint`

Uso: caja o encargado.

Debe crear un nuevo `PrintJob` con `document_type = REPRINT_ORDER`.


---

# CIERRE DE TURNO / LIQUIDACIONES

## Cierre de caja

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/shift-closures/current` | Ver caja/turno abierto actual |
| GET | `/shift-closures/{id}/summary` | Resumen del cierre por método de pago |
| POST | `/shift-closures/{id}/close` | Cerrar turno |
| POST | `/shift-closures/{id}/approve` | Aprobar cierre por encargado/admin |

Respuesta esperada de resumen:

```json
{
  "sales_cash_total": 3500,
  "sales_qr_total": 2800,
  "sales_card_total": 700,
  "sales_total": 7000,
  "waiter_commissions_total": 420,
  "girl_settlements_total": 1010,
  "expense_cash_total": 300,
  "expected_cash_amount": 2780,
  "cash_difference": 0
}
```

## Liquidación de chicas

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/shifts/{shiftId}/girl-settlements` | Ver liquidaciones de chicas del turno |
| GET | `/girl-settlements/{id}` | Ver detalle de una chica |
| POST | `/girl-settlements/{id}/pay` | Marcar pago a chica |

## Liquidación de garzones

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/shifts/{shiftId}/waiter-settlements` | Ver comisiones de garzones del turno |
| GET | `/waiter-settlements/{id}` | Ver detalle de un garzón |
| POST | `/waiter-settlements/{id}/pay` | Marcar pago a garzón |

## Servicios de pieza

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| POST | `/room-services` | Registrar pieza |
| GET | `/shifts/{shiftId}/room-services` | Listar piezas del turno |
| DELETE | `/room-services/{id}` | Anular pieza según permisos |

## Servicios de show

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| POST | `/show-services` | Registrar show |
| GET | `/shifts/{shiftId}/show-services` | Listar shows del turno |
| DELETE | `/show-services/{id}` | Anular show según permisos |

## Reglas de precios por producto

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/products/{id}/price-rules` | Ver precios solo/con chica |
| POST | `/products/{id}/price-rules` | Crear regla de precio |
| PUT | `/product-price-rules/{id}` | Actualizar regla de precio |

## Personal operativo

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET | `/staff` | Listar personal operativo |
| POST | `/staff` | Crear perfil operativo |
| PUT | `/staff/{id}/commission-percent` | Cambiar porcentaje de garzón |
