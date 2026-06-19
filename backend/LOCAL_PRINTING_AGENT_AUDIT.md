# Auditoría / Diseño — Impresión local automática (Backend)

**Fecha:** 2026-06-17  
**Tipo:** Auditoría + diseño arquitectónico  
**Estado:** **NO IMPLEMENTADO** — sin migraciones ni código en esta entrega  
**Contexto:** NightPOS en hosting web; impresora USB en PC principal de cada sucursal; garzones en celular.

---

## 1. Estado actual

| Componente | Estado real en código |
|------------|----------------------|
| Tabla `print_jobs` | ❌ No existe (solo en `DATABASE_GUIDELINES.md`, `MIGRATION_PLAN.md`) |
| Tabla `print_devices` / `printers` | ❌ No existe |
| Rutas `/api/v1/print-jobs/*` | ❌ No en `routes/api.php` |
| Bounded context Printing | ⚠️ Placeholder (`PrintJobRepositoryInterface` vacío, `.gitkeep`) |
| Enum `PrintJobStatus` | ✅ Existe (`PENDING`, `PRINTING`, `PRINTED`, `FAILED`, `CANCELLED`) |
| Creación job al enviar barra | ❌ `SendOrderToBarUseCase` solo emite SSE `order.sent_to_bar` |
| Creación job al cobrar | ❌ No |
| Agente local | ❌ No existe carpeta/agente en repo |
| V1-97 tickets | ✅ Vista imprimible frontend + `window.print()` |
| API datos ticket | ✅ `GET /orders/{id}`, `/precheck`, `/sales/{id}`, etc. |

**Conclusión:** La documentación legacy (`API_DOCUMENTATION.md`, `DOMAIN_DESIGN.md`) describe un **diseño aspiracional**. La **implementación V1 actual** es impresión manual vía navegador (`PRINTABLE_TICKETS_V1_REPORT.md` — explícitamente **sin print_jobs**).

---

## 2. Limitación técnica (hosting / USB / celular)

```
Garzón (celular, Chrome/Safari)
        ↓ HTTPS
Servidor NightPOS (hosting cloud)
        ✗ no accede a USB de la sucursal

PC sucursal (USB → impresora térmica)
        ↑ requiere software local
Agente de impresión
```

| Actor | ¿Puede imprimir USB local? |
|-------|----------------------------|
| Navegador del celular | ❌ No (otra máquina) |
| Backend en hosting | ❌ No |
| Agente en PC sucursal | ✅ Sí |
| `window.print()` en PC | ⚠️ Manual, no automático desde celular |

**Regla:** El hosting **solo encola** `print_jobs` con contenido listo; **nunca** imprime.

---

## 3. Arquitecturas posibles

| Opción | Descripción | V1 NightPOS |
|--------|-------------|-------------|
| **A — Agente pull (polling)** | PC consulta jobs pendientes cada 1–2 s | ✅ **Recomendado V1** |
| **B — Agente push (WebSocket/SSE)** | Servidor notifica al agente | V1.1 |
| **C — QZ Tray / browser bridge** | Certificados + JS en navegador | ❌ No resuelve celular→PC sin PC abierta con QZ |
| **D — Print Server OS (CUPS)** | Cola Linux | ❌ Windows típico en boliches BO |
| **E — Email/PDF a PC** | Lento, no operativo | ❌ |
| **F — Bluetooth desde celular** | Impresora BT en garzón | ❌ Fuera de alcance (impresora en PC) |

---

## 4. Recomendación V1

### Arquitectura

**Agente local Windows + polling + device_key + cola en backend.**

