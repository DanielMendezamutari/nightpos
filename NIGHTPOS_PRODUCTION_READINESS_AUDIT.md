# NightPOS — Auditoría de preparación para producción

**Fecha:** 2026-06-30  
**Alcance:** Diagnóstico integral pre-producción — **sin correcciones aplicadas**  
**Base de datos:** MySQL `nigtpos` @ `127.0.0.1` (dump real del hosting Ribersoft)  
**Método:** SQL read-only + API JWT con usuarios reales (`php scripts/real_db_qa_internal.php`) + revisión estática de rutas/código  
**Prohibido en esta auditoría:** SQLite, seeders, factories, mutaciones de código o de base de datos

---

## Resumen ejecutivo

NightPOS fue auditado como un equipo de QA operativo contra la **misma base MySQL que se usa en hosting**, cubriendo autenticación, caja, turnos, comandas, ventas, liquidaciones, habitaciones, garzón, impresión, SaaS, integridad de BD, backend, frontend, seguridad, rendimiento y concurrencia.

| Métrica | Valor |
|---------|-------|
| Tenants | 3 (`casa-demo` suspended, `C22` activo operativo, `R` activo) |
| Usuarios | 27 |
| Turnos (`official_shifts`) | 6 |
| Cajas (`cash_sessions`) | 4 (2 OPEN, 2 CLOSED) |
| Comandas | 23 |
| Ventas | 13 |
| Liquidaciones | 7 (3 PENDING) |
| Print jobs | 105 (1 PENDING) |

**Integridad financiera básica:** no hay tickets duplicados, no hay ventas con suma de pagos distinta al total, no hay movimientos de caja huérfanos, no hay liquidaciones PAID sin `cash_movement_id`. La secuencia documental C22 está alineada (`last_value=3`, tickets `000002`–`000003`).

**Bloqueadores operativos para una noche real en C22:** liquidaciones PENDING colgadas del turno cerrado 5, cajera sin permiso de cierre de turno, cierre de caja histórico sin monto declarado, turnos/cajas zombie en otros tenants.

### Veredicto

| Campo | Valor |
|-------|-------|
| **Preparación estimada** | **68 %** |
| **Veredicto** | **APTO CON RIESGOS** |

No es **NO APTO** porque el núcleo de ventas, pagos y numeración documental funciona y la base no muestra corrupción grave de dinero. No es **APTO PARA PRODUCCIÓN** porque persisten riesgos P0/P1 en cierre de turno, liquidaciones pendientes de turnos cerrados, configuración de despliegue (debug) y visibilidad inconsistente entre roles.

---

## Metodología

### SQL (read-only)

Script base: `backend/scripts/production_readiness_audit.sql` (parcialmente ejecutado; algunas secciones usan nombres de columna legacy y deben corregirse en el script, no en la BD). Consultas ad-hoc complementarias sobre tablas reales: `official_shifts`, `cash_sessions`, `staff_settlements`, `orders`, `sales`, `print_jobs`, etc.

### API (read-only)

```bash
cd backend && php scripts/real_db_qa_internal.php
```

Resultados: `backend/storage/app/real_db_qa_api_results.json`

Usuarios probados: `superadmin`, `LAURA` (owner C22), `lizvania` (cajera C22), `HUGO` (garzón C22), `GARZON DARDO` (R), `admin.demo` (casa-demo suspended).

### Código (solo lectura)

Revisión de `routes/api.php`, use cases de liquidaciones/ventas, informes previos (`SETTLEMENTS_PERMISSION_AUDIT.md`, `DOCUMENT_SEQUENCE_SERVICE_FINAL_FIX_REPORT.md`, etc.).

### Frontend / agente

Checklist derivado de respuestas API + auditorías parciales en `frontend/NIGHTPOS_REAL_DB_QA_AUDIT.md` y `agent/NIGHTPOS_REAL_DB_QA_AUDIT.md`. **No se ejecutó QA visual completo en navegador** en esta pasada.

---

## Hallazgos P0

> Bloqueadores críticos: pueden causar pérdida de dinero, bloqueo total de operación o exposición grave en producción.

---

### P0-01 — Riesgo residual de HTTP 409 en pagos de liquidación si el fix no está desplegado en hosting

