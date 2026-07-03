# NightPOS V1 — Roadmap de Hardening Operativo

**Fecha:** 2026-06-30  
**Alcance:** Prevención de errores humanos en operación nocturna — **sin implementación de código**  
**Base:** QA funcional sobre MySQL real (`nigtpos`), auditoría `NIGHTPOS_PRODUCTION_READINESS_AUDIT.md`, revisión de flujos existentes (`CashSessionCloseCheckBuilder`, `getShiftClosureCheck`, `EnsureOperationalShiftUseCase`, Control Center SaaS)

---

## Objetivo

Convertir NightPOS en un sistema donde **un error humano típico no pueda propagarse** hasta romper caja, turno, personal sin cobrar o auditoría fiscal. El hardening no busca bugs de programación: busca **cortar la cadena operador → acción irreversible → daño**.

**Principio rector:** toda acción crítica debe pasar por **validación → bloqueo o confirmación explícita → registro auditable → alerta si queda deuda**.

---

## Estado actual — defensas ya existentes

NightPOS V1 ya tiene piezas de hardening que deben **conservarse y extenderse**, no reemplazarse:

| Capa | Qué existe hoy | Límite actual |
|------|----------------|---------------|
| **Cierre de caja** | `GET /cash/session/current/close-check` + bloqueo en `CloseCashSessionUseCase` | Bloquea comandas cobrables, piezas activas, liquidaciones sin generar/pagar (scope caja) |
| **Cierre de turno (UI)** | `GET /shifts/current/close-check` + pantalla `shifts/close.vue` | Liquidaciones PENDING son **warning**, no blocker; confirmación permite cerrar igual |
| **Cierre de turno (API)** | `CloseOfficialShiftUseCase` | Solo exige cajas cerradas; **no valida** liquidaciones PENDING ni fuentes sin liquidar |
| **Rotación AUTO** | `EnsureOperationalShiftUseCase` | Puede cerrar turno vencido **sin resolver** pendientes (evidencia real: turno 5 C22) |
| **Cierre forzado caja** | Admin `force-close` con snapshot de blockers + motivo | Existe; requiere permiso y auditoría |
| **Checklist primera noche** | `GET /first-night-checklist` | Informativo; **no bloquea** apertura de caja/turno |
| **Monitoreo SaaS** | Control Center (`PlatformOperationsTenantAnalyzer`) | Alertas caja/turno >14 h, agente offline, print FAILED — **warning**, no acción en tenant |
| **Dominio liquidaciones** | Excepciones: caja requerida, no pagar PAID, ticket conflict 409 | No impide cierre de turno con deuda de personal |
| **Impresión** | Pago no bloqueado si agente caído | Correcto operativamente; riesgo de ticket no impreso sin alerta visible en caja |

---

## Incidentes reales mapeados a error humano

Estos casos ocurrieron (o son reproducibles) con operadores reales en la base de hosting — son la **lista de diseño** del hardening:

| Error humano | Consecuencia observada | Módulo |
|--------------|------------------------|--------|
| Cerrar turno con liquidaciones sin pagar | 3 PENDING (333 Bs) en turno 5 CLOSED; turno 6 ya OPEN | Turnos + Liquidaciones |
| Supervisor cree que no hay pendientes | Owner `current-shift` count=0; cajera count=5 | Liquidaciones + UX |
| Cajera intenta cerrar turno | 403 `shifts.close` pero acción expuesta en UI | Permisos + UX |
| Cerrar caja sin arqueo declarado | `cash_sessions.id=3` CLOSED con `declared_closing_amount=NULL` | Caja |
| Operar con agente apagado | `print_job` 105 PENDING; pago OK sin ticket | Impresión |
| No cerrar turno/caja días anteriores | Turnos 1–2 OPEN >48 h; caja R OPEN >34 h | Turnos + Caja + SaaS |
| Rotación / cierre apresurado al amanecer | Comandas BILLED en turnos cerrados (cobradas, pero ruido fiscal) | Turnos + Comandas |

---

## Matriz de prioridades

| Prioridad | Criterio | Meta operativa |
|-----------|----------|----------------|
| **Crítica** | Puede dejar dinero/personal sin resolver o bloquear la noche siguiente | Implementar antes de go-live |
| **Alta** | Distorsiona cierre, auditoría o roles; alta probabilidad en turno real | Primera quincena post go-live |
| **Media** | Reduce fricción y errores recurrentes; no suele ser bloqueante solo | V1.1 |
| **Baja** | Pulido, observabilidad extra, confort | Backlog |

