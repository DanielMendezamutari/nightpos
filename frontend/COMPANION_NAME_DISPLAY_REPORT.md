# Fase A — Nombre de acompañante visible (Frontend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Completado  
**Par backend:** `backend/COMPANION_NAME_DISPLAY_REPORT.md`  
**Auditoría origen:** `frontend/WAITER_TABLES_COMPANION_BRACELET_AUDIT.md` (Parte 2)

---

## Problema

Garzón y cajera veían «Con acompañante» sin el nombre de la chica en comanda, precuenta y tickets. Combos ya mostraban reparto multichica.

## Regla UI implementada

| Tipo | Visualización |
|------|---------------|
| **SOLO_CLIENTE** | Producto + «Solo cliente» |
| **CON_ACOMPANANTE simple** | Producto + «Con acompañante» + `Manilla: María` |
| **CON_ACOMPANANTE sin nombre** | `Manilla: Sin asignar` |
| **Combo** | Sin cambios — manillas + `María ×3` / `Laura ×2` |

---

## Helper compartido

`src/composables/useOrderHelpers.js`:

- `shouldShowCompanionBraceletLine(item)` — `CON_ACOMPANANTE` y `!requires_allocation`
- `formatCompanionBraceletLine(item)` — `Manilla: {girl_name}` o `Manilla: Sin asignar`

---

## Superficies actualizadas

| Componente / página | Cambio |
|---------------------|--------|
| `OrderItemsTable.vue` | Línea manilla en tabla y lista móvil |
| `waiter/orders/[id].vue` | Línea manilla bajo cada ítem |
| `orders/[id].vue` | Vía `OrderItemsTable` |
| `PrintableOrderTicket.vue` | Reemplaza «Chica asignada» genérico |
| `PrintablePrecheckTicket.vue` | Línea manilla en precuenta |
| `PrintableSaleTicket.vue` | Línea manilla en ticket venta |
| `SaleDetailDialog.vue` | Columna chica con `Manilla: …` |

---

## Build

`npm run build` — OK.

---

## QA manual sugerido

1. Garzón: Paceña CON_ACOMPANANTE + María → detalle muestra `Manilla: María`
2. Cajera: misma comanda → `OrderItemsTable` muestra manilla
3. Precuenta / ticket comanda / ticket venta → `Manilla: María`
4. Combo 6 cervezas → reparto multichica sin regresión
5. SOLO_CLIENTE → sin línea manilla

---

## Pendiente (Fases B–D)

- Home garzón «Mis mesas» (grid LIBRE/OCUPADA)
- Config admin mesas + asignación garzones
- Unificar copy manillas en liquidaciones
- Mover manillas manuales a Configuración / Avanzado