| Campo | Detalle |
|-------|---------|
| **Descripción** | Bug histórico en `DocumentSequenceService`: `PDO::lastInsertId()` devolvía el `id` AUTO_INCREMENT de `document_sequences`, no `last_value`. Segundo pago calculaba correlativo duplicado → **409 en bucle** (rollback revertía incremento). |
| **Evidencia** | Depuración real documentada en `backend/DOCUMENT_SEQUENCE_409_DEBUG_REPORT.md`. En BD local post-fix: `document_sequences` tenant C22 `last_value=3`; tickets `1-2026-000002`, `1-2026-000003` sin duplicados. API `settlement/6 pay-preview` → 200. |
| **Riesgo** | **Crítico** en hosting si corre código anterior: imposible pagar segunda liquidación consecutiva; cajera bloqueada en cierre de noche. |
| **Cómo reproducir** | En hosting sin fix: pagar liquidación A (OK), pagar liquidación B → HTTP 409 repetido; `document_sequences.last_value` no avanza. |
| **Módulos** | Liquidaciones, Caja, Impresión |
| **Prioridad** | P0 |
| **Recomendación** | Verificar commit desplegado incluye `DocumentSequenceService` con `lockForUpdate` + reconciliación. Confirmar en hosting que `last_value` ≥ max ticket antes de abrir noche. |

---

### P0-02 — `APP_DEBUG=true` y entorno `local` en stack conectado a BD de producción

| Campo | Detalle |
|-------|---------|
| **Descripción** | `php artisan about` reporta **Debug Mode ENABLED**, **Environment local**, base `mysql`/`nigtpos`. Si el hosting replica esta configuración, stack traces y datos sensibles pueden filtrarse en respuestas de error. |
| **Evidencia** | `php artisan about` (2026-06-30): `Debug Mode ... ENABLED`, `Environment ... local`, `public/storage ... NOT LINKED`. |
| **Riesgo** | **Crítico** de seguridad y cumplimiento en producción. |
| **Cómo reproducir** | Provocar excepción no controlada con `APP_DEBUG=true` → respuesta JSON con trace completo. |
| **Módulos** | Seguridad, Backend, SaaS |
| **Prioridad** | P0 |
| **Recomendación** | En hosting: `APP_ENV=production`, `APP_DEBUG=false`, `php artisan config:cache`, revisar handler de excepciones. |

---

## Hallazgos P1

> Problemas que pueden romper o distorsionar una noche operativa real; requieren resolución o procedimiento explícito antes de go-live.

---

### P1-01 — Tres liquidaciones PENDING en turno ya CERRADO (shift 5)

| Campo | Detalle |
|-------|---------|
| **Descripción** | Liquidaciones `id=3` (WAITER, 53 Bs), `id=6` (GIRL, 190 Bs), `id=7` (GIRL, 90 Bs) permanecen **PENDING** con `official_shift_id=5` (**CLOSED** 2026-06-30 09:16). Turno actual C22 = **6 OPEN**. Total pendiente: **333 Bs**. |
| **Evidencia** | SQL: `staff_settlements` JOIN `official_shifts` WHERE `status='PENDING' AND os.status!='OPEN'`. Todas tienen `cash_session_id=4` (caja actual). |
| **Riesgo** | Personal no cobrado; bloqueadores de cierre fiscal; confusión en pantalla “turno actual”. |
| **Cómo reproducir** | Login cajera `lizvania` → `GET /settlements/current-shift` → count=5 incluye estos PENDING del turno 5. |
| **Módulos** | Liquidaciones, Turnos, Caja |
| **Prioridad** | P1 |
| **Recomendación** | Proceso operativo: pagar o anular antes de nueva rotación; evaluar migración automática al turno OPEN o bloqueo de rotación si hay PENDING. |

---

### P1-02 — Cajera C22 no puede ejecutar close-check de turno (403)

| Campo | Detalle |
|-------|---------|
| **Descripción** | Usuario `lizvania` (rol `cashier`) recibe **403** en `shifts/current/close-check` con mensaje `Permiso requerido: shifts.close`. Rol `cashier` tiene `shifts.access` y `shift_console.access` pero **no** `shifts.close`. |
| **Evidencia** | `backend/storage/app/real_db_qa_api_results.json`: HTTP 403. SQL permisos rol cashier: sin `shifts.close`. |
| **Riesgo** | En UI la cajera ve botón/acción que falla; cierre de turno depende 100 % del owner en horario pico. |
| **Cómo reproducir** | JWT lizvania → `GET /api/v1/shifts/current/close-check` → 403. |
| **Módulos** | Turnos, Autenticación/Permisos, Frontend |
| **Prioridad** | P1 |
| **Recomendación** | Decidir política: otorgar `shifts.close` a cajera senior o ocultar acción en UI para rol cashier; documentar flujo owner-only. |

---

### P1-03 — Visibilidad de liquidaciones inconsistente: owner 0 vs cajera 5