---

## 1. Caja

### H-01 — Bloqueo backend: cierre sin monto declarado

| | |
|---|---|
| **Error humano** | Cajera/admin cierra caja apurada sin contar efectivo o sin completar el formulario |
| **Estado actual** | `CloseCashSessionUseCase` valida `declaredClosingAmount >= 0`; en BD hay cierre con NULL (probable force-close legacy o bypass) |
| **Propuesta** | Validación **obligatoria** `declared_closing_amount IS NOT NULL` en cierre normal; force-close solo con permiso + motivo + snapshot; UI no habilita botón hasta campo numérico válido |
| **Alertas** | Banner rojo si `expected_amount` calculado y campo vacío >30 s |
| **Monitoreo** | Métrica SaaS: `CASH_CLOSED_WITHOUT_DECLARED` |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Evita cierres fiscalmente inválidos; arqueo siempre trazable |

---

### H-02 — Notas obligatorias en diferencias de caja

| | |
|---|---|
| **Error humano** | Cierre con faltante/sobrante sin explicación (evidencia: sesión 1, diferencia -75 Bs) |
| **Estado actual** | `closing_notes` opcional; diferencia calculada pero no exige justificación |
| **Propuesta** | Umbral configurable (ej. \|diff\| > 20 Bs o >0.5 % del expected): **bloquear cierre** hasta `closing_notes` mín. 20 caracteres; segundo nivel owner si diff > umbral alto |
| **Automatización** | Pre-llenar resumen de movimientos sospechosos en modal de cierre |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Reduce faltantes “silenciosos”; mejora disciplina de arqueo |

---

### H-03 — Wizard de cierre (no modal único)

| | |
|---|---|
| **Error humano** | Saltarse pasos: no revisar blockers, no imprimir resumen, no confirmar combo/manillas |
| **Estado actual** | `close-check` + modal en `cash/index.vue` con blockers y acciones |
| **Propuesta** | Flujo secuencial: (1) blockers → (2) resumen financiero → (3) conteo declarado → (4) confirmación escrita (“CERRAR CAJA”) → (5) impresión obligatoria o “omitir con motivo” solo owner |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Impone ritmo de cierre; baja cierres incompletos |

---

### H-04 — Una sesión OPEN por sucursal (no solo por usuario)

| | |
|---|---|
| **Error humano** | Dos cajeras abren caja en la misma sucursal; movimientos mezclados |
| **Estado actual** | `OpenCashSessionUseCase` impide segunda sesión **del mismo usuario**; no hay lock global por branch en revisión rápida |
| **Propuesta** | Validación branch: máximo 1 `cash_sessions.status=OPEN` por `(tenant_id, branch_id)` salvo modo multi-caja explícito en plan; lock pesimista en apertura |
| **Alertas** | SSE + banner admin si se intenta segunda apertura |
| **Prioridad** | **Crítica** (si operación es single-caja) / **Media** (si multi-caja planificado) |
| **Impacto operativo** | Elimina caos de dos cajas paralelas no intencionales |

---

### H-05 — TTL y alertas proactivas de caja abierta

| | |
|---|---|
| **Error humano** | Olvidar cerrar caja al terminar (evidencia: caja R OPEN 34 h) |
| **Estado actual** | Control Center warning >14 h (`CASH_SESSION_TOO_LONG`) |
| **Propuesta** | Escalonado: **8 h** banner cajera; **14 h** notificación owner; **24 h** ticket SaaS + email; opcional bloqueo de **nueva** apertura hasta cerrar la anterior |
| **Automatización** | Job nocturno `DetectStaleCashSessions` |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Evita mezclar días contables |

---

### H-06 — Force-close con doble autorización

| | |
|---|---|
| **Error humano** | Admin fuerza cierre para “salir rápido” dejando blockers sin resolver |
| **Estado actual** | `ForceCloseCashSessionAdminUseCase` guarda snapshot; motivos predefinidos |
| **Propuesta** | Si `blockers.length > 0`: exigir permiso `admin.cash_sessions.force_close` + **segundo confirmador** (owner PIN) o delay 60 s con lista de blockers visible; prohibir force-close sin `declared_closing_amount` |
| **Monitoreo** | Dashboard: forced closes / semana con blockers unresolved |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Force-close deja de ser atajo destructivo |

