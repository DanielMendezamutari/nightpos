# NightPOS Health Center — Arquitectura del sistema de autodiagnóstico

**Fecha:** 2026-06-30  
**Versión:** 1.0 (diseño)  
**Alcance:** Arquitectura de producto — **sin implementación de código**  
**Audiencia:** Ribersoft (plataforma), owners de local, operación técnica  
**Documentos base:** `NIGHTPOS_V1_HARDENING_ROADMAP.md`, `NIGHTPOS_PRODUCTION_READINESS_AUDIT.md`, SAAS-1.5 Control Center, QA base real

---

## 1. Visión y propósito

### 1.1 Qué es NightPOS Health Center

**NightPOS Health Center** es el módulo de **autodiagnóstico permanente** de la plataforma. Observa continuamente la salud de todos los componentes — backend, frontend, agente de impresión, base de datos, hosting, licencias y flujos operativos (caja, turnos, comandas, liquidaciones, secuencias documentales) — y produce:

- **Indicadores de salud** en tiempo casi real y histórico  
- **Chequeos automáticos** programados y bajo demanda  
- **Autocorrección** solo donde sea seguro, reversible y auditable  
- **Alertas** graduadas por severidad y audiencia  
- **Reportes diarios** para Ribersoft y para cada local  
- **Porcentaje global de salud** desglosable por dominio, tenant y sucursal  

No reemplaza el monitoreo de infraestructura del hosting (CPU, disco, LiteSpeed); **lo complementa** con inteligencia de negocio NightPOS: “¿puede este local cerrar la noche sin sorpresas?”.

### 1.2 Objetivos de producto

| Objetivo | Métrica de éxito |
|----------|------------------|
| Detectar problemas **antes** de que el operador los sufra | MTTD < 5 min en incidentes críticos operativos |
| Una sola fuente de verdad de “¿estamos bien?” | Health score único consultable en 3 clics |
| Reducir tickets de soporte repetitivos | −40 % consultas “no imprime / no cierra caja” |
| Conectar hardening con observabilidad | Cada regla H-xx del roadmap tiene un check asociado |
| Operación SaaS profesional | Ribersoft ve plataforma + N tenants en un dashboard |

### 1.3 Relación con componentes existentes

NightPOS **ya tiene cimientos** que Health Center debe **evolucionar**, no duplicar:

| Componente actual | Rol hoy | Evolución en Health Center |
|-------------------|---------|----------------------------|
| `GET /api/v1/health` | DB + JWT up/down | Probe de infraestructura L1 |
| `public/health.php` | PHP sin Laravel | Probe hosting / rewrite |
| Laravel `/up` | Health framework | Alias interno |
| **Ribersoft Control Center** (SAAS-1.5) | `health_score`, `operational_status`, issues por tenant | **Vista plataforma** del Health Center |
| `PlatformOperationsTenantAnalyzer` | Score 0–100, tipos de issue limitados | **Motor de scoring** tenant (extender reglas) |
| `GetFirstNightChecklistUseCase` | Bootstrap operativo local | Dominio **Configuración** |
| `PlatformOperationsChecklistCatalog` | Checklist instalación SaaS | Dominio **Onboarding** |
| `CashSessionCloseCheckBuilder` | Blockers pre-cierre caja | Probe **Caja** on-demand |
| `getShiftClosureCheck` | Blockers/warnings turno | Probe **Turnos** on-demand |
| Agente `status.Snapshot` | Estado local tray/servicio | Probe **Agente** edge |
| SSE `OperationalEventEmitter` | Refresh UI | Canal de **alertas in-app** |
| Hardening roadmap jobs propuestos | Diseño only | **Scheduler** Health Center |

**Decisión de producto:** Control Center pasa a ser la **consola Ribersoft** dentro de Health Center; owners y cajeras ven un **Health Panel** reducido en su tenant.

---

## 2. Principios de diseño

1. **Read-only first** — La mayoría de checks no mutan datos; los mutadores van a cola de remediación con confirmación o auto-fix whitelist.  
2. **Scope estricto** — Todo check declara alcance: `platform` | `tenant` | `branch` | `device`.  
3. **Evidencia siempre** — Cada incidencia incluye query, conteos, IDs de ejemplo (sin PII innecesaria).  
4. **Scoring explicable** — El usuario puede expandir “por qué 73 %” dominio por dominio.  
5. **Degradación graceful** — Si el scheduler falla, probes L1 (HTTP health) siguen respondiendo.  
6. **Multitenancy blind** — Checks tenant nunca filtran datos de otro tenant.  
7. **Separación diagnóstico / acción** — Autocorrección nunca en el mismo proceso síncrono que el probe (evita loops).  
8. **Auditoría total** — Toda autocorrección → `health_remediation_logs` + `audit_logs`.