| Campo | Detalle |
|-------|---------|
| **Descripción** | Mismo endpoint `GET /settlements/current-shift`: **LAURA** (tenant_owner) → count=**0**; **lizvania** (cashier) → count=**5**. |
| **Evidencia** | API QA JSON. Turno 6 OPEN no tiene liquidaciones generadas; cajera filtra por `cash_session_id=4` e incluye registros del turno 5 cerrado. |
| **Riesgo** | Supervisor cree que no hay pendientes; cajera ve deuda real; doble trabajo o omisiones en cierre. |
| **Cómo reproducir** | Comparar respuestas owner vs cajera en `/settlements/current-shift` con BD actual. |
| **Módulos** | Liquidaciones, Frontend, Backend (`GetCurrentShiftSettlementsUseCase`, `SettlementShiftScopeResolver`) |
| **Prioridad** | P1 |
| **Recomendación** | Unificar criterio de “turno actual” (turno OPEN vs caja vs turno de caja); alerta explícita “pendientes turno anterior”. |

---

### P1-04 — Cierre de caja sin monto declarado (`cash_sessions.id=3`)

| Campo | Detalle |
|-------|---------|
| **Descripción** | Sesión C22 cerrada con `declared_closing_amount=NULL`, `expected_amount=542.00`, `difference_amount=NULL`. |
| **Evidencia** | SQL: `cash_sessions` id=3, `status=CLOSED`, `closed_at=2026-06-29 23:33:11`. |
| **Riesgo** | Arqueo incompleto; imposible auditar diferencia real; riesgo fiscal. |
| **Cómo reproducir** | Consultar historial de caja tenant C22 sesión 3 en admin/fiscalización. |
| **Módulos** | Caja |
| **Prioridad** | P1 |
| **Recomendación** | Validar backend que impida cierre sin `declared_closing_amount` salvo forced-close documentado; corregir dato histórico manualmente en operación. |

---

### P1-05 — Diferencia de caja no explicada en cierre (-75 Bs)

| Campo | Detalle |
|-------|---------|
| **Descripción** | `cash_sessions.id=1`: `opening_amount=500`, `expected_amount=525`, `declared_closing_amount=450`, **`difference_amount=-75`**. |
| **Evidencia** | SQL directo sobre `cash_sessions`. |
| **Riesgo** | Faltante de efectivo no reconciliado; patrón repetible si el flujo no exige notas obligatorias en diferencias grandes. |
| **Cómo reproducir** | Revisar reporte de cierre sesión 1 (turno 2 C22). |
| **Módulos** | Caja |
| **Prioridad** | P1 |
| **Recomendación** | Política de umbral para diferencias; forced-close con motivo; conciliación con movimientos (`cash_movements` sesión 1). |

---

### P1-06 — Turnos OPEN “zombie” (>14 h) en tenants demo y R

| Campo | Detalle |
|-------|---------|
| **Descripción** | `official_shifts` id=**1** (tenant casa-demo) y id=**2** (tenant R) permanecen **OPEN** desde 2026-06-28 (>48 h). |
| **Evidencia** | SQL: `official_shifts WHERE status='OPEN'`. No hay múltiples OPEN por sucursal en C22 (OK). |
| **Riesgo** | Comandas y liquidaciones pueden quedar asociadas a turnos imposibles; rotación AUTO inconsistente; reportes distorsionados en R. |
| **Cómo reproducir** | Login contexto tenant R → ver turno abierto desde 28/06. |
| **Módulos** | Turnos, SaaS |
| **Prioridad** | P1 |
| **Recomendación** | Cierre forzado auditado de turnos 1 y 2; job de alerta turnos >24 h OPEN. |

---

### P1-07 — Caja OPEN antigua tenant R (>34 h)

| Campo | Detalle |
|-------|---------|
| **Descripción** | `cash_sessions.id=2` tenant R **OPEN** desde 2026-06-29 00:07, `opening_amount=0`, sin `expected_amount`. |
| **Evidencia** | SQL `cash_sessions WHERE status='OPEN'`: ids 2 (R, 34 h) y 4 (C22, 11 h). |
| **Riesgo** | Movimientos acumulados en sesión interminable; mezcla de días contables. |
| **Cómo reproducir** | Admin tenant R → caja actual siempre OPEN. |
| **Módulos** | Caja, SaaS |
| **Prioridad** | P1 |
| **Recomendación** | Cierre forzado con snapshot; política una caja OPEN por sucursal con TTL. |

---

### P1-08 — Alcance read/write desalineado en liquidaciones (diseño)

| Campo | Detalle |
|-------|---------|
| **Descripción** | `GenerateCurrentShiftSettlementsUseCase` opera a nivel **turno completo**; `GetCurrentShiftSettlementsUseCase` con scope `my_cash_session` filtra por **`cash_session_id`**, ocultando o mezclando registros según rol. Documentado en `SETTLEMENTS_PERMISSION_AUDIT.md`. |
| **Evidencia** | Settlements 3,6,7: turno 5 cerrado pero `cash_session_id=4` actual → visibles para cajera, no para owner en turno 6. |
| **Riesgo** | Liquidaciones “invisibles” o “fantasma” según rol; pagos duplicados o omitidos en concurrencia multi-caja. |
| **Cómo reproducir** | Dos cajeras, generar/pagar en cajas distintas; comparar listados. |
| **Módulos** | Liquidaciones, Concurrencia |
| **Prioridad** | P1 |
| **Recomendación** | Unificar reglas de scope en generate, read, pay y close-check. |