---

### H-07 — Alinear close-check caja con cola real de cobro

| | |
|---|---|
| **Error humano** | Creer que no hay comandas pendientes cuando sí las hay (filtros distintos) |
| **Estado actual** | `CashierChargeableOrdersScope` alineado con close-check (mejora previa documentada) |
| **Propuesta** | Mantener single source of truth; test de regresión en cada release; en UI mostrar **IDs/mesas** de comandas bloqueantes, no solo count |
| **Prioridad** | **Media** |
| **Impacto operativo** | Menos “cerré pensando que estaba limpio” |

---

## 2. Turnos

### H-08 — Liquidaciones PENDING como **blocker** de cierre de turno (backend + UI)

| | |
|---|---|
| **Error humano** | Owner cierra turno aceptando dialog “hay pendientes” (evidencia: turno 5 C22) |
| **Estado actual** | `getShiftClosureCheck`: PENDING → **warnings**; `CloseOfficialShiftUseCase`: **no valida** PENDING; UI `hasPendingSettlements` → confirm y permite cerrar |
| **Propuesta** | **Crítico:** mover `pending_settlements` a **blockers** en API y use case; cierre solo con permiso `shifts.close_with_pending` + motivo auditado; UI elimina “cerrar igual” por defecto |
| **Automatización** | Lista de personas afectadas + monto total en modal |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Impide dejar 333 Bs (o más) de personal sin pagar en turno cerrado |

---

### H-09 — Bloquear rotación AUTO si hay deuda operativa

| | |
|---|---|
| **Error humano** | Sistema rota turno a las 06:00 con liquidaciones/comandas/cajas pendientes |
| **Estado actual** | `EnsureOperationalShiftUseCase` cierra turno AUTO vencido sin checklist |
| **Propuesta** | Antes de `markAutoClosed`: ejecutar mismo checklist que cierre manual; si blockers → **no rotar** + alerta crítica owner + flag `shift_rotation_blocked` |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Evita “turno zombie” y PENDING huérfanos |

---

### H-10 — Política de roles: quién cierra turno

| | |
|---|---|
| **Error humano** | Cajera intenta cerrar turno (403) o owner no sabe que es su responsabilidad |
| **Estado actual** | Permiso `shifts.close` ausente en rol `cashier`; botón puede estar visible |
| **Propuesta** | UI: ocultar acción sin permiso; badge persistente “Cierre de turno: solo supervisor”; checklist pre-cierre visible en dashboard owner; opcional rol `shift_closer` |
| **Capacitación** | Tarjeta en consola: orden obligatorio **caja → liquidaciones → turno** |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Elimina 403 sorpresa y clarifica responsabilidad |

---

### H-11 — Semáforo “Pre-cierre de turno” unificado

| | |
|---|---|
| **Error humano** | Supervisor mira pantallas distintas y concluye que todo está OK |
| **Estado actual** | close-check turno, close-check caja, liquidaciones y reconciliación en pantallas separadas |
| **Propuesta** | Panel único `/shift-console/pre-close` con: cajas abiertas, comandas activas, piezas, liquidaciones generate/pay, agente impresión, diferencia caja acumulada — todo rojo/amarillo/verde |
| **Monitoreo** | Export PDF snapshot antes de cerrar turno |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Una sola fuente de verdad para el cierre de noche |

---

### H-12 — TTL turnos OPEN manuales

| | |
|---|---|
| **Error humano** | Abrir turno manual y no cerrarlo nunca (turnos 1–2 >48 h) |
| **Estado actual** | Turnos manuales no auto-cierran (`EnsureOperationalShiftUseCase`) |
| **Propuesta** | Alertas escalonadas; bloqueo de **nuevo** turno manual si existe OPEN >24 h; SaaS Control Center acción “solicitar cierre” |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Evita turnos imposibles en reportes |

---

### H-13 — Confirmación de efectivo contado vs expected en cierre de turno

| | |
|---|---|
| **Error humano** | Ingresar `counted_cash` incorrecto por prisa |
| **Propuesta** | Si \|counted - expected\| > umbral: segundo campo “repetir conteo” + notas obligatorias; mostrar desglose expected (ventas efectivo, movimientos, liquidaciones) |
| **Prioridad** | **Media** |
| **Impacto operativo** | Reduce errores de tipeo en cierre fiscal |

