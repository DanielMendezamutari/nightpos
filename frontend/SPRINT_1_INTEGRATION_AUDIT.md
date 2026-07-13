# Auditoría de integración — Sprint 1 taxonomía financiera (Frontend)

**Fecha:** 2026-07-10
**Alcance:** adopción real de `movement_family` y `movement_category` en frontend post Sprint 1
**Fuentes oficiales:**

- `FINANCIAL_ARCHITECTURE_MASTER.md`
- `FINANCIAL_IMPLEMENTATION_BLUEPRINT.md`
- `frontend/SPRINT_1_TAXONOMIA_FINANCIERA_IMPLEMENTATION_REPORT.md`

## 1. Resumen ejecutivo

El frontend post Sprint 1 está en estado de **compatibilidad mínima exitosa**, no de adopción integral.

Qué significa eso:

- la taxonomía nueva ya puede viajar por la API y mostrarse en tablas de movimientos;
- la UI principal de Caja, fiscalización y reportes todavía sigue pensada alrededor del summary legado (`financial_summary`, `total_manual_income`, `total_manual_expense`, `expected_cash`, etc.);
- impresión y cierres browser siguen leyendo etiquetas y estructuras antiguas.

Conclusión técnica frontend:

- **la base para migrar ya existe**;
- **el dashboard, los reportes y la impresión todavía no usan la nueva taxonomía como modelo principal**.

## 2. ¿Qué módulos frontend ya usan `movement_family` y `movement_category`?

## 2.1 Constantes y labels nuevos

Integrados:

- `frontend/src/constants/cashMovements.js`

Expone:

- `CASH_MOVEMENT_FAMILY_LABELS`
- `CASH_MOVEMENT_CATEGORY_LABELS`
- `cashMovementFamilyLabel()`
- `cashMovementCategoryLabel()`

Esto resuelve correctamente el requisito de no mostrar el slug técnico crudo si no existe traducción.

## 2.2 Pantallas que ya muestran taxonomía nueva

### Caja operativa

- `frontend/src/pages/nightpos/cash/index.vue`

Ya muestra columnas:

- `Tipo`
- `Familia`
- `Categoría`

Y usa:

- `cashMovementFamilyLabel(item.movement_family)`
- `cashMovementCategoryLabel(item.movement_category, item.movement_type)`

### Detalle administrativo de caja

- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue`

También muestra:

- `Tipo`
- `Familia`
- `Categoría`

con labels legibles.

## 3. ¿Qué módulos frontend todavía usan lógica antigua?

## 3.1 Caja operativa principal

- `frontend/src/pages/nightpos/cash/index.vue`

Aunque ya muestra family/category en la tabla, el resto de la pantalla sigue usando:

- `session.financial_summary`
- `total_manual_income`
- `total_manual_expense`
- `expected_cash`
- `expected_by_method`

Es decir:

- la tabla de movimientos ya conoce la taxonomía nueva,
- el dashboard como tal sigue viviendo en la arquitectura anterior.

## 3.2 Detalle admin de caja

- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue`

Mismo patrón:

- movimientos con taxonomía nueva en tabla,
- summary superior todavía heredado.

## 3.3 Componentes de movimiento manual

- `frontend/src/components/nightpos/cash/CashMovementDialog.vue`

Sigue operando con:

- `movement_type`
- filtro por `reason.type`
- `cash_movement_reason_id`

No usa todavía `movement_family` ni `movement_category` como parte del flujo de creación o revisión.

Esto es correcto para Sprint 1, pero confirma que el frontend aún no migró a taxonomía estructurada como primer concepto visible.

## 3.4 Impresión browser

### Movimiento individual

- `frontend/src/components/nightpos/print/PrintableCashMovementTicket.vue`

Sigue mostrando:

- `movement_type`
- `reason_name || description`

No muestra family/category.

### Cierre / arqueo browser

- `frontend/src/components/nightpos/print/PrintableCashSessionReport.vue`

Sigue construyendo texto con:

- `mov.movement_type === 'INCOME' ? 'Ingreso' : 'Egreso'`
- `mov.reason || 'Movimiento'`

No usa family/category para agrupar o rotular.

## 3.5 Fiscalización admin resumida

Todavía no muestran taxonomía nueva como summary principal:

- `frontend/src/pages/nightpos/finance/cash-sessions/index.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/summary.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/history.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-cashier.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-shift.vue`

Todos siguen leyendo agregados legacy del backend.

## 3.6 Reportes y dashboard financiero

Todavía no adoptan taxonomía nueva:

- `frontend/src/pages/nightpos/finance/reports/index.vue`
- `frontend/src/pages/nightpos/finance/reports/managerial-daily.vue`
- cualquier componente que dependa del `reports/cash`, `reports/daily` o del cierre de turno heredado.

## 4. ¿Qué endpoints frontend consumidos ya traen taxonomía y cuáles no se usan aún?

## 4.1 Ya traen taxonomía y frontend la aprovecha parcialmente

- `GET /cash/session/current`
- `GET /admin/cash-sessions/{id}`
- `POST /cash/movements`

## 4.2 Aún no existe consumo financiero nuevo por dominio

Frontend todavía no consume:

- `sales_summary`
- `cash_summary`
- `movement_summary`
- `settlement_summary`
- `scope_summary`
- `managerial_summary`

porque Sprint 1 no los implementó todavía.

## 5. ¿Qué dashboards todavía no aprovechan la nueva taxonomía?

## 5.1 Dashboard de Caja

- `frontend/src/pages/nightpos/cash/index.vue`

Estado:

- movimientos: sí
- KPI y resumen general: no

## 5.2 Fiscalización admin

- `frontend/src/pages/nightpos/finance/cash-sessions/index.vue`
- `summary.vue`
- `by-cashier.vue`
- `by-shift.vue`

Estado:

- usan totales y expected cash legacy
- no explotan categorías nuevas

## 5.3 Reporte gerencial

- `frontend/src/pages/nightpos/finance/reports/managerial-daily.vue`

Estado:

- no usa taxonomía nueva como fuente principal;
- sigue dependiendo de payloads gerenciales heredados.

## 5.4 Reportes operativos

- `frontend/src/pages/nightpos/finance/reports/index.vue`

Estado:

- sin consumo explícito de `movement_family` o `movement_category`;
- sigue agrupando con reportes legacy del backend.

## 6. ¿Qué impresión frontend sigue leyendo datos legacy?

Principalmente:

- `PrintableCashMovementTicket.vue`
- `PrintableCashSessionReport.vue`

Ambos siguen expresando:

- ingreso / egreso por `movement_type`
- motivo por `reason_name` o `description`

No muestran todavía:

- family
- category
- clasificación financiera oficial

## 7. ¿Qué código frontend quedó duplicado o híbrido?

## 7.1 Doble lenguaje de movimiento

Hoy conviven dos maneras de representar el movimiento:

1. lenguaje viejo:
- `movement_type`
- `reason_name`
- `description`

2. lenguaje nuevo:
- `movement_family`
- `movement_category`

Eso es normal en transición, pero confirma un estado híbrido.

## 7.2 Dos niveles de resumen financiero

La UI sigue leyendo:

- `financial_summary`
- totales legacy (`income_total`, `expense_total`, `expected_cash`)

mientras en los movimientos ya existe taxonomía nueva.

## 7.3 Labels nuevos sin adopción global

`cashMovements.js` ya existe, pero solo se usa en dos pantallas.

## 8. ¿Qué componentes Vue siguen usando lógica antigua?

Componentes claramente legacy o híbridos:

- `frontend/src/components/nightpos/cash/CashMovementDialog.vue`
- `frontend/src/components/nightpos/print/PrintableCashMovementTicket.vue`
- `frontend/src/components/nightpos/print/PrintableCashSessionReport.vue`
- `frontend/src/pages/nightpos/cash/index.vue` (dashboard, no tabla)
- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue` (summary superior)
- vistas admin agrupadas de caja
- reportes de finanzas

## 9. ¿Qué riesgos existen si iniciamos Sprint 2 sin corregir estas dependencias?

1. **UI nueva sobre summary viejo**
- si Sprint 2 toca dashboard sin cambiar summaries primero, el frontend va a seguir mostrando KPIs heredados aunque los movimientos ya estén bien clasificados.

2. **Impresión inconsistente**
- el usuario puede ver taxonomía en tablas pero seguir recibiendo tickets con lógica vieja.

3. **Fiscalización admin parcial**
- el detalle admin ya muestra categorías nuevas, pero los agregados superiores no. Eso puede volver la lectura más confusa, no menos.

4. **Doble terminología en UX**
- “Ingreso/Egreso” seguirá coexistiendo con “Familia/Categoría” sin una narrativa financiera unificada.

## 10. ¿Qué porcentaje de adopción frontend tiene hoy la nueva arquitectura financiera?

Estimación razonada:

### Consumo de payload con taxonomía nueva
- **50–60%**

### Render visible en UI
- **20–25%**

### Uso de taxonomía para resúmenes y decisiones visuales
- **5–10%**

### Adopción frontend global consolidada
- **20% aprox.**

Lectura correcta:

- el frontend ya es compatible,
- pero todavía no es un frontend guiado por la nueva arquitectura financiera.

## 11. Mapa frontend de dependencias

```mermaid
flowchart TD
  A[movement_family / movement_category desde API] --> B[cashMovements.js labels]
  A --> C[pages/nightpos/cash/index.vue tabla de movimientos]
  A --> D[pages/nightpos/finance/cash-sessions/[id].vue tabla de movimientos]
  C --> E[UI operativa caja]
  D --> F[UI fiscalizacion detalle]
  A --> G[Printable payloads backend]
  G --> H[PrintableCashMovementTicket.vue]
  G --> I[PrintableCashSessionReport.vue]
  J[financial_summary legacy] --> E
  J --> F
  J --> K[cash-sessions/index.vue]
  J --> L[cash-sessions/summary.vue]
  J --> M[cash-sessions/by-cashier.vue]
  J --> N[cash-sessions/by-shift.vue]
  O[reports legacy] --> P[reports/index.vue]
  O --> Q[managerial-daily.vue]
```

## 12. ¿Qué puede eliminarse al final de la migración desde el lado frontend?

No ahora, pero cuando termine la migración podrán simplificarse:

- múltiples labels basadas solo en `movement_type`
- renderizado legacy en tickets de movimiento/cierre
- dependencia visual de `financial_summary` en caja y fiscalización
- columnas o textos que mezclan manual income / manual expense con conceptos ya redefinidos

## 13. Conclusión final

Frontend quedó correctamente preparado para convivir con la taxonomía nueva, pero todavía no ha migrado de verdad al nuevo modelo financiero.

Eso es aceptable para Sprint 1.

No sería aceptable para Sprint 2 saltar directo al rediseño visual sin antes migrar summaries y builders backend. El orden correcto sigue siendo:

1. summaries backend por dominio;
2. adopción frontend de esos summaries;
3. recién después rediseño del dashboard.