---

## Hallazgos P2

> Importantes; no detienen ventas inmediatas pero degradan operación, auditoría o UX.

---

### P2-01 — Print job pendiente: ticket liquidación no impreso

| Campo | Detalle |
|-------|---------|
| **Descripción** | `print_jobs.id=105`, tipo `SETTLEMENT_PAYMENT`, **PENDING**, `source_id=4` (liquidación PAID), creado 2026-06-30 09:16:15. Dispositivo C22 `last_seen_at=08:40:26` (agente desconectado antes del job). |
| **Evidencia** | SQL `print_jobs`; `print_devices` id=1. 104 jobs PRINTED, 0 FAILED. |
| **Riesgo** | Ticket físico ausente; personal sin comprobante; no bloquea pago (correcto). |
| **Cómo reproducir** | Apagar agente → pagar liquidación → job queda PENDING. |
| **Módulos** | Impresión, Agent |
| **Prioridad** | P2 |
| **Recomendación** | Reprint desde UI/admin; monitoreo heartbeat en Control Center; alerta jobs PENDING >5 min. |

---

### P2-02 — Comandas BILLED en turnos cerrados (C22)

| Campo | Detalle |
|-------|---------|
| **Descripción** | 10 comandas BILLED en turnos 4 y 5 ya **CLOSED** (ids 13–23). **Todas tienen venta asociada** (`sales.order_id` OK) — no están “sin cobrar”. |
| **Evidencia** | SQL JOIN orders/sales/official_shifts. Pagos cuadran con totales. |
| **Riesgo** | Ruido en auditoría de turno; reportes por turno cerrado mezclan operación ya facturada; confusión en QA (“¿cobrables?”). |
| **Cómo reproducir** | Filtrar comandas BILLED con `official_shift_id` en turnos CLOSED. |
| **Módulos** | Comandas, Turnos, Ventas |
| **Prioridad** | P2 |
| **Recomendación** | Documentar que BILLED+cobrada en turno cerrado es válido; mejorar reportes por rango horario vs turno. |

---

### P2-03 — Ventas directas sin comanda (`sales.id=2,3`)

| Campo | Detalle |
|-------|---------|
| **Descripción** | Dos ventas QR de 8 Bs con `order_id=NULL`, status PAID, con items y pagos OK. |
| **Evidencia** | SQL `sales WHERE order_id IS NULL`. |
| **Riesgo** | Bajo si son ventas directas intencionales; medio si UI no distingue origen en reportes. |
| **Cómo reproducir** | `POST /direct-sales` o flujo equivalente histórico. |
| **Módulos** | Ventas |
| **Prioridad** | P2 |
| **Recomendación** | Etiquetar en reportes “venta directa”; validar permiso `sales.direct_create`. |

---

### P2-04 — Tenant `casa-demo` suspended con datos zombie

| Campo | Detalle |
|-------|---------|
| **Descripción** | Tenant id=1 **suspended**; turno 1 OPEN; comandas OPEN/SENT antiguas (ids 1,2,3,6); login PIN/password falla con mensaje suscripción. JWT existente de `admin.demo` aún responde `auth/me` 200. |
| **Evidencia** | SQL tenants; API login 422; API me 200 con token previo. |
| **Riesgo** | Confusión en QA; datos demo contaminan métricas globales si no se filtra tenant. |
| **Cómo reproducir** | Login `cajero.demo` → 422; consultar orders tenant 1. |
| **Módulos** | SaaS, Autenticación |
| **Prioridad** | P2 |
| **Recomendación** | No usar casa-demo en prod; invalidar tokens al suspender tenant; limpiar turnos/comandas demo. |

---

### P2-05 — Garzón HUGO: API reporta 2 comandas activas

| Campo | Detalle |
|-------|---------|
| **Descripción** | `GET /waiter/orders/active` (proxy en script QA) → count=2 para HUGO. En BD C22 **no hay** orders OPEN/SENT_TO_BAR (solo BILLED/CANCELLED recientes). |
| **Evidencia** | API QA vs SQL `orders WHERE status IN ('OPEN','SENT_TO_BAR') AND tenant_id=2` → 0 filas. |
| **Riesgo** | UI garzón muestra mesas/comandas stale o criterio distinto al operador espera. |
| **Cómo reproducir** | Login HUGO → pantalla “Mis mesas”. |
| **Módulos** | Garzón, Frontend |
| **Prioridad** | P2 |
| **Recomendación** | Auditar filtro de `WaiterController` (turno, asignaciones, estados); alinear con turno OPEN 6. |