---

## 3. Comandas

### H-14 — Bloqueo de cierre con comandas cobrables (refuerzo UX)

| | |
|---|---|
| **Error humano** | Dejar mesas SENT_TO_BAR sin cobrar |
| **Estado actual** | Blocker en close-check caja; turno close-check cuenta OPEN/SENT |
| **Propuesta** | Lista nominal en UI; notificación SSE a cajera cada +N min si comanda >X horas; garzón ve badge “pendiente cobro” |
| **Automatización** | Job: comandas SENT_TO_BAR >2 h → notificación owner |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Menos ventas perdidas al cierre |

---

### H-15 — Precheck obligatorio antes de cobrar

| | |
|---|---|
| **Error humano** | Cobrar comanda equivocada, ya cancelada o con items no listos |
| **Estado actual** | `GetOrderPrecheckUseCase`, `OrderItemReadinessChecker` con `charge_blockers` |
| **Propuesta** | UI: no abrir modal de pago si precheck falla; resumen mesa + turno + garzón en header; confirmación si total > umbral |
| **Prioridad** | **Media** |
| **Impacto operativo** | Menos reversos y quejas |

---

### H-16 — Cancelación con motivo y doble confirmación

| | |
|---|---|
| **Error humano** | Cancelar comanda enviada a barra por error |
| **Propuesta** | Motivo obligatorio categorizado; comanda SENT_TO_BAR requiere permiso `orders.cancel_after_bar` o PIN supervisor; límite cancelaciones/garzón/turno con alerta |
| **Monitoreo** | Reporte cancelaciones por usuario |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Disuade cancelaciones accidentales post-barra |

---

### H-17 — Aislamiento sucursal/turno en garzón

| | |
|---|---|
| **Error humano** | Garzón opera mesas de turno anterior o ve datos stale (API HUGO count=2 vs BD 0 OPEN) |
| **Propuesta** | Filtrar `waiter/active` estrictamente por `official_shift_id` OPEN + asignaciones; banner si turno rotó mid-session; forzar refresh al detectar SSE `shift.rotated` |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Evita comandas en turno equivocado |

---

### H-18 — Estados terminales claros (BILLED vs cobrada)

| | |
|---|---|
| **Error humano** | Confundir BILLED con “pendiente de cobro” en auditoría |
| **Propuesta** | UI caja solo estados cobrables; admin ve BILLED+cobrada vs BILLED sin venta (blocker duro si existe); glosario en tooltips |
| **Prioridad** | **Media** |
| **Impacto operativo** | Menos pánico en cierre de turno |

---

## 4. Liquidaciones

### H-19 — Unificar scope lectura/escritura (eliminar “liquidaciones fantasma”)

| | |
|---|---|
| **Error humano** | Owner genera/piensa que no hay pendientes; cajera ve 5 items de turno anterior |
| **Estado actual** | `GenerateCurrentShiftSettlementsUseCase` vs `GetCurrentShiftSettlementsUseCase` con scopes distintos (`my_cash_session` vs branch) |
| **Propuesta** | Regla única documentada en producto: **misma clave** `(official_shift_id, cash_session_id?)` en generate, read, pay, close-check; sección UI “Pendientes turno anterior” separada visualmente |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Supervisor y cajera ven la misma realidad |

---

### H-20 — Bloqueo: no rotar turno / no cerrar caja con PENDING del turno actual

| | |
|---|---|
| **Error humano** | Pagar algunas liquidaciones y olvidar otras |
| **Estado actual** | Close-check caja ya bloquea PENDING en scope caja |
| **Propuesta** | Extender a turno (H-08); desglose por tipo WAITER/GIRL/CLEANING con enlaces directos; contador monto pendiente en app bar caja |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Personal no queda sin cobrar |

---

### H-21 — Secuencia obligatoria: generar → revisar → pagar

| | |
|---|---|
| **Error humano** | Pagar sin generar o generar dos veces |
| **Propuesta** | Botón Pagar deshabilitado si no existe fila PENDING generada; `generate-current-shift` idempotente con resumen “0 nuevas / 3 actualizadas”; alerta si generate con fuentes unsettled post-generate |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Evita omisiones y duplicados lógicos |

---

