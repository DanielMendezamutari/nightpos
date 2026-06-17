# Fix — Liquidaciones parciales tras flujo Mis mesas

**Fecha:** 2026-06-16  
**Basado en:** `PARTIAL_SETTLEMENTS_AFTER_TABLES_DIAGNOSTIC.md`, `PARTIAL_SETTLEMENTS_AUDIT.md`

---

## Resumen

| Hallazgo | Resultado |
|----------|-----------|
| Migración `2026_06_16_100010` en entorno dev/test | ✅ Aplicada |
| Unique `(official_shift_id, staff_user_id, settlement_type)` | ✅ Eliminado |
| Lógica `ensureSettlement` (solo reutiliza PENDING) | ✅ Correcta en código |
| Flujo Mis mesas → cobro → generate → pay → 2.º corte | ✅ **Pasa tests** |
| Mis mesas como causa directa | ❌ **Descartada** |
| Cambio backend de negocio | **No requerido** en dev |

---

## Causa más probable en producción

1. **Migración pendiente** — sin `2026_06_16_100010`, el 2.º `staff_settlements` INSERT falla o no crea Corte #2 (bug María original).
2. **UI desactualizada** — tab Chicas/Garzones sin SSE (corregido en frontend).
3. **Scope cajera** — `empty_overview` si caja en turno anterior (ver diagnóstico; no es bug de generate).

---

## Acciones realizadas

### 1. Confirmación migración

```bash
php artisan migrate:status
# 2026_06_16_100010_allow_multiple_settlements_per_shift_staff ... Ran
```

Test: `allows multiple settlement headers per shift staff type after migration` — unique antiguo ausente.

### 2. Tests de regresión (nuevo archivo)

`tests/Feature/Api/V1/PartialSettlementsAfterTablesTest.php`

| Escenario | Estado |
|-----------|--------|
| Misma chica, Corte #2 vía `POST /waiter/my-tables/{id}/open` | ✅ |
| Otra chica tras PAID | ✅ |
| Combo `GIRL_BRACELET_ALLOCATION` tras PAID | ✅ |
| SOLO_CLIENTE sin liquidación chica | ✅ |
| Sin duplicar `staff_settlement_items` | ✅ |
| Admin + cajera ven Corte #2 en API | ✅ |
| `sales.official_shift_id` alineado con turno OPEN | ✅ |

Suite legacy: `PartialSettlementsTest` — 10/10 OK.

### 3. Backend código

**Sin cambios** — el pipeline ya era correcto post-migración.

---

## Producción — checklist

```bash
php artisan migrate:status | findstr 100010
# Si Pending:
php artisan migrate
```

Verificar índice:

```sql
SHOW INDEX FROM staff_settlements;
-- NO debe existir: staff_settlements_shift_staff_type_unique
```

Tras 2.º cobro + generate, revisar respuesta:

```json
POST /api/v1/settlements/generate-current-shift
{ "created_items": 1, ... }
```

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Migración no corrida en prod | Checklist arriba |
| Cajera ve vacío tras rotación turno | Admin genera/ve; revisar scope (doc diagnóstico) |

---

## Archivos tocados

- `tests/Feature/Api/V1/PartialSettlementsAfterTablesTest.php` (nuevo)
- Documentación (este archivo + diagnósticos actualizados)

**No tocado:** Mis mesas, CBA, liquidaciones core, ChargeOrderUseCase.