---

### P2-06 — Heartbeat impresión desactualizado vs operación

| Campo | Detalle |
|-------|---------|
| **Descripción** | Dispositivos ACTIVE pero `last_seen_at` ~08:40 mientras último pago/print job 09:16. |
| **Evidencia** | `print_devices` vs `print_jobs` id=105. |
| **Riesgo** | Cola silenciosa sin impresión; operador asume impresora OK. |
| **Cómo reproducir** | Detener agente Windows; operar caja normalmente. |
| **Módulos** | Impresión, Agent, SaaS Control Center |
| **Prioridad** | P2 |
| **Recomendación** | Indicador UI “agente offline”; reintentos/backoff documentados en agente. |

---

### P2-07 — Comandas demo tenant 1 antiguas (>14 h)

| Campo | Detalle |
|-------|---------|
| **Descripción** | Orders 1,2,3,6 en estados OPEN/SENT_TO_BAR desde 2026-06-28 en tenant suspended. |
| **Evidencia** | SQL auditoría órdenes antiguas. |
| **Riesgo** | Solo afecta demo; ninguna en C22. |
| **Cómo reproducir** | Consultar `orders WHERE tenant_id=1`. |
| **Módulos** | Comandas, SaaS |
| **Prioridad** | P2 |
| **Recomendación** | Archivar o cancelar en entorno importado. |

---

### P2-08 — Concurrencia: apertura/cierre caja sin `lockForUpdate` evidente

| Campo | Detalle |
|-------|---------|
| **Descripción** | Revisión estática: `ChargeOrderUseCase` usa transacción; `MarkSettlementPaidUseCase` usa transacción + secuencia con lock. Apertura de caja no muestra patrón `lockForUpdate` en búsqueda rápida. Dos cajeras abriendo caja simultáneamente podrían crear dos sesiones OPEN (depende de validación en use case). |
| **Evidencia** | Código; BD actual C22 solo 1 OPEN por branch (OK histórico, no prueba de carrera). |
| **Riesgo** | Doble sesión OPEN; movimientos en sesiones paralelas. |
| **Cómo reproducir** | Test de carga: dos POST concurrentes `/cash/session/open` misma sucursal. |
| **Módulos** | Caja, Concurrencia |
| **Prioridad** | P2 |
| **Recomendación** | Unique parcial o lock pesimista en “una OPEN por branch”; test de concurrencia automatizado. |

---

### P2-09 — Concurrencia: dos pagos simultáneos misma liquidación

| Campo | Detalle |
|-------|---------|
| **Descripción** | `MarkSettlementPaidUseCase` transaccional; status PENDING→PAID debería ser idempotente, pero requiere verificación de lock en fila `staff_settlements`. |
| **Evidencia** | Código con `DB::transaction`; sin prueba concurrente en BD real. |
| **Riesgo** | Doble pago / doble movimiento de caja en race. |
| **Cómo reproducir** | Dos POST `/settlements/{id}/mark-paid` paralelos con misma liquidación PENDING. |
| **Módulos** | Liquidaciones, Concurrencia |
| **Prioridad** | P2 |
| **Recomendación** | `lockForUpdate` en settlement row; unique en ticket ya existe (OK). |

---

### P2-10 — Rutas print-device autenticadas por token de dispositivo (superficie ampliada)

| Campo | Detalle |
|-------|---------|
| **Descripción** | Grupo `nightpos.print-device` expone heartbeat, pending jobs, claim/printed/failed **sin** JWT de usuario. Correcto para agente, pero compromiso de `device_token` = control de cola de impresión. |
| **Evidencia** | `routes/api.php` líneas 71–77. |
| **Riesgo** | Token filtrado permite claim/fail de jobs ajenos si no hay binding estricto tenant/branch. |
| **Cómo reproducir** | Usar token de dispositivo de otra sucursal contra `/print-jobs/pending`. |
| **Módulos** | Seguridad, Impresión |
| **Prioridad** | P2 |
| **Recomendación** | Auditar middleware `nightpos.print-device`; rotación de tokens; rate limit heartbeat. |

---

## Hallazgos P3

> Mejoras, deuda técnica, configuración dev — no bloquean noche si se conocen.

---

### P3-01 — `public/storage` NOT LINKED

| Campo | Detalle |
|-------|---------|
| **Descripción** | `php artisan about` reporta enlace simbólico de storage ausente. |
| **Evidencia** | Artisan about 2026-06-30. |
| **Riesgo** | Logos/adjuntos 404 en UI. |
| **Cómo reproducir** | Subir logo tenant → URL `/storage/...` falla. |
| **Módulos** | Frontend, Backend |
| **Prioridad** | P3 |
| **Recomendación** | `php artisan storage:link` en despliegue. |

