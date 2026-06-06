# BAR_MODULE_V1_DECISION.md
# Decisión: Módulo Barra en NightPOS V1

**Fecha:** 2026-06-06  
**Versión:** V1-91.1 Estabilización Pre-SSE  
**Estado:** DECISIÓN FIRME

---

## Decisión

**En V1 no habrá módulo Barra operativo.**

No se implementa pantalla de barra, vista de preparación ni flujo de estados de cocina/barra en V1.

---

## Flujo de comandas en V1

```
OPEN → SENT_TO_BAR → BILLED
              ↓
         CANCELLED (desde cualquier estado activo)
```

### Qué significa SENT_TO_BAR en V1

- La comanda fue enviada a barra por el garzón.
- Es **visible para la cajera** en su lista de "pendientes de cobro".
- Está **pendiente de cobro** — la cajera debe cobrarla.
- No pasa por `IN_PREPARATION` ni `READY` en el flujo normal.
- El garzón puede agregar extras (productos adicionales) mientras la comanda está en `SENT_TO_BAR`.

---

## Estados reservados para V2

| Estado | Descripción | Estado V1 |
|--------|-------------|-----------|
| `IN_PREPARATION` | Barra confirmó que está preparando la orden | **Reservado — no usar** |
| `READY` | Barra indica que la orden está lista para servir | **Reservado — no usar** |

Estos estados:
- **Existen en el dominio** (enum `OrderStatus`) para evitar migraciones en V2.
- **No se usan en el flujo operativo de V1**.
- **No se usan en SSE-2** de V1.
- **No generan eventos** en V1.
- **No aparecen en KPIs principales** (la UI solo los muestra si count > 0, lo cual nunca ocurre en V1).

---

## Decisiones de KPI en V1

| KPI | Qué cuenta | Visible en V1 |
|-----|-----------|---------------|
| Abiertas | `OPEN` | ✓ Siempre |
| En barra | `SENT_TO_BAR` | ✓ Siempre |
| Pendientes cobro (garzón) | `IN_PREPARATION + READY` | Solo si > 0 (siempre 0 en V1) |

**Nota**: En el cashier scope `pending_charge` incluye `SENT_TO_BAR + IN_PREPARATION + READY` para que la cajera vea todas las comandas que necesita cobrar. Esto es correcto y no cambia en V1.

---

## Cobrar comandas OPEN en V1

Se permite cobrar comandas `OPEN` directamente (sin enviar a barra primero), para flexibilidad operativa. En ese caso:

- Se muestra aviso en el modal de cobro: *"Esta comanda aún no fue enviada a barra. Puede cobrarla de todas formas."*
- No se bloquea el cobro.
- La comanda pasa directamente a `BILLED`.

Referencia: `ORDER_CHARGE_RULES_V1.md`

---

## Plan V2 — Módulo Barra

En V2, si se implementa el módulo Barra:

1. La pantalla de barra consume eventos SSE con tipo `ORDER_SENT_TO_BAR`.
2. El bartender puede actualizar el estado a `IN_PREPARATION` y luego `READY`.
3. Los garzones reciben notificación cuando la orden está `READY`.
4. El KPI "Pendientes cobro" del garzón mostrará comandas `READY`.
5. Se requiere migración de permisos para rol `BARMAN` o similar.

---

## Archivos relacionados

- `ORDER_CHARGE_RULES_V1.md` — Reglas de cobro de comandas en V1
- `NIGHTPOS_V1_DEVELOPMENT_MAP.md` — Mapa de desarrollo V1
- `backend/app/Application/Order/Support/OrderListScopeResolver.php` — Scopes operativos
- `backend/app/Domain/Order/ValueObjects/OrderStatus.php` — Enum de estados
