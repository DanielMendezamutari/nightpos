# PHASE_16_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 16 — Liquidaciones reales de garzones y chicas  
**Fecha:** 2026-06-02  
**Referencias:** Fases 13–15 (turnos, ventas, manillas, piezas, shows)

> **Actualización 2026-06-08:** Ver `ROOM_SERVICE_PRICING_MODEL_FIX_REPORT.md` — habitación = recurso físico; montos en `room_services` con `girl_percent` y snapshots `girl_amount` / `house_amount`.

> **Actualización 2026-06-08 (caja):** Ver `SERVICES_CASH_ACCOUNTING_FIX_REPORT.md` — manilla, pieza y show requieren caja abierta del operador, `payment_method` obligatorio y movimiento `INCOME` en `cash_movements` con `source_type` / `source_id`.

> **Nota:** Piezas y shows no pertenecen al flujo de garzones. Son servicios directos liquidados solo a la chica (`GIRL_ROOM` / `GIRL_SHOW`). La comisión de garzón (`WAITER_COMMISSION`) proviene únicamente de ventas cobradas.

---

## 1. Tablas creadas / ampliadas

### `staff_settlements` (Fase 14, sin cambios estructurales)

Liquidación agregada por turno oficial y personal (`staff_user_id` + `settlement_type`).

### `staff_settlement_items` (Fase 14 + Fase 16)

| Campo nuevo / uso | Descripción |
| ----------------- | ----------- |
| `source_id` | ID de manilla, pieza o show (nullable para ventas) |
| Índice único `(source_id, source_type)` | Evita duplicar manillas/piezas/shows al regenerar |
| Índice único `(sale_item_id, source_type)` | Evita duplicar líneas de venta |

**Migración Fase 16:** `2026_06_03_100019_add_source_id_to_staff_settlement_items.php`

---

## 2. Endpoints

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/settlements/current-shift` | `settlements.access` |
| POST | `/api/v1/settlements/generate-current-shift` | `settlements.generate` |
| GET | `/api/v1/settlements/{id}` | `settlements.access` |
| POST | `/api/v1/settlements/{id}/mark-paid` | `settlements.pay` |
| GET | `/api/v1/settlements/history` | `settlements.history` |

**Filtros historial:** `official_shift_id`, `staff_user_id`, `settlement_type`, `status`, `date_from`, `date_to`, `limit`.

---

## 3. Reglas implementadas

1. Generación por turno oficial (abierto o último con liquidaciones / último turno para consulta).
2. No duplica ítems: ventas por `(sale_item_id, source_type)`; servicios por `(source_id, source_type)`.
3. Si existe liquidación `PENDING` del turno, agrega solo ítems nuevos y recalcula total.
4. Si está `PAID`, no se modifica al regenerar.
5. No recalcula ventas históricas: usa snapshots en `sale_items`.
6. Manillas, piezas y shows usan `total_amount` registrado en su tabla.
7. Alcance `tenant_id`, `branch_id`, `official_shift_id`.
8. `SettlementAccessPolicy::scopedStaffUserId()`: admin/cajera con `generate`/`pay`/`history` ven todo; garzón/chica solo lo propio.
9. Liquidación visible con turno cerrado (`resolveOverviewShiftId`).
10. Superadmin con contexto operativo: mismos permisos según rol efectivo.

---

## 4. Cálculo garzones

Fuente: `sale_items` de ventas cobradas del turno con `waiter_commission_amount_snapshot > 0`.

| Campo ítem | Valor |
| ---------- | ----- |
| `source_type` | `WAITER_COMMISSION` |
| `base_amount` | Total línea |
| `percent` | `waiter_commission_percent_snapshot` |
| `amount` | `waiter_commission_amount_snapshot` |
| Agrupación | `sales.waiter_user_id` → `settlement_type = WAITER` |

---

## 5. Cálculo chicas

| Fuente | `source_type` | Monto |
| ------ | ------------- | ----- |
| Venta `CON_ACOMPANANTE` con `girl_user_id` | `GIRL_CONSUMPTION` | `girl_amount_snapshot` |
| Tabla `bracelets` | `GIRL_BRACELET` | `total_amount` |
| Tabla `room_services` | `GIRL_ROOM` | `girl_amount` snapshot (calculado desde `girl_percent` al registrar; casa: `house_amount`, no liquida personal) |
| Tabla `shows` | `GIRL_SHOW` | `total_amount` |

Agrupación por `girl_user_id` → `settlement_type = GIRL`.

**Nota:** Los consumos con acompañante ya no usan `GIRL_BRACELET` en ventas (Fase 14); ese tipo queda para manillas manuales.

---

## 6. Integración manillas / piezas / shows

- Repositorio carga registros del turno desde `bracelets`, `room_services`, `shows`.
- Cada registro genera un ítem con `source_id` = PK del servicio.
- `registered_at` del servicio se expone en detalle de liquidación.
- Resumen turno: `total_bracelets`, `total_pieces`, `total_shows`, `total_consumption` (suma `GIRL_CONSUMPTION`).

---

## 7. Permisos

| Slug | Admin | Cajera | Garzón | Chica |
| ---- | ----- | ------ | ------ | ----- |
| `settlements.access` | ✓ | ✓ | ✓ (propia) | ✓ (propia) |
| `settlements.generate` | ✓ | ✓ | — | — |
| `settlements.pay` | ✓ | ✓ | — | — |
| `settlements.history` | ✓ | ✓ | — | — |

Seeder: `NightPosSeeder.php`.

---

## 8. Tests

`tests/Feature/Api/V1/SettlementsPhase16Test.php` (9 casos) + `SettlementsPhase14Test.php` actualizado.

Cubre: garzón, consumo, manilla, pieza, show, mezcla completa, no duplicar, no tocar `PAID`, marcar pagado, scope garzón/chica, turno cerrado, filtros historial, resumen por concepto.

**Suite completa:** 121 tests OK.

---

## 9. Validación manual (dev)

1. Login `admin.demo` / `AdminDemo123!`
2. Abrir caja (PIN `1234`)
3. Comanda `CON_ACOMPANANTE`, cobrar
4. Registrar manilla, pieza y show (chica `chica.centro` PIN `9012`)
5. Finanzas → Liquidaciones → Generar
6. Revisar Garzones / Chicas / Historial
7. Marcar pagado (dialog confirmación)
8. Consola sin errores críticos

---

## 10. Próxima fase recomendada

- **Fase 17 — Reportes financieros consolidados:** export PDF/Excel de liquidaciones por turno, cruce caja vs comisiones vs chicas, y dashboard gerencial.
- Opcional: selector de personal en historial (API list staff) y notificaciones al personal cuando hay liquidación pendiente.