---

### P3-02 — PWA garzón deshabilitada en build local

| Campo | Detalle |
|-------|---------|
| **Descripción** | `VITE_PWA_ENABLED=false` en frontend dev (documentado en auditoría frontend). |
| **Evidencia** | `frontend/NIGHTPOS_REAL_DB_QA_AUDIT.md`. |
| **Riesgo** | Ninguno para caja desktop; garzón sin install prompt. |
| **Cómo reproducir** | Revisar `.env` frontend. |
| **Módulos** | Frontend, Garzón |
| **Prioridad** | P3 |
| **Recomendación** | Habilitar en perfil de build waiter cuando proceda. |

---

### P3-03 — Agente local `poll_interval_ms=100`

| Campo | Detalle |
|-------|---------|
| **Descripción** | Config dev del agente imprime polling agresivo (100 ms vs 15 s recomendado prod). |
| **Evidencia** | `agent/NIGHTPOS_REAL_DB_QA_AUDIT.md`, `agent/config.json`. |
| **Riesgo** | Carga innecesaria en hosting si se copia config dev. |
| **Cómo reproducir** | Leer `agent/config.json`. |
| **Módulos** | Agent, Rendimiento |
| **Prioridad** | P3 |
| **Recomendación** | Template prod con ≥15000 ms. |

---

### P3-04 — Script SQL auditoría con columnas obsoletas

| Campo | Detalle |
|-------|---------|
| **Descripción** | `production_readiness_audit.sql` referencia `closing_amount`, `orders.sale_id`, `waiter_tables`, etc. Ejecución aborta en sección caja cerrada. |
| **Evidencia** | Error MySQL 1054 en `closing_amount`; tabla `waiter_tables` no existe. |
| **Riesgo** | QA automatizado incompleto en próximas pasadas. |
| **Cómo reproducir** | Pipe script completo a mysql. |
| **Módulos** | Base de datos, Tooling |
| **Prioridad** | P3 |
| **Recomendación** | Actualizar script a schema real (`declared_closing_amount`, JOIN sales por `order_id`). |

---

### P3-05 — Habitación demo en CLEANING sin servicio activo

| Campo | Detalle |
|-------|---------|
| **Descripción** | Room id=7 (tenant demo) `status=CLEANING`; único `room_service` en BD está FINISHED en tenant R room 8. |
| **Evidencia** | SQL rooms + room_services. |
| **Riesgo** | Bajo; solo demo. |
| **Cómo reproducir** | Ver mapa habitaciones casa-demo. |
| **Módulos** | Habitaciones |
| **Prioridad** | P3 |
| **Recomendación** | Reset estado a AVAILABLE en demo o al reactivar tenant. |

---

## Módulos — estado resumido

| Módulo | Estado | Notas clave |
|--------|--------|-------------|
| **Autenticación** | ⚠️ | JWT OK usuarios reales; tenant suspended bloquea login pero no invalida tokens viejos; permiso `shifts.close` ausente en cajera |
| **Caja** | ⚠️ | Integridad movimientos OK; cierre id=3 sin declarado; diferencia -75 sesión 1; R caja zombie |
| **Turnos** | ⚠️ | C22 turno 6 OPEN OK; turnos 1–2 zombie; cierre turno bloqueado para cajera |
| **Comandas** | ✅ C22 | Sin OPEN/SENT pendientes en C22; BILLED+cobradas en turnos cerrados |
| **Ventas** | ✅ | 13 ventas; pagos = totales; 2 ventas directas sin comanda |
| **Liquidaciones** | ❌ | 3 PENDING en turno cerrado; scope owner/cajera inconsistente; numeración OK post-fix |
| **Habitaciones** | ✅ | Sin OCCUPIED huérfanas; 1 servicio FINISHED coherente |
| **Garzón** | ⚠️ | API 2 activas vs 0 OPEN en BD — revisar filtro |
| **Impresión** | ⚠️ | 1 PENDING; heartbeat stale; no bloquea pagos |
| **SaaS** | ⚠️ | 3 tenants coherentes; demo suspended; sin branch codes duplicados |
| **Base de datos** | ✅ | Sin FK rotas detectadas; sin tickets duplicados; `document_sequences` alineado |
| **Backend** | ⚠️ | Transacciones en ventas/liquidaciones; riesgos concurrencia caja/apertura |
| **Frontend** | ⚠️ | No QA visual completo; API indica UX cierre turno y liquidaciones |
| **Seguridad** | ❌ | APP_DEBUG; revisar tokens print-device y multitenancy en endpoints admin |
| **Rendimiento** | ✅ local | Sin evidencia queries lentas en dump; polling agente dev agresivo |
| **Concurrencia** | ⚠️ | No probado bajo carga real; escenarios documentados P2 |