### H-22 — Preview de pago con confirmación de monto grande

| | |
|---|---|
| **Error humano** | Pagar liquidación chica con monto incorrecto (teclado) |
| **Estado actual** | `pay-preview` existe |
| **Propuesta** | Para neto > umbral: modal “Confirma X BOB a [nombre]” + checkbox; registrar en audit log |
| **Prioridad** | **Media** |
| **Impacto operativo** | Reduce pagos erróneos de alto monto |

---

### H-23 — Guardián de numeración documental pre-noche

| | |
|---|---|
| **Error humano** | Operador no detecta desfase de tickets antes de la noche |
| **Propuesta** | Job diario: comparar `document_sequences.last_value` vs max ticket; banner admin si desfase; checklist pre-apertura incluye “secuencia OK” |
| **Monitoreo** | Alerta SaaS `DOCUMENT_SEQUENCE_LAG` |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Previene repetición del incidente 409 en producción |

---

### H-24 — Multas y descuentos manuales con trazabilidad reforzada

| | |
|---|---|
| **Error humano** | Descuento manual excesivo o multa mal aplicada |
| **Estado actual** | Validaciones dominio (`manualDiscountReasonRequired`, etc.) |
| **Propuesta** | Descuento > X % requiere PIN owner; multa cancelada solo con motivo; reporte fin de turno de ajustes |
| **Prioridad** | **Media** |
| **Impacto operativo** | Disuade abuso y errores de deducción |

---

## 5. Impresión

### H-25 — Indicador “Agente OFFLINE” en caja (no bloqueante)

| | |
|---|---|
| **Error humano** | Operar asumiendo tickets impresos; agente apagado (job 105 PENDING) |
| **Estado actual** | Control Center warning; cajera sin indicador prominente |
| **Propuesta** | Banner amarillo persistente si `last_seen_at` > 3 min; contador jobs PENDING/FAILED en header caja; sonido opcional al pagar liquidación sin PRINTED en 2 min |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Operador sabe que debe reimprimir o encender agente |

---

### H-26 — Cola de reimpresión operativa

| | |
|---|---|
| **Error humano** | No saber que falta ticket de liquidación |
| **Propuesta** | Pantalla caja “Impresiones pendientes” con botón reprint; auto-retry jobs FAILED con backoff; no bloquear pago |
| **Automatización** | Job nocturno lista jobs PENDING >1 h → email owner |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Recuperación sin soporte técnico |

---

### H-27 — Checklist pre-apertura: agente conectado

| | |
|---|---|
| **Error humano** | Abrir caja sin probar impresora |
| **Propuesta** | `first-night-checklist` + ítem “Agente impresión online (heartbeat <5 min)”; test print opcional antes de abrir caja |
| **Prioridad** | **Media** |
| **Impacto operativo** | Detecta problema antes del primer pago |

---

### H-28 — Heartbeat SLA en SaaS con acciones sugeridas

| | |
|---|---|
| **Error humano** | Ignorar agente offline en Control Center |
| **Propuesta** | Playbook en UI: reiniciar servicio, verificar URL, test heartbeat; escalar a critical si offline durante horario pico configurado |
| **Prioridad** | **Media** |
| **Impacto operativo** | Reduce tiempo muerto de impresión |

---

## 6. SaaS y gobernanza multi-tenant

### H-29 — Checklist primera noche **bloqueante**

| | |
|---|---|
| **Error humano** | Abrir local sin productos, métodos de pago, motivos de egreso liquidaciones |
| **Estado actual** | `GetFirstNightChecklistUseCase` informativo |
| **Propuesta** | Modo `strict_bootstrap`: impedir `cash/session/open` y `shifts/open` si ítems críticos incompletos (pagos, egreso liquidaciones, ≥1 cajera, agente registrado) |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | Imposible iniciar noche mal configurada |

---

### H-30 — Invalidar sesiones al suspender tenant

| | |
|---|---|
| **Error humano** | Usuario sigue operando con JWT viejo en tenant suspendido |
| **Estado actual** | Login bloqueado; `auth/me` 200 con token previo |
| **Propuesta** | Al suspender: revocar refresh tokens / blacklist JWT por tenant; middleware rechaza operaciones si `tenant.status != active` |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Cierre real de tenant demo/inactivo |

---

### H-31 — Separación entornos demo vs producción