| Decisión | Elección | Motivo |
|----------|----------|--------|
| Agente runtime | **Node.js** empaquetado `.exe` (pkg/nexe) | ESC/POS maduro, ligero vs Electron, rápido de iterar |
| Transporte V1 | **Polling 1,5 s** | Simple, funciona detrás de NAT/firewall, sin WebSocket persistente |
| Formato ticket | **`content_text` generado en backend** | Agente tonto; una sola fuente de verdad; reimpresión idéntica |
| Encoding impresión | **ESC/POS sobre texto preformateado** o raw bytes en `content_escpos` base64 V1.1 | 80 mm default |
| Auth agente | **`device_key`** (Bearer), no usuario | Principio mínimo privilegio |
| Multisucursal | `device_key` → `tenant_id` + `branch_id` fijos | Aislamiento estricto |
| Anti-doble | Claim atómico + idempotencia por `(source_type, source_id, type, copy)` | Ver §8 |
| Auto impresión comanda | Al **`SendOrderToBarUseCase`** si `branch.auto_print_order_command=true` | Barra necesita ticket al enviar |
| Venta/cierre auto | **Manual o V1.1** (configurable) | Priorizar comanda barra |

**Node vs Python vs Electron vs QZ:**

| | Node | Python | Electron | QZ Tray |
|---|------|--------|----------|---------|
| Peso | Bajo | Medio | Alto | Medio (dep externa) |
| ESC/POS | ✅ | ✅ | ✅ | ✅ |
| UI config | CLI/json V1 | CLI | ✅ Nativa | Certificados |
| Updates | Manual V1 | Manual | auto-updater | Externo |
| **V1** | **✅ Elegido** | Alternativa | V1.1 panel | No (complejidad certs + no agente propio) |

---

## 5. Respuestas a las 17 preguntas

| # | Pregunta | Respuesta V1 |
|---|----------|--------------|
| 1 | ¿Arquitectura V1? | Agente Windows Node + cola `print_jobs` + polling |
| 2 | ¿Polling o SSE/WS? | **Polling** V1; SSE push al agente en V1.1 |
| 3 | ¿Dónde se crean jobs? | Use cases: `SendOrderToBar`, `ChargeOrder`, `CreateDirectSale`, `CloseCashSession`, endpoints manuales precheck/reprint |
| 4 | ¿Qué eventos auto? | **V1:** `ORDER_COMMAND` al send-to-bar (si config). **Opcional V1:** `SALE_RECEIPT` al cobrar. Resto manual o V1.1 |
| 5 | ¿Qué ticket al comandar? | **Al enviar a barra** (`SENT_TO_BAR`), no al crear borrador OPEN |
| 6 | ¿Todo o solo si auto_print? | Solo si sucursal/dispositivo tiene `auto_print_*` activo |
| 7 | ¿Reimpresión? | `POST /print-jobs` o `/orders/{id}/reprint` → nuevo job `PENDING` |
| 8 | ¿Evitar doble impresión? | Unique parcial + estado CLAIMED atómico + agente ack una vez |
| 9 | ¿PC apagada? | Jobs quedan `PENDING`; al encender agente imprime backlog (con límite edad configurable) |
| 10 | ¿Sin papel? | Agente → `FAILED` + `last_error`; reintento manual |
| 11 | ¿Sin internet? | Cola local agente opcional V1.1; V1: jobs pendientes en server hasta reconexión |
| 12 | ¿Cajera ve fallo? | SSE `print_job.failed` + badge en detalle comanda + lista jobs admin |
| 13 | ¿Asignar impresora a sucursal? | Admin registra `print_device` con `branch_id` |
| 14 | ¿Varias impresoras? | V1: un device **primario** por sucursal; `destination` en job V1.1 (BAR/KITCHEN) |
| 15 | ¿Proteger device_key? | Hash en DB; mostrar una vez al crear; rotación; rate limit |
| 16 | ¿Auditoría? | `requested_by_user_id` + `print_jobs` inmutable + audit log |
| 17 | ¿Test sin impresora? | Agente modo `--dry-run` escribe a archivo; tests backend con fake printer port |

---

## 6. Modelo de datos

### 6.1 `print_devices`