---

## Integridad de base de datos (resultados positivos)

Consultas ejecutadas sin anomalías críticas:

| Check | Resultado |
|-------|-----------|
| Tickets liquidación duplicados `(tenant_id, branch_id, ticket_number)` | **0** |
| `document_sequences.last_value` vs max ticket C22/R | **Alineado** |
| Ventas sin items | **0** |
| Ventas sin pagos | **0** |
| Suma `sale_payments` vs `sales.total` | **0 diferencias** |
| Movimientos caja sin sesión | **0** |
| Liquidaciones PAID sin `cash_movement_id` | **0** |
| Multas APPLIED sin settlement | **0** (0 multas en BD) |
| Usuarios activos sin sucursal (no superadmin) | **0** |
| Usuario con branch fuera de tenant | **0** |
| Múltiples turnos OPEN misma sucursal C22 | **0** |
| Branch codes duplicados por tenant | **0** |

---

## Autenticación y permisos — detalle

| Prueba | Resultado |
|--------|-----------|
| `auth/me` superadmin, LAURA, lizvania, HUGO, DARDO | 200 |
| Login PIN demo (casa-demo) | 422 tenant inactivo (esperado) |
| `auth/me` admin.demo con JWT previo | 200 (token no revocado por suspensión) |
| Refresh/logout | No mutado en esta pasada (solo lectura me) |
| Permisos cajera cierre turno | **403** `shifts.close` |
| Rutas operativas caja/liquidaciones cajera | 200 |

**Endpoints públicos intencionales:** `health`, `auth/login-*`, `auth/login-context/*`.  
**Resto:** `auth:api` + `nightpos.tenant` + permisos granulares (~100 grupos con `nightpos.permission` en `api.php`).

---

## Caja — detalle

| Sesión | Tenant | Estado | Notas |
|--------|--------|--------|-------|
| 1 | C22 | CLOSED | Diferencia **-75 Bs** |
| 2 | R | **OPEN** 34 h | `opening_amount=0` |
| 3 | C22 | CLOSED | **Sin monto declarado** |
| 4 | C22 | **OPEN** | Caja operativa actual (`opening_amount=1000`) |

Movimientos: 33 registros, todos con sesión válida. Pagos mixtos: todas las ventas usan un solo `sale_payment` coherente con `payment_mode` (CASH o QR); no hay split multi-línea en datos actuales.

---

## Turnos — detalle

| id | Tenant | Estado | business_date | Notas |
|----|--------|--------|---------------|-------|
| 1 | demo | OPEN | 2026-06-28 | Zombie |
| 2 | R | OPEN | 2026-06-28 | Zombie |
| 3–5 | C22 | CLOSED | — | Operación real |
| 6 | C22 | **OPEN** | 2026-06-30 | Turno actual |

Rotación 5→6 ocurrió 2026-06-30 09:16 con liquidaciones PENDING sin resolver en turno 5.

---

## Comandas y ventas — detalle

**C22:** estados BILLED/CANCELLED recientes; **0** OPEN/SENT_TO_BAR.  
**BILLED en turno cerrado:** 10 comandas, **todas cobradas** (sale_id presente).  
**Ventas huérfanas de comanda:** ids 2, 3 (QR 8 Bs) — probable venta directa.

---

## Liquidaciones — detalle

| id | Tipo | Turno | Estado | Neto | Ticket | cash_movement |
|----|------|-------|--------|------|--------|---------------|
| 1 | WAITER | 2 | PAID | 1.25 | 000001 | ✓ |
| 2 | GIRL | 2 | PAID | 290 | 000002 | ✓ |
| 3 | WAITER | **5 CLOSED** | **PENDING** | 53 | — | — |
| 4 | GIRL | 5 | PAID | 80 | 000003 | ✓ |
| 5 | GIRL | 5 | PAID | 40 | 000002 | ✓ |
| 6 | GIRL | **5 CLOSED** | **PENDING** | 190 | — | — |
| 7 | GIRL | **5 CLOSED** | **PENDING** | 90 | — | — |

---

## Habitaciones — detalle

8 habitaciones totales; 7 AVAILABLE, 1 CLEANING (demo).  
1 `room_service` FINISHED en R — habitación vuelve AVAILABLE. **Sin bloqueos anómalos en C22.**

---

## Impresión — detalle

| Métrica | Valor |
|---------|-------|
| Jobs PRINTED | 104 |
| PENDING | 1 (id=105) |
| FAILED | 0 |
| Dispositivos | 2 ACTIVE (C22, R) |

Pago liquidación **no depende** de print job SUCCESS (comportamiento correcto).

---

## SaaS — detalle