| | |
|---|---|
| **Error humano** | Importar BD demo en hosting productivo; QA en tenant suspended |
| **Propuesta** | Convención: tenants demo con slug `-demo` auto-suspended; script pre-deploy detecta turnos OPEN >7 días; documento runbook “nunca operar casa-demo en prod” |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Evita datos zombie y confusión operativa |

---

### H-32 — Control Center: acciones, no solo alertas

| | |
|---|---|
| **Error humano** | Ribersoft ve warning y no actúa |
| **Estado actual** | `PlatformOperationsTenantAnalyzer` genera issues |
| **Propuesta** | Acciones guiadas: “contactar local”, “ver pendientes”, checklist remoto; severidad critical abre ticket; SLA por tipo |
| **Prioridad** | **Media** |
| **Impacto operativo** | Monitoreo proactivo de franquicias |

---

### H-33 — Límites de plan en operación (no solo en admin)

| | |
|---|---|
| **Error humano** | Exceder usuarios/sucursales/dispositivos sin darse cuenta |
| **Propuesta** | Validar límites en creación usuario/branch/device con mensaje claro; warning en Control Center al 90 % |
| **Prioridad** | **Baja** |
| **Impacto operativo** | Evita sorpresas comerciales mid-noche |

---

## 7. Capas transversales

### H-34 — Backend siempre refuerza lo que muestra la UI

| | |
|---|---|
| **Principio** | Nunca confiar solo en deshabilitar botones |
| **Propuesta** | Todo flujo crítico duplica validación en use case: cierre caja, cierre turno, force-close, mark-paid, charge, open cash |
| **Prioridad** | **Crítica** |
| **Impacto operativo** | API directa / PWA / futuro mobile no bypassa reglas |

---

### H-35 — Audit log operativo legible

| | |
|---|---|
| **Error humano** | Disputa post-noche sin saber quién cerró con pendientes |
| **Propuesta** | Eventos estándar: `shift.closed_with_pending`, `cash.force_closed`, `settlement.paid`, `order.cancelled_after_bar`; vista admin timeline por turno |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Responsabilidad clara |

---

### H-36 — Confirmaciones destructivas tipadas

| | |
|---|---|
| **Propuesta** | Acciones irreversibles requieren escribir texto clave o PIN supervisor: cerrar turno con warning, force-close, cancel post-bar |
| **Prioridad** | **Alta** |
| **Impacto operativo** | Fricción intencional en puntos de no retorno |

---

### H-37 — SSE / refresh obligatorio post-acciones críticas

| | |
|---|---|
| **Error humano** | Pantalla desactualizada tras pago/cierre/rotación |
| **Propuesta** | Tras mark-paid, close cash, shift rotate: invalidar stores Vue + banner “Datos actualizados”; garzón recarga mesas en `shift.rotated` |
| **Prioridad** | **Media** |
| **Impacto operativo** | Menos decisiones sobre datos viejos |

---

### H-38 — Runbook operativo embebido (1 página)

| | |
|---|---|
| **Propuesta** | En consola owner: orden **Comandas → Cobros → Piezas → Generar liquidaciones → Pagar → Cerrar cajas → Cerrar turno**; enlace desde cada blocker |
| **Prioridad** | **Media** |
| **Impacto operativo** | Capacitación in situ para relevos |

---

### H-39 — Modo “noche bloqueada” post-cierre parcial

| | |
|---|---|
| **Error humano** | Reabrir caja/turno en desorden tras cierre a medias |
| **Propuesta** | Si turno OPEN pero todas las cajas cerradas y hay PENDING históricos: modo solo-liquidaciones hasta clearance |
| **Prioridad** | **Baja** |
| **Impacto operativo** | Fuerza completar deuda de personal |

---

## 8. Monitoreo y automatización recomendados

### Jobs programados (cron)

| Job | Frecuencia | Dispara |
|-----|------------|---------|
| `DetectStaleCashSessions` | cada hora | alerta 8/14/24 h |
| `DetectStaleOpenShifts` | cada hora | alerta turnos manuales zombie |
| `DetectPendingSettlementsOnClosedShifts` | cada 15 min | **critical** SaaS + owner |
| `DetectDocumentSequenceLag` | diario 05:00 | admin pre-apertura |
| `DetectOrphanPrintJobs` | cada 30 min | PENDING >15 min |
| `DetectChargeableOrdersAging` | cada 30 min | SENT_TO_BAR >2 h |