---

## 3. Arquitectura lógica

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         NIGHTPOS HEALTH CENTER                               │
├─────────────────────────────────────────────────────────────────────────────┤
│  UI LAYER                                                                    │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────────┐ │
│  │ Platform Console │  │ Tenant Health    │  │ Branch Ops Strip         │ │
│  │ (Ribersoft)      │  │ (Owner/Admin)    │  │ (Cajera/Supervisor)      │ │
│  └────────┬─────────┘  └────────┬─────────┘  └────────────┬─────────────┘ │
├───────────┼─────────────────────┼─────────────────────────┼─────────────────┤
│  API      │                     │                         │                 │
│  /health-center/platform/*      /health-center/tenant/*   /health-center/  │
│                                 branch/* (read-only KPIs)                    │
├───────────┴─────────────────────┴─────────────────────────┴─────────────────┤
│  HEALTH ORCHESTRATOR                                                         │
│  · Scheduler (cron)  · On-demand triggers  · Event-driven hooks              │
├─────────────────────────────────────────────────────────────────────────────┤
│  PROBE RUNTIME          │  SCORING ENGINE        │  INCIDENT MANAGER         │
│  · Registered checks    │  · Weighted composite  │  · Open / ack / resolve   │
│  · Parallel execution   │  · Domain scores       │  · Dedup + escalation     │
│  · Timeout budgets      │  · Trend (24h/7d)      │  · SLA timers             │
├─────────────────────────┴────────────────────────┴──────────────────────────┤
│  REMEDIATION ENGINE (optional auto)  │  NOTIFICATION BUS                     │
│  · Safe action catalog               │  · In-app / SSE / email / webhook     │
│  · Manual approval queue             │  · Daily digest builder               │
├─────────────────────────────────────────────────────────────────────────────┤
│  HEALTH REGISTRY (persistence)                                               │
│  health_check_catalog · health_runs · health_scores · health_incidents ·     │
│  health_reports · health_subscriptions · health_remediation_logs             │
├─────────────────────────────────────────────────────────────────────────────┤
│  DATA SOURCES / PROBES                                                       │
│  MySQL · Redis/Cache · Queue · Storage FS · JWT config · HTTP self-ping ·    │
│  Print devices/jobs · Cash/Shifts/Orders/Settlements · document_sequences ·  │
│  Permissions/Roles · Tenant license · Agent heartbeat · Frontend beacon      │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.1 Capas de profundidad (L0–L4)

| Nivel | Nombre | Latencia | Ejemplo |
|-------|--------|----------|---------|
| **L0** | Ping | <1 s | `/health`, `/health.php`, agente alive |
| **L1** | Infraestructura | 1–5 s | DB, JWT, storage link, queue worker, APP_DEBUG |
| **L2** | Integridad datos | 5–30 s | Secuencia documental, pagos vs totales, huérfanos |
| **L3** | Operación en curso | 30–120 s | Caja zombie, turno zombie, PENDING en turno cerrado |
| **L4** | Predicción / tendencia | minutos | “Probable bloqueo de cierre en 2 h” |

Checks de nivel superior **dependen** de L0–L1 OK; si DB down, no ejecutar L2–L4.

---

## 4. Modelo de datos (conceptual)

### 4.1 Entidades principales

| Entidad | Propósito |
|---------|-----------|
| **HealthCheckDefinition** | Catálogo: `code`, dominio, nivel, frecuencia, peso score, severidad default, auto_fix_allowed |
| **HealthCheckRun** | Ejecución: timestamp, scope, duration_ms, status (`pass`/`warn`/`fail`/`error`), payload JSON evidencia |
| **HealthScoreSnapshot** | Score compuesto: scope, score 0–100, breakdown por dominio, operational_status |
| **HealthIncident** | Problema activo: type, severity, opened_at, acked_at, resolved_at, fingerprint (dedup) |
| **HealthRemediationLog** | Acción automática o manual: check_code, action, before/after, user/system |
| **HealthReport** | Reporte diario/semanal: HTML/PDF metadata, recipients, summary |
| **HealthSubscription** | Quién recibe qué: tenant owner email, Ribersoft ops, canales |

### 4.2 Fingerprints y deduplicación

Cada incidencia usa fingerprint estable, por ejemplo:

`{tenant_id}:{branch_id}:SETTLEMENTS_PENDING_ON_CLOSED_SHIFT`

Si el mismo fingerprint está OPEN, no crear duplicado; actualizar `last_seen_at` y evidencia.

### 4.3 Retención

| Tipo | Retención sugerida |
|------|-------------------|
| `health_check_runs` raw | 7 días |
| Agregados horarios | 90 días |
| `health_incidents` resueltos | 1 año |
| `health_reports` | 2 años |
| Evidencia con IDs operativos | Sin datos de tarjetas; solo IDs y montos |

---

## 5. Dominios de salud y catálogo de checks

Cada dominio aporta al **score compuesto** con peso configurable (default abajo). Estado por check: `HEALTHY` | `DEGRADED` | `UNHEALTHY` | `UNKNOWN`.

### 5.1 Infraestructura y hosting

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `INFRA.API_REACHABLE` | GET `/api/v1/health` → 200, `ok:true` | 1 min | L0 | 10 | — |
| `INFRA.PHP_BOOTSTRAP` | GET `health.php` → 200 | 5 min | L0 | 5 | — |
| `INFRA.DB_CONNECTIVITY` | PDO + `SELECT 1` + latency | 1 min | L1 | 15 | — |
| `INFRA.JWT_CONFIGURED` | `jwt.secret` presente | 15 min | L1 | 10 | — |
| `INFRA.APP_ENV_PRODUCTION` | `APP_ENV=production`, `APP_DEBUG=false` en prod | 1 h | L1 | 8 | — |
| `INFRA.STORAGE_LINKED` | `public/storage` → symlink válido | 1 h | L1 | 5 | **Sí** (`storage:link` si permisos OK) |
| `INFRA.CACHE_WRITABLE` | Write/read probe en cache driver | 15 min | L1 | 5 | — |
| `INFRA.QUEUE_WORKER_ALIVE` | Heartbeat job / `queue:work` ping | 5 min | L1 | 8 | **Sí** (supervisor restart vía runbook) |
| `INFRA.SCHEDULER_ALIVE` | `health:scheduler_ping` updated_at < 5 min | 5 min | L1 | 10 | — |
| `INFRA.DISK_SPACE` | Espacio libre > umbral (hosting API o probe) | 1 h | L1 | 5 | — |
| `INFRA.SSL_CERT_EXPIRY` | Certificado > 14 días | 24 h | L1 | 5 | — |
| `INFRA.MIGRATIONS_PENDING` | `migrate:status` sin pending | 24 h | L2 | 8 | — |

**Gap actual:** no hay scheduler Laravel configurado (`routes/console.php` vacío) — Health Center **requiere** cron + worker como prerrequisito de despliegue.

### 5.2 Base de datos e integridad

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `DB.FK_ORPHAN_SAMPLE` | Muestra N huérfanos conocidos (movements, sale_items) | 6 h | L2 | 10 | — |
| `DB.DUPLICATE_SETTLEMENT_TICKETS` | UNIQUE `(tenant, branch, ticket_number)` | 1 h | L2 | 15 | — |
| `DB.SALES_PAYMENT_MISMATCH` | `SUM(sale_payments) = sales.total` | 1 h | L2 | 15 | — |
| `DB.PAID_SETTLEMENT_NO_CASH_MOVEMENT` | PAID sin `cash_movement_id` | 15 min | L2 | 12 | — |
| `DB.INDEX_HEALTH` | Slow query patterns / missing index hints | 24 h | L2 | 5 | — |
| `DB.CONNECTION_POOL` | Conexiones activas vs max | 5 min | L1 | 5 | — |

### 5.3 Secuencias documentales

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `DOCSEQ.LAG_SETTLEMENT` | `last_value` vs max ticket por tenant/branch/period | 15 min | L2 | 15 | **Sí** (reconcile según `DocumentSequenceService`) |
| `DOCSEQ.GAP_DETECTION` | Huecos en numeración (warning only) | 24 h | L3 | 3 | — |
| `DOCSEQ.ROW_MISSING` | Tenant activo sin fila en `document_sequences` | 1 h | L2 | 8 | **Sí** (insert `last_value=0` + reconcile) |

**Lección QA real:** desfase secuencia → 409 en pagos; este check es **crítico de producto**.

### 5.4 JWT, autenticación y permisos

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `AUTH.JWT_SIGN_VERIFY` | Emit + verify token de servicio | 15 min | L1 | 10 | — |
| `AUTH.REFRESH_FLOW` | Smoke test refresh (service account) | 1 h | L1 | 5 | — |
| `AUTH.SUSPENDED_TENANT_TOKENS` | Tokens activos en tenant suspended | 1 h | L2 | 8 | **Sí** (revocación batch — hardening H-30) |
| `AUTH.ROLE_WITHOUT_PERMISSIONS` | Roles operativos con 0 permisos | 24 h | L2 | 5 | — |
| `AUTH.CASHIER_SHIFT_CLOSE_GAP` | Rol cajera sin `shifts.close` pero UI expone cierre | 24 h | L3 | 3 | — |
| `AUTH.USERS_NO_BRANCH` | Usuarios activos sin sucursal | 6 h | L2 | 8 | — |
| `AUTH.BRANCH_TENANT_MISMATCH` | `user.branch_id` fuera de tenant | 6 h | L2 | 10 | — |

### 5.5 Caja

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `CASH.OPEN_SESSION_TTL` | Sesión OPEN > 8h / 14h / 24h escalonado | 15 min | L3 | 10 | — |
| `CASH.MULTIPLE_OPEN_BRANCH` | >1 OPEN por branch (si política single-caja) | 5 min | L3 | 12 | — |
| `CASH.CLOSED_WITHOUT_DECLARED` | CLOSED + `declared_closing_amount` NULL | 1 h | L2 | 12 | — |
| `CASH.LARGE_DIFFERENCE` | \|difference_amount\| > umbral sin notas | 1 h | L3 | 8 | — |
| `CASH.CLOSE_CHECK_BLOCKERS` | Simula close-check por sesión OPEN | 15 min | L3 | 10 | — |
| `CASH.EXPECTED_AMOUNT_STALE` | OPEN sin recalcular expected > 1 h con movimientos | 30 min | L3 | 5 | **Sí** (recalcular expected) |

### 5.6 Turnos

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `SHIFT.OPEN_TTL` | Turno OPEN > 14h / 24h | 15 min | L3 | 10 | — |
| `SHIFT.MULTIPLE_OPEN_BRANCH` | >1 OPEN por branch | 5 min | L3 | 15 | — |
| `SHIFT.ZOMBIE_MANUAL` | Turno manual OPEN > 48h | 1 h | L3 | 8 | — |
| `SHIFT.CLOSURE_CHECK_BLOCKERS` | Blockers en `getShiftClosureCheck` | 15 min | L3 | 10 | — |
| `SHIFT.PENDING_SETTLEMENTS_ON_CLOSE` | PENDING + turno CLOSED (evidencia QA C22) | **15 min** | L3 | **15** | — |
| `SHIFT.AUTO_ROTATION_BLOCKED` | Turno AUTO vencido pero no rotó por blockers | 15 min | L3 | 10 | — |
| `SHIFT.CASH_OPEN_ON_CLOSED_SHIFT` | Inconsistencia shift vs cash_session | 30 min | L2 | 8 | — |

### 5.7 Comandas y ventas

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `ORDER.CHARGEABLE_AGING` | SENT_TO_BAR > 2h | 30 min | L3 | 8 | — |
| `ORDER.OPEN_AGING` | OPEN > 4h en turno activo | 30 min | L3 | 5 | — |
| `ORDER.BILLED_WITHOUT_SALE` | BILLED sin venta | 15 min | L2 | 15 | — |
| `ORDER.SALE_WITHOUT_ITEMS` | Venta sin líneas | 1 h | L2 | 10 | — |
| `ORDER.CANCEL_SPIKE` | Pico cancelaciones vs baseline | 24 h | L4 | 3 | — |

### 5.8 Liquidaciones

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `SETTLE.PENDING_TTL` | PENDING > X horas | 15 min | L3 | 12 | — |
| `SETTLE.SCOPE_MISMATCH` | Owner count ≠ cajera count mismo scope | 30 min | L3 | 8 | — |
| `SETTLE.UNGENERATED_SOURCES` | Fuentes sin generar con turno activo | 30 min | L3 | 10 | — |
| `SETTLE.PENDING_ON_CLOSED_SHIFT` | Alias operativo crítico (ver SHIFT.*) | 15 min | L3 | 15 | — |
| `SETTLE.FINE_APPLIED_ORPHAN` | Multa APPLIED sin settlement | 6 h | L2 | 5 | — |

### 5.9 Impresión y agente

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `PRINT.AGENT_HEARTBEAT` | `last_seen_at` < `agent_online_seconds` | 1 min | L1 | 10 | — |
| `PRINT.JOB_PENDING_TTL` | PENDING > 15 min | 5 min | L3 | 8 | **Sí** (requeue / claim timeout release) |
| `PRINT.JOB_FAILED_RATE` | FAILED hoy > umbral | 15 min | L3 | 10 | — |
| `PRINT.DEVICE_DISABLED` | Device registrado pero disabled | 1 h | L3 | 5 | — |
| `PRINT.STALE_CLAIMED` | Jobs claimed > 10 min sin printed/failed | 5 min | L3 | 8 | **Sí** (release claim) |
| `AGENT.LOCAL_STATUS` | Agente reporta `StateConnected` (push opcional) | 1 min | L1 | 5 | — |
| `AGENT.CONFIG_VALID` | backend_url, device_token presentes | 24 h | L2 | 5 | — |
| `AGENT.PRINTERREACHABLE` | Agente self-test impresora | 15 min | L2 | 8 | — |

### 5.10 Configuración y bootstrap

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `CONFIG.FIRST_NIGHT_INCOMPLETE` | `GetFirstNightChecklistUseCase` incomplete | 1 h | L2 | 10 | — |
| `CONFIG.PAYMENT_METHODS` | Sin CASH habilitado | 6 h | L2 | 8 | — |
| `CONFIG.CASH_EXPENSE_REASON` | Sin motivo egreso liquidaciones | 6 h | L2 | 10 | — |
| `CONFIG.NO_ACTIVE_PRODUCTS` | Catálogo vacío | 24 h | L2 | 5 | — |
| `CONFIG.INSTALL_CHECKLIST` | Platform checklist Ribersoft incompleto | 24 h | L3 | 5 | — |

### 5.11 SaaS, licencias y multitenancy

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `SAAS.TENANT_SUSPENDED` | status suspended | 15 min | L2 | 15 | — |
| `SAAS.SUBSCRIPTION_EXPIRED` | `subscription_ends_at` pasado | 1 h | L2 | 15 | — |
| `SAAS.PLAN_LIMITS` | Usuarios/sucursales/dispositivos > plan | 6 h | L3 | 8 | — |
| `SAAS.BRANCH_DUPLICATE_CODE` | Código sucursal duplicado | 24 h | L2 | 10 | — |
| `SAAS.NO_RECENT_ACTIVITY` | Sin ventas/actividad (existente) | 1 h | L3 | 8 | — |
| `SAAS.DEMO_TENANT_ON_PROD` | Tenant demo en instancia prod | 24 h | L3 | 5 | — |

### 5.12 Frontend y conectividad cliente

| Code | Check | Freq | Nivel | Peso | Auto-fix |
|------|-------|------|-------|------|----------|
| `FE.API_LATENCY` | p95 latencia desde beacon opcional | 5 min | L2 | 5 | — |
| `FE.SSE_CONNECTED` | Owner/caja con SSE activo en horario pico | 15 min | L2 | 5 | — |
| `FE.BUILD_VERSION_DRIFT` | Versión frontend ≠ backend `backend_version` | 24 h | L3 | 5 | — |
| `FE.PWA_OFFLINE_QUEUE` | Cola offline garzón > N items (futuro PWA) | 15 min | L3 | 5 | — |

---

## 6. Motor de scoring

### 6.1 Fórmula compuesta

```
HealthScore = clamp(0, 100, 100 - Σ(penalty_i) - trend_penalty + recovery_bonus)
```

- **penalty_i** = peso del dominio × severidad del peor check activo en ese dominio  
- **Severidad multipliers:** `fail/critical` = 1.0, `warn` = 0.5, `info` = 0.1  
- **trend_penalty:** mismo incidente > 24 h sin resolver → +5 por día (max 20)  
- **recovery_bonus:** +2 si todos los checks L2 pasaron en última hora (opcional)

### 6.2 Estados operativos (alineados SAAS-1.5)

| Score | Estado | Color UI |
|-------|--------|----------|
| 90–100 | `ONLINE` | Verde |
| 75–89 | `WARNING` | Amarillo |
| 50–74 | `DEGRADED` | Naranja |
| 25–49 | `OFFLINE` | Gris |
| 0–24 | `CRITICAL` | Rojo |

### 6.3 Scopes de score

| Scope | Audiencia | Componentes en breakdown |
|-------|-----------|----------------------------|
| **Platform** | Ribersoft | Infra + agregado tenants + agentes + cola global |
| **Tenant** | Owner | Config + licencia + suma branches |
| **Branch** | Supervisor/Cajera | Caja + turno + comandas + liquidaciones + print |
| **Device** | Soporte | Agente individual |

### 6.4 Extensión del analyzer existente

`PlatformOperationsTenantAnalyzer` pasa a consumir **HealthScoreSnapshot** branch-agregado en lugar de calcular inline. Nuevos tipos de issue del QA/hardening se registran en catálogo unificado (evitar lógica duplicada).

---

## 7. Autocorrección (remediación)

### 7.1 Filosofía

Solo acciones que son:

- **Idempotentes**  
- **Reversibles** o de bajo riesgo  
- **Auditadas**  
- **Sin impacto en dinero en tránsito** (no pagar, no cerrar, no cancelar ventas)

### 7.2 Catálogo de auto-fix permitidos (V1)

| Action code | Trigger check | Acción | Aprobación |
|-------------|---------------|--------|------------|
| `REMEDIATION.STORAGE_LINK` | `INFRA.STORAGE_LINKED` fail | Ejecutar storage:link | Automático |
| `REMEDIATION.DOCSEQ_RECONCILE` | `DOCSEQ.LAG_SETTLEMENT` | Reconcile `last_value` | Automático |
| `REMEDIATION.DOCSEQ_INIT_ROW` | `DOCSEQ.ROW_MISSING` | Crear fila secuencia | Automático |
| `REMEDIATION.PRINT_RELEASE_STALE_CLAIM` | `PRINT.STALE_CLAIMED` | Reset claim → PENDING | Automático |
| `REMEDIATION.PRINT_REQUEUE` | `PRINT.JOB_PENDING_TTL` | Increment attempts + requeue | Automático (max 3) |
| `REMEDIATION.CASH_RECALC_EXPECTED` | `CASH.EXPECTED_AMOUNT_STALE` | Recalcular expected | Automático |
| `REMEDIATION.CACHE_CLEAR_CONFIG` | Config corrupta detectada | `config:clear` + cache | Manual Ribersoft |
| `REMEDIATION.REVOKE_SUSPENDED_TOKENS` | `AUTH.SUSPENDED_TENANT_TOKENS` | Blacklist refresh tokens | Automático |

### 7.3 Cola de remediación manual

Acciones propuestas pero **no automáticas** aparecen en “Acciones sugeridas”:

- Cerrar turno zombie (requiere owner)  
- Force-close caja (requiere admin)  
- Cancelar comandas stale (requiere permiso)  
- Reprint batch jobs PENDING  

---

## 8. Sistema de alertas

### 8.1 Severidades

| Nivel | Descripción | Canales default |
|-------|-------------|-----------------|
| **P0 Critical** | Operación bloqueada o integridad financiera | SSE + in-app modal + email Ribersoft + push owner |
| **P1 High** | Cierre de noche en riesgo | SSE banner + email owner |
| **P2 Medium** | Degradación (agente offline, aging orders) | In-app banner |
| **P3 Info** | Informative / tendencia | Health panel only |

### 8.2 Reglas de escalación

```
Incident OPEN (P1+) 
  → 30 min sin ack → re-notify owner
  → 2 h sin ack → Ribersoft ops queue
  → 24 h → CRITICAL escalation + ticket soporte
```

### 8.3 Integración con SSE existente

Nuevo evento: `health.incident.opened` | `health.incident.resolved`  
Payload mínimo: `{ code, severity, message, route_hint }`  
Frontend suscrito refresca Health Strip y banners (caja, liquidaciones).

### 8.4 Canales futuros (V1.1)

- Webhook tenant (Slack/WhatsApp vía integración)  
- Telegram ops Ribersoft  
- SMS solo P0 fuera horario  

---

## 9. Reportes diarios

### 9.1 Reporte Ribersoft (platform)

**Horario:** 06:00 America/La_Paz  
**Contenido:**

- Platform health score + delta vs ayer  
- Top 10 tenants por riesgo  
- Incidentes P0/P1 abiertos/cerrados 24 h  
- Agentes offline > 1 h  
- Auto-remediaciones ejecutadas  
- Checks infra fallidos  
- Recomendaciones accionables  

### 9.2 Reporte Owner (tenant)

**Horario:** 07:00 (configurable)  
**Contenido:**

- Tenant health score  
- Por branch: caja, turno, liquidaciones pendientes, comandas aging  
- Impresión: jobs fallidos / agente offline  
- Config incompleta  
- “Listo para abrir noche”: sí/no + checklist  

### 9.3 Formato y entrega

- HTML email responsive + PDF adjunto opcional  
- Copia en `health_reports` consultable desde UI  
- API: `GET /health-center/reports?from=&to=`  

---

## 10. UI — NightPOS Health Center

### 10.1 Mapa de pantallas

```
Health Center
├── Platform (Ribersoft)          ← evolución Control Center
│   ├── Dashboard global
│   ├── Tenants health list
│   ├── Tenant detail (checks, incidents, timeline)
│   ├── Agents fleet
│   ├── Incidents inbox
│   └── Reports archive
│
├── Tenant (Owner)
│   ├── Health overview (score + dominios)
│   ├── Branches comparison
│   ├── Open incidents + ack
│   └── Daily reports history
│
└── Branch (Supervisor/Cajera)
    ├── Ops Health Strip (compacto)
    ├── Pre-close readiness (link hardening H-11)
    └── Print status chip
```

### 10.2 Componentes UI clave

| Componente | Ubicación | Contenido |
|------------|-----------|-----------|
| **HealthScoreRing** | Dashboard owner | 0–100 animado + estado |
| **DomainHealthGrid** | Tenant overview | 12 dominios con semáforo |
| **IncidentFeed** | Platform + tenant | Timeline con ack/resolve |
| **CheckDetailDrawer** | Platform | Evidencia SQL/API, histórico runs |
| **OpsHealthStrip** | Cajera app bar | Agente, caja TTL, PENDING liq, comandas |
| **ReadinessBanner** | Pre-apertura | First night + infra checks |

### 10.3 Permisos

| Permiso | Rol |
|---------|-----|
| `health.platform.view` | Ribersoft ops (reemplaza/extiende `platform.operations.view`) |
| `health.platform.remediate` | Ribersoft admin |
| `health.tenant.view` | tenant_owner |
| `health.tenant.ack` | tenant_owner, cashier_senior |
| `health.branch.view` | cashier, supervisor |
| `health.reports.view` | tenant_owner |

---

## 11. API (contrato conceptual)

### 11.1 Endpoints platform

```
GET  /api/v1/admin/health-center/platform/summary
GET  /api/v1/admin/health-center/platform/incidents
GET  /api/v1/admin/health-center/platform/tenants
GET  /api/v1/admin/health-center/platform/tenants/{id}
GET  /api/v1/admin/health-center/platform/checks
GET  /api/v1/admin/health-center/platform/checks/{code}/runs
POST /api/v1/admin/health-center/platform/checks/run          # on-demand all or subset
POST /api/v1/admin/health-center/platform/incidents/{id}/ack
POST /api/v1/admin/health-center/platform/remediations/{code}/execute
GET  /api/v1/admin/health-center/platform/reports
```

### 11.2 Endpoints tenant / branch

```
GET  /api/v1/health-center/tenant/summary
GET  /api/v1/health-center/tenant/incidents
GET  /api/v1/health-center/branch/current                   # strip operativo
GET  /api/v1/health-center/branch/readiness                 # pre-noche / pre-cierre
POST /api/v1/health-center/tenant/incidents/{id}/ack
```

### 11.3 Compatibilidad

Rutas SAAS-1.5 `/admin/platform/operations/*` permanecen como **alias deprecated** 6 meses, delegando a Health Center.

---

## 12. Ejecución técnica (scheduler y workers)

### 12.1 Infraestructura requerida

| Proceso | Función |
|---------|---------|
| **cron** `* * * * * php artisan schedule:run` | Orquestador |
| **queue worker** `queue:work --sleep=3` | Checks pesados L2–L4, reportes, remediación |
| **opcional:** worker dedicado `health` queue | Aislar probes de jobs operativos |

### 12.2 Schedule propuesto (extracto)

| Comando | Frecuencia | Cola |
|---------|------------|------|
| `health:probe --level=0` | everyMinute | sync |
| `health:probe --level=1` | everyFiveMinutes | health |
| `health:probe --level=2` | everyFifteenMinutes | health |
| `health:probe --level=3` | everyFifteenMinutes | health |
| `health:score:aggregate` | everyFiveMinutes | health |
| `health:incidents:escalate` | everyTenMinutes | default |
| `health:report:daily` | dailyAt('06:00') | default |
| `health:remediation:process` | everyMinute | health |

### 12.3 Hooks event-driven

Disparar probe targeted tras eventos de dominio:

| Evento | Check disparado |
|--------|-----------------|
| `cash.session.closed` | `CASH.*`, branch score |
| `official_shift.closed` | `SHIFT.*`, `SETTLE.*` |
| `settlement.paid` | `SETTLE.*`, `DOCSEQ.*` |
| `print_job.failed` | `PRINT.*` |
| `tenant.suspended` | `SAAS.*`, `AUTH.*` |

---

## 13. Agente de impresión — extensión health

### 13.1 Push desde agente (V1.1)

Además del heartbeat existente, payload opcional:

```json
{
  "agent_version": "1.2.0",
  "state": "connected",
  "printer_reachable": true,
  "pending_jobs_local": 0,
  "last_error": null,
  "health_checks": {
    "config_valid": true,
    "disk_space_ok": true
  }
}
```

### 13.2 Self-healing agente (local, sin backend)

| Condición | Acción local |
|-----------|--------------|
| N consecutive heartbeat fail | Backoff exponencial (ya parcial) |
| Printer error | Retry + estado tray |
| Config invalid | StateConfigError, no loop infinito |
| Stale job local | Re-fetch pending |

Backend correlaciona agent push + `print_devices.last_seen_at`.

---

## 14. Seguridad y compliance

- Probes **nunca** loguean PINs, tokens completos ni payloads de pago.  
- Evidencia truncada: max 10 IDs por check.  
- Endpoints platform solo Ribersoft; tenant aislado por middleware existente.  
- Reportes email: enlaces firmados expirables.  
- Auto-fix lista blanca revisable en config `health.php`.  
- Probe `APP_DEBUG` en prod → incident P0 visible Ribersoft, oculto a clientes.

---

## 15. Roadmap de implementación (producto)

### Fase H1 — Fundación (4–6 semanas)

- Catálogo checks L0–L1 (infra, health, JWT, DB ping)  
- Tablas registry + scheduler + worker  
- Platform dashboard score global  
- Migrar Control Center a Health Center shell  
- Daily report Ribersoft básico  

### Fase H2 — Operación (4–6 semanas)

- Checks L2–L3: caja, turno, liquidaciones, docseq, print  
- Branch Ops Strip en frontend caja  
- Incident manager + SSE alerts  
- Auto-fix: docseq reconcile, print stale claim, storage link  
- Daily report owner  

### Fase H3 — Madurez (6–8 semanas)

- Hooks event-driven  
- Trend analytics + readiness pre-noche  
- Agente health push  
- Webhooks / escalación  
- Runbooks in-app por incident type  

---

## 16. KPIs del propio Health System

| KPI | Meta |
|-----|------|
| Probe success rate | > 99.5 % |
| False positive rate (incidents) | < 10 % |
| Mean time to detect (P1) | < 5 min |
| Mean time to resolve (con auto-fix) | < 15 min |
| Checks coverage (dominios) | 100 % dominios críticos |
| Platform score correlación con incidentes reales | R > 0.8 (calibrar trimestral) |

---

## 17. Resumen ejecutivo

**NightPOS Health Center** unifica health checks dispersos en un **producto SaaS de observabilidad operativa**: no solo “¿el servidor responde?” sino “¿este local puede completar la noche sin dejar plata o personal colgado?”.

Construye sobre **Control Center SAAS-1.5**, **close-checks** de caja/turno, **first-night checklist** y lecciones del **QA real C22** (liquidaciones en turno cerrado, secuencia documental, agente offline).

Entrega un **porcentaje de salud explicable**, alertas accionables, reportes diarios y autocorrección prudente — el complemento natural del **Hardening V1**, orientado a **detección continua** donde el hardening **previene**.

---

## Documentos relacionados

- `NIGHTPOS_V1_HARDENING_ROADMAP.md`  
- `NIGHTPOS_PRODUCTION_READINESS_AUDIT.md`  
- `backend/SAAS_1_5_PLATFORM_OPERATIONS_IMPLEMENTATION_REPORT.md`  
- `backend/SAAS_5_RIBERSOFT_PLATFORM_AUDIT.md` (§5.8 Observabilidad)  
- `backend/PRINT_AGENT_HEARTBEAT_CONNECTION_RESET_DEEP_AUDIT.md`  

---

**Fin del documento de arquitectura — diseño únicamente, sin código.**