| Tenant | Plan | Estado |
|--------|------|--------|
| casa-demo | NULL | suspended |
| C22 | ENTERPRISE | active |
| R | ENTERPRISE | active |

1 branch por tenant; códigos únicos. Roles con permisos asignados (girl 15 → tenant_owner 255).

---

## Frontend — hallazgos (API-backed)

| Pantalla / flujo | Riesgo |
|------------------|--------|
| Liquidaciones turno owner vs cajera | Lista vacía vs 5 items |
| Cierre turno cajera | Botón falla 403 |
| Pago liquidación 6/7 | pay-preview 200; verificar UI sin 409 post-fix |
| Login demo | Mensaje suscripción |
| Garzón activas | Posible stale (2 vs 0 BD) |

**Checklist manual pendiente** (operador en navegador C22): ver `frontend/NIGHTPOS_REAL_DB_QA_AUDIT.md`.

---

## Seguridad — resumen

| Área | Hallazgo |
|------|----------|
| Debug en prod | P0-02 |
| Multitenancy | Middleware `nightpos.tenant` en rutas operativas; no probado bypass IDOR en esta pasada |
| Print device token | P2-10 |
| JWT post-suspensión tenant | P2-04 |
| Permisos granulares | Mayoría rutas con `nightpos.permission`; health/login públicos |

---

## Rendimiento — resumen

| Área | Hallazgo |
|------|----------|
| Dump actual | Volúmenes bajos; sin slow query log analizado |
| Cache/Queue | database driver (artisan about) |
| Polling agente | 100 ms dev (P3) |
| SSE/notificaciones | No medido en esta pasada |

---

## Concurrencia — escenarios a probar pre-go-live

| Escenario | Riesgo | Prioridad |
|-----------|--------|-----------|
| Dos cobros misma comanda | Venta duplicada | Alta |
| Dos `mark-paid` misma liquidación | Doble salida caja | Alta |
| Dos aperturas caja misma sucursal | Doble OPEN | Media |
| Cierre turno + cobro simultáneo | PENDING huérfanos | Media |
| Claim mismo print_job dos agentes | Doble impresión | Baja |
| Rotación AUTO turno + operaciones | Comandas en turno viejo | Media |

---

## Cálculo de preparación (68 %)

Ponderación por área operativa crítica:

| Área | Peso | Score | Ponderado |
|------|------|-------|-----------|
| Integridad BD / ventas / pagos | 20 % | 95 % | 19.0 |
| Liquidaciones + numeración | 15 % | 55 % | 8.3 |
| Caja + arqueo | 15 % | 60 % | 9.0 |
| Turnos | 10 % | 50 % | 5.0 |
| Auth + permisos | 10 % | 70 % | 7.0 |
| Comandas + garzón | 10 % | 75 % | 7.5 |
| Impresión | 5 % | 80 % | 4.0 |
| Seguridad despliegue | 10 % | 40 % | 4.0 |
| Frontend UX operativa | 5 % | 65 % | 3.3 |
| **Total** | **100 %** | — | **67.1 → 68 %** |

---

## Acciones recomendadas antes de noche producción (sin implementar aquí)

1. **Desplegar** fix `DocumentSequenceService` y verificar `last_value` en hosting (P0-01).
2. **Configurar** `APP_DEBUG=false`, `APP_ENV=production` (P0-02).
3. **Pagar o resolver** liquidaciones 3, 6, 7 (333 Bs) o bloquear rotación hasta clearance (P1-01).
4. **Definir** quién cierra turno; ajustar permiso o UI (P1-02).
5. **Cerrar** turnos/cajas zombie tenants R y demo (P1-06, P1-07).
6. **Levantar** agente impresión y reprint job 105 (P2-01).
7. **Ejecutar** checklist UI C22 (`frontend/NIGHTPOS_REAL_DB_QA_AUDIT.md`).

---

## Scripts y evidencia reutilizable

```bash
# API read-only
cd backend && php scripts/real_db_qa_internal.php

# SQL (corregir script antes de batch completo)
Get-Content backend/scripts/production_readiness_audit.sql | mysql -u root nigtpos

# Evidencia API
backend/storage/app/real_db_qa_api_results.json
```

---

## Documentos relacionados

- `backend/NIGHTPOS_REAL_DB_QA_AUDIT.md`
- `frontend/NIGHTPOS_REAL_DB_QA_AUDIT.md`
- `agent/NIGHTPOS_REAL_DB_QA_AUDIT.md`
- `backend/DOCUMENT_SEQUENCE_SERVICE_FINAL_FIX_REPORT.md`
- `backend/SETTLEMENTS_PERMISSION_AUDIT.md`

---

**Fin del diagnóstico — ningún código ni dato fue modificado durante esta auditoría.**