```sql
print_devices
  id                  BIGINT PK
  tenant_id           FK NOT NULL
  branch_id           FK NOT NULL
  name                VARCHAR(100)      -- "PC Barra Centro"
  device_key_hash     VARCHAR(255)      -- bcrypt/argon2 del secret
  device_key_prefix   VARCHAR(12)       -- "npd_abc..." para identificar
  status              VARCHAR(20)       -- ACTIVE, DISABLED, REVOKED
  enabled             BOOLEAN DEFAULT true
  printer_name        VARCHAR(255) NULL -- nombre Windows spooler
  paper_width_mm      SMALLINT DEFAULT 80 -- 58 o 80
  auto_print_order    BOOLEAN DEFAULT true
  auto_print_sale     BOOLEAN DEFAULT false
  auto_print_cash_close BOOLEAN DEFAULT false
  last_seen_at        TIMESTAMP NULL
  last_error          TEXT NULL
  agent_version       VARCHAR(40) NULL
  created_at, updated_at

UNIQUE (tenant_id, branch_id, name)
INDEX (tenant_id, branch_id, status)
```

**Regla:** Un `device_key` plaintext se muestra **una sola vez** al registrar (patrón API key).

### 6.2 `print_jobs`

```sql
print_jobs
  id                      BIGINT PK
  tenant_id               FK NOT NULL
  branch_id               FK NOT NULL
  device_id               FK NULL           -- asignado al claim
  type                    VARCHAR(40)       -- ver tipos abajo
  source_type             VARCHAR(40)       -- order, sale, cash_session, shift, room_service
  source_id               BIGINT NOT NULL
  idempotency_key         VARCHAR(64) NULL  -- evita duplicados
  payload                 JSON NOT NULL     -- datos estructurados
  content_text            TEXT NOT NULL     -- ticket pre-renderizado 80 cols
  content_escpos_base64   TEXT NULL         -- V1.1 opcional
  status                  VARCHAR(20)       -- PENDING, CLAIMED, PRINTED, FAILED, CANCELLED
  priority                SMALLINT DEFAULT 0
  attempts                INT DEFAULT 0
  max_attempts            INT DEFAULT 3
  last_error              TEXT NULL
  requested_by_user_id    FK users NULL
  claimed_at              TIMESTAMP NULL
  printed_at              TIMESTAMP NULL
  failed_at               TIMESTAMP NULL
  created_at, updated_at

INDEX (tenant_id, branch_id, status, created_at)
INDEX (device_id, status)
UNIQUE (tenant_id, branch_id, idempotency_key) WHERE idempotency_key IS NOT NULL
```

**Tipos V1:**

| type | Trigger |
|------|---------|
| `ORDER_COMMAND` | Send to bar (auto) / reprint manual |
| `PRECHECK` | Manual garzón/cajera |
| `SALE_RECEIPT` | Charge / direct sale (opcional auto) |
| `CASH_CLOSE` | Close cash session (opcional V1.1) |
| `SHIFT_CLOSE` | Close shift (V1.1) |
| `SETTLEMENT` | V1.1 |
| `ROOM_SERVICE` | V1.1 |

**Estados:**

```
PENDING → CLAIMED → PRINTED
                 ↘ FAILED (reintento → PENDING si attempts < max)
PENDING → CANCELLED (admin)
```

Nota: enum existente usa `PRINTING`; alinear a **`CLAIMED`** (más claro para lock) o mapear `PRINTING` = `CLAIMED`.

### 6.3 Config sucursal (opcional)

```sql
branch_print_settings (o columnas en branches)
  auto_print_order_command  BOOLEAN DEFAULT true
  auto_print_sale_receipt   BOOLEAN DEFAULT false
  max_job_age_hours         INT DEFAULT 24
  allow_reprint             BOOLEAN DEFAULT true
```

---

## 7. Endpoints propuestos

### Autenticación agente

Header: `Authorization: Bearer npd_live_xxxxx`  
Middleware: `print-device` (distinct de `auth:api` JWT usuario)

| Método | Ruta | Actor | Descripción |
|--------|------|-------|-------------|
| POST | `/print-devices/register` | Admin JWT | Crea device, devuelve `device_key` once |
| GET | `/print-devices` | Admin | Lista devices sucursal |
| PATCH | `/print-devices/{id}` | Admin | enable/disable, nombre |
| POST | `/print-devices/{id}/rotate-key` | Admin | Nueva key |
| GET | `/print-devices/me` | Device | Info device + config |
| POST | `/print-devices/heartbeat` | Device | last_seen, printer_name, agent_version |

