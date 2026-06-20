# Precuenta local garzón — Backend

**Fecha:** Jun 2026  
**Estado:** Implementado

---

## Endpoint

```
POST /api/v1/orders/{id}/precheck/print
```

**Permiso:** `orders.access` (mismo grupo que `GET /orders/{id}/precheck`)

**Respuesta 201:**

```json
{
  "message": "Precuenta enviada a impresora.",
  "data": {
    "job": {
      "id": 1,
      "type": "PRECHECK",
      "status": "PENDING",
      "source_id": 42,
      "requested_by_user_id": 2,
      "content_text": "..."
    }
  }
}
```

**422:** comanda cobrada/cancelada, sin ítems, o sin impresora activa en sucursal.

**404:** comanda de otra sucursal.

---

## Use cases

| Clase | Rol |
|-------|-----|
| `PrintOrderPrecheckUseCase` | Valida tenant/branch/orden, acceso garzón, estado |
| `CreatePrecheckPrintJobUseCase` | Crea `print_job` PENDING tipo `PRECHECK` |

---

## Contenido ticket (`PrintTicketContentBuilder::buildPrecheck`)

- Nombre sucursal
- Número comanda, mesa, salón, garzón, fecha/hora
- Productos: cantidad, modalidad, manilla/chica, distribución combo
- Total
- Leyenda: **PRECUENTA — NO ES COMPROBANTE FISCAL**

---

## Reglas operativas

| Tipo | Disparo | Uso |
|------|---------|-----|
| `ORDER_COMMAND` | Auto al enviar a barra | Interno barra |
| `PRECHECK` | Manual garzón (`precheck/print`) | Cliente antes de cobrar |

No se imprime precuenta automáticamente.

---

## Tests

`tests/Feature/Api/V1/WaiterPrecheckPrintTest.php` — **8 tests**

1. Garzón crea job PRECHECK  
2. Content incluye TOTAL  
3. Content incluye Manilla CON_ACOMPANANTE  
4. Content incluye distribución combo  
5. Rechaza otra sucursal (404)  
6. Rechaza comanda cancelada  
7. Job queda PENDING  
8. `requested_by_user_id` = usuario autenticado  

---

## Sin cambios

- `SendOrderToBarUseCase`
- Liquidaciones, caja, contratos de ítems