### KPIs en dashboard owner (semáforo)

| KPI | Verde | Amarillo | Rojo |
|-----|-------|----------|------|
| Comandas cobrables | 0 | 1–2 | ≥3 |
| Liquidaciones PENDING | 0 | monto <100 | monto ≥100 |
| Agente impresión | heartbeat <3 min | 3–15 min | >15 min o PENDING jobs |
| Caja abierta | <8 h | 8–14 h | >14 h |
| Diferencia caja turno | \|diff\| ≤20 | ≤100 | >100 |

### Canales de alerta

1. **In-app** — banners + SSE (inmediato, cajera/owner)  
2. **Control Center SaaS** — tenants con severidad critical  
3. **Email/Telegram** (opcional V1.1) — solo critical fuera horario  

---

## 9. Roadmap por fases

### Fase 0 — Antes de la primera noche productiva (Crítica)

| ID | Mejora |
|----|--------|
| H-08 | PENDING → blocker cierre turno (API) |
| H-09 | Rotación AUTO con checklist |
| H-19 | Scope liquidaciones unificado + UI “turno anterior” |
| H-20 | Refuerzo PENDING en caja/turno |
| H-23 | Guardián secuencia documental |
| H-29 | Checklist bootstrap bloqueante |
| H-34 | Validación backend espejo de UI |
| H-01 | Cierre caja sin monto declarado imposible |

**Impacto:** cierra el hueco que produjo turno 5 con 333 Bs pendientes y cierres inválidos.

---

### Fase 1 — Primera semana en producción (Alta)

| ID | Mejora |
|----|--------|
| H-02 | Notas en diferencias caja |
| H-03 | Wizard cierre caja |
| H-05 | TTL caja + alertas |
| H-06 | Force-close reforzado |
| H-10 | Política roles cierre turno |
| H-11 | Panel pre-cierre unificado |
| H-12 | TTL turnos manuales |
| H-14 | Comandas aging + lista nominal |
| H-16 | Cancel post-bar reforzada |
| H-17 | Garzón alineado a turno OPEN |
| H-21 | Secuencia generar→pagar |
| H-25 | Banner agente offline en caja |
| H-26 | Cola reimpresión |
| H-30 | Revocar JWT al suspender tenant |
| H-31 | Separación demo/prod |
| H-35 | Audit log operativo |
| H-36 | Confirmaciones tipadas |

**Impacto:** operación nocturna con fricción correcta y recuperación sin soporte.

---

### Fase 2 — V1.1 (Media)

H-07, H-13, H-15, H-18, H-22, H-24, H-27, H-28, H-32, H-37, H-38, jobs de aging.

**Impacto:** menos errores de baja frecuencia y mejor observabilidad.

---

### Fase 3 — Backlog (Baja)

H-33, H-39, integraciones email, multi-caja avanzada (H-04 según producto).

---

## 10. Resumen ejecutivo de prioridades

| Prioridad | Cantidad | Enfoque |
|-----------|----------|---------|
| **Crítica** | 8 | Cierre turno/caja, liquidaciones, secuencia, bootstrap, scope, backend enforcement |
| **Alta** | 16 | TTL, roles, UX destructiva, comandas, impresión, SaaS |
| **Media** | 11 | Wizards, prechecks, runbooks, observabilidad |
| **Baja** | 2 | Límites plan, modos avanzados |

**Meta V1 hardened:** un operador puede equivocarse en un clic, pero **no puede cerrar la noche dejando personal sin pagar, caja sin arqueo o turno rotado con deuda** sin permiso excepcional auditado.

---

## Documentos relacionados

- `NIGHTPOS_PRODUCTION_READINESS_AUDIT.md` — evidencia QA base real  
- `backend/SETTLEMENTS_PERMISSION_AUDIT.md` — desalineación scope liquidaciones  
- `backend/DOCUMENT_SEQUENCE_SERVICE_FINAL_FIX_REPORT.md` — lección secuencia documental  
- `backend/PRINT_AGENT_HEARTBEAT_CONNECTION_RESET_DEEP_AUDIT.md` — impresión y agente  
- `frontend/NIGHTPOS_REAL_DB_QA_AUDIT.md` — UX operativa  

---

**Fin del roadmap — documento de diseño únicamente; sin cambios de código ni base de datos.**