### Cola

| Método | Ruta | Actor | Descripción |
|--------|------|-------|-------------|
| GET | `/print-jobs/pending` | Device | Jobs `PENDING` branch, limit 10, FIFO |
| POST | `/print-jobs/{id}/claim` | Device | PENDING→CLAIMED atómico |
| POST | `/print-jobs/{id}/printed` | Device | CLAIMED→PRINTED |
| POST | `/print-jobs/{id}/failed` | Device | →FAILED, attempts++ |
| POST | `/print-jobs/{id}/cancel` | Admin | CANCELLED |

### Operación (usuarios)

| Método | Ruta | Actor | Descripción |
|--------|------|-------|-------------|
| POST | `/print-jobs` | User | Crear job manual (precheck, reprint) |
| POST | `/orders/{id}/reprint` | User | Atajo ORDER_COMMAND |
| GET | `/print-jobs` | Admin | Historial + filtros |
| GET | `/orders/{id}/print-status` | User | Último job comanda |

---

## 8. Seguridad

| Regla | Implementación |
|-------|----------------|
| Agente sin credenciales humanas | Solo `device_key` |
| Scope | Device solo lee/ack jobs de **su** `tenant_id` + `branch_id` |
| Key storage | Hash; prefix para admin UI |
| Rotación | Admin revoca + genera nueva |
| Rate limit | heartbeat 1/min; pending 1/s por device |
| No cobrar/modificar | Middleware sin permisos operativos |
| HTTPS obligatorio | Prod |
| Audit | `requested_by_user_id`, IP agente en heartbeat |

---

## 9. Flujo multisucursal

```
Tenant Casa22
├── Branch Centro → Device Key A → Agente PC Centro → Impresora USB Centro
└── Branch Norte  → Device Key B → Agente PC Norte  → Impresora USB Norte

Job (branch_id=Centro) → solo visible para Key A
```

Validación en **cada query**: `WHERE tenant_id = :device.tenant AND branch_id = :device.branch`.

---

## 10. Formato ticket

### Recomendación V1: **`content_text` backend**

| Enfoque | Pros | Contras |
|---------|------|---------|
| Backend `content_text` | Consistente, reimpresión idéntica, agente simple | Ancho fijo en server |
| Agente renderiza JSON | Flexible | Duplicar lógica Vue/backend |
| HTML/PDF | Bonito | Lento, no térmica directa |

**Implementación:** nuevo `PrintTicketContentBuilder` (Application/Printing):

- Reutiliza datos de `OrderPresentationService` / `GetOrderPrecheckUseCase`.
- Líneas 42–48 chars (58 mm) o 48 chars (80 mm) según `paper_width_mm`.
- Incluye: número comanda, mesa, garzón, ítems, qty, modalidad, chica/manillas combo, notas, total, fecha.
- ESC/POS: agente envía texto + comandos cut (`\x1dV\x00`) en V1.

**Ejemplo `content_text`:**

```
======== NIGHTPOS ========
COMANDA BAR — #0152
Mesa: 4 · Salón Principal
Garzón: Carlos
16/06/2026 23:42
------------------------
2x Paceña        SOLO
1x Combo 6 Cer     ACOMP
   Manillas: Ana×4, Bea×2
------------------------
NOTA: Sin hielo
========================
```

---

## 11. Dónde crear print_jobs (hooks)

| Evento | Use case | type | Condición |
|--------|----------|------|-----------|
| Enviar a barra | `SendOrderToBarUseCase` | `ORDER_COMMAND` | `auto_print_order_command` |
| Cobrar comanda | `ChargeOrderUseCase` | `SALE_RECEIPT` | `auto_print_sale_receipt` |
| Venta directa | `CreateDirectSaleUseCase` | `SALE_RECEIPT` | idem |
| Cerrar caja | `CloseCashSessionUseCase` | `CASH_CLOSE` | V1.1 o config |
| Precheck manual | `POST /print-jobs` | `PRECHECK` | Usuario explícito |
| Reimpresión | `POST /orders/{id}/reprint` | `ORDER_COMMAND` | Permiso + nuevo idempotency |

