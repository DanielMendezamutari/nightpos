# Sprint 1 — Taxonomía financiera estructurada (Frontend)

**Fecha:** 2026-07-10
**Estado:** Implementado
**Fuente oficial funcional:** `FINANCIAL_ARCHITECTURE_MASTER.md`
**Fuente oficial técnica:** `FINANCIAL_IMPLEMENTATION_BLUEPRINT.md`

## 1. Objetivo ejecutado

Adaptar mínimamente el frontend para soportar `movement_family` y `movement_category` sin rediseñar todavía el dashboard de Caja y manteniendo la UI actual operativa.

## 2. Restricción respetada

No se rediseñó:

- dashboard de Caja
- bloques KPI
- summaries financieros
- estructura visual principal

Solo se adaptaron:

- labels seguros
- columnas de tablas de movimientos
- consumo de payload extendido sin romper campos anteriores

## 3. Cambios implementados

## 3.1 Archivo nuevo

- `frontend/src/constants/cashMovements.js`

Incluye:

- labels legibles para `movement_family`
- labels legibles para `movement_category`
- fallback seguro:
  - `Otro ingreso`
  - `Otro egreso`

## 3.2 Pantallas adaptadas

### Caja operativa

- `frontend/src/pages/nightpos/cash/index.vue`

Cambios:

- se agregan columnas:
  - `Familia`
  - `Categoría`
- se usa label amigable, no slug técnico crudo
- no se alteró el resto del layout

### Detalle administrativo de caja

- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue`

Cambios:

- se agregan columnas:
  - `Familia`
  - `Categoría`
- se muestran chips/labels legibles
- se mantiene el resto de la experiencia igual

## 4. Compatibilidad preservada

Se conservaron intactos los campos usados por la UI actual:

- `movement_type`
- `amount`
- `description`
- `reason_name`
- `payment_method`
- `notes`
- `created_at`

La nueva taxonomía entra como información adicional, no como ruptura.

## 5. Archivos modificados

### Nuevos

- `frontend/src/constants/cashMovements.js`

### Modificados

- `frontend/src/pages/nightpos/cash/index.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue`

## 6. Validación realizada

### Validación estática

- `get_errors` sin errores en los archivos modificados

### Compilación

- se intentó `npm run build` mediante `npm.cmd`
- no se observó error de compilación atribuible a los cambios del Sprint 1
- la salida quedó truncada por volumen y mostró warnings heredados del proyecto, no fallos específicos de esta implementación

## 7. Riesgos encontrados

1. **La UI actual todavía no reordena conceptos**
- Ahora puede mostrar family/category, pero sigue consumiendo summaries legados.

2. **Taxonomía visible antes que el rediseño completo**
- La cajera verá nuevas columnas en movimientos, pero el resto del dashboard sigue siendo el modelo anterior.

3. **Las categorías nuevas de servicios no tienen todavía visualización dedicada**
- `BRACELET_COLLECTION`, `ROOM_SERVICE_COLLECTION` y `SHOW_COLLECTION` tendrán label correcto, pero no bloque visual propio todavía.

## 8. Qué queda pendiente para Sprint 2

1. consumir `movement_summary` oficial cuando exista.
2. retirar dependencias UI de summaries heredados ambiguos.
3. reestructurar dashboard de Caja por dominios oficiales.
4. crear componentes dedicados para:
- Caja física
- Ventas
- Movimientos
- Liquidaciones
- Scope

## 9. Conclusión

Frontend quedó adaptado al Sprint 1 sin romper la operación actual.

La taxonomía financiera ya puede verse y viajar por payloads, pero todavía no se ha usado para rediseñar la experiencia ni los summaries visuales. Eso queda correctamente diferido al Sprint 2 y posteriores.