**Idempotency keys:**

- Auto send-to-bar: `order_command:{order_id}:v1` (reprint usa `:reprint:{uuid}`)
- Sale: `sale_receipt:{sale_id}`

**No crear job:**

- Crear comanda OPEN
- Agregar ítems
- Precuenta sin botón
- Cancelar comanda no cobrada

---

## 12. Reimpresión

1. Usuario pulsa «Reimprimir comanda» (garzón/cajera/admin).
2. Backend valida permiso `printing.reprint` o `orders.access`.
3. Crea **nuevo** job con `idempotency_key` único (uuid).
4. Agente imprime en siguiente poll.
5. Historial muestra N jobs para mismo `order_id`.

---

## 13. Manejo de errores

| Escenario | Comportamiento V1 |
|-----------|-------------------|
| PC apagada | Jobs acumulan `PENDING`; imprimir al volver (max age 24h) |
| Sin papel | `FAILED`, error «paper out»; notificación UI |
| Impresora offline | `FAILED` tras timeout; reintento hasta `max_attempts` |
| Sin internet agente | Server retiene jobs; agente no poll → mismo efecto PC apagada |
| Job stale | Cron marca `CANCELLED` si > `max_job_age_hours` |
| Doble claim | Segundo claim 409 Conflict |

**SSE:** emitir `print_job.created`, `print_job.failed` para UI cajera.

---

## 14. Plan por fases

| Fase | Alcance | Días est. |
|------|---------|-----------|
| **PRINT-1** | Migraciones, repos, device auth, CRUD devices | 2–3 |
| **PRINT-2** | `PrintTicketContentBuilder`, tests contenido | 2 |
| **PRINT-3** | Hook `SendOrderToBar` + endpoints pending/claim/printed/failed | 2–3 |
| **PRINT-4** | Agente Node.js MVP (polling, ESC/POS, dry-run) | 3–4 |
| **PRINT-5** | Admin API + permisos + reprint + precheck job | 2 |
| **PRINT-6** | Frontend admin devices + failure UX | 2–3 |
| **PRINT-1.1** | SSE push agente, auto_print sale, cola offline agente | 3–4 |
| **PRINT-2.0** | Multi-impresora BAR/KITCHEN, Electron config UI | 1–2 sem |

**Total V1 mínimo (comanda auto):** ~11–15 días dev + QA hardware.

---

## 15. Qué entra en V1

- `print_devices` + `print_jobs`
- Device key auth
- Polling agente Windows (Node)
- `ORDER_COMMAND` auto en send-to-bar (configurable)
- `PRECHECK` + reprint manual
- `content_text` 80 mm
- Admin: registrar device, ver online/offline, historial jobs
- Anti-doble impresión
- Failed → visible en UI
- Tests backend (creación job, claim, tenant isolation, idempotency)

## 16. V1.1 / V2

| V1.1 | V2 |
|------|-----|
| SSE/WebSocket al agente | Multi destino BAR/KITCHEN/CASHIER |
| `SALE_RECEIPT` auto | Electron tray app |
| Cola offline en agente | Impresión por categoría producto |
| `content_escpos` nativo | Cloud print monitoring multi-tenant |
| Auto-update agente | Integración peso/balanza |

---

## 17. Tests backend planificados

1. Send-to-bar crea job si auto_print true
2. Send-to-bar no crea si auto_print false
3. Idempotency evita duplicado mismo order
4. Device solo ve jobs de su branch
5. Claim atómico — segundo claim falla
6. Printed solo desde CLAIMED
7. Failed incrementa attempts
8. Reprint crea nuevo job
9. content_text incluye allocations combo
10. Tenant isolation cross-branch
11. Device key inválido → 401
12. Precheck manual crea PRECHECK job

---

## 18. Compatibilidad con impresión actual

**No tocar** rutas `/nightpos/print/*` ni `window.print()` en V1 agente.

Conviven:

- **Automático:** agente local (PC barra)
- **Manual:** cualquier dispositivo con navegador → vista imprimible

---

*Documento de diseño. Implementación por fases PRINT-1…6 cuando se apruebe.*
