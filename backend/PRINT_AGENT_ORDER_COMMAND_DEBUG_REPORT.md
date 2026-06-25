# PRINT_AGENT_ORDER_COMMAND_DEBUG_REPORT.md (Backend)

**Fecha:** 2026-06-25  
**Síntoma reportado:** Garzón envía comanda desde celular pero no imprime.

---

## 1. Diagnóstico en base de datos (nigtpos)

### Branches

| id | name | auto_print_order_command |
|----|------|--------------------------|
| 1 | Sucursal Centro | **1 (true)** |

### Print devices

| id | branch_id | name | printer_name | enabled | status | last_seen_at | agent_version |
|----|-----------|------|--------------|---------|--------|--------------|---------------|
| 1 | 1 | prueba | prueba | 1 | ACTIVE | reciente | 1.1.0 |

### Print jobs (últimos 20)

- **ORDER_COMMAND** jobs **sí se crean** (ej. #43 order 18, #37 order 15)
- Estados recientes: **PRINTED** (cadena operativa OK cuando agente activo)
- **PENDING: 0 | CLAIMED: 0 | FAILED: 0** al momento del diagnóstico

**Conclusión:** El pipeline backend **funciona**. El problema reportado no es migración, enum ni query rota.

---

## 2. Cadena ORDER_COMMAND verificada

```
POST /orders/{id}/send-to-bar
  → SendOrderToBarUseCase (marca SENT_TO_BAR)
  → CreateOrderCommandPrintJobUseCase
       Gate 1: auto_print_order_command = true ✓
       Gate 2: hasActiveDevice (ACTIVE + enabled) ✓
       → INSERT print_jobs PENDING, device_id=null
  → Agente poll GET /print-jobs/pending
  → POST claim → WinSpool RAW → POST printed
```

Archivos clave:

| Paso | Archivo |
|------|---------|
| Send | `SendOrderToBarUseCase.php:179` |
| Create job | `CreateOrderCommandPrintJobUseCase.php` |
| Pending | `EloquentPrintJobRepository::listPending` |
| Claim | `ClaimPrintJobUseCase` |

Test automatizado: `LocalPrintAgentTest.php` — crea ORDER_COMMAND al send-to-bar.

---

## 3. Causas probables cuando NO imprime

### Causa A — No se crea print_job

| Condición | Efecto |
|-----------|--------|
| `auto_print_order_command = false` | Use case retorna `null` (silencioso) |
| Sin dispositivo ACTIVE+enabled | Use case retorna `null` (silencioso) |
| Orden ya enviada | No re-dispara send-to-bar |

**Verificar:** Admin → Impresoras → auto impresión ON + al menos 1 dispositivo registrado.

### Causa B — Job PENDING pero agente offline

| Condición | Efecto |
|-----------|--------|
| Servicio apagado | `last_seen_at` null o > 30 s → Offline |
| `backend_url` incorrecto en config.json | Heartbeat falla |
| `device_key` incorrecta | HTTP 401 en heartbeat |
| Firewall / Apache apagado | Sin conexión |

**Verificar:** `POST /print-devices/heartbeat` con Bearer device_key.

### Causa C — Job FAILED

| Condición | Efecto |
|-----------|--------|
| `printer_name` ≠ nombre Windows | WinSpool error |
| Impresora apagada / sin papel | FAILED con last_error |
| Driver incorrecto | RAW rechazado |

En DB actual: `printer_name = "prueba"` — debe ser el nombre **exacto** de la impresora en Windows.

### Causa D — Job CLAIMED stuck

Si el agente crashea post-claim, el job queda CLAIMED y no reaparece en pending. **Mitigación futura:** timeout reclaim (no implementado en V1).

---

## 4. API agente (equivalente a ping)

No existe `/print-agent/ping`. Usar:

```bash
curl -X POST http://nightpos.test/api/v1/print-devices/heartbeat \
  -H "Authorization: Bearer npd_live_..." \
  -H "Content-Type: application/json" \
  -d "{\"printer_name\":\"POS-80\",\"agent_version\":\"2.0.0\"}"
```

Respuesta 200 → device online en admin (< 30 s).

---

## 5. Mejoras aplicadas (2026-06-25)

| Cambio | Archivo |
|--------|---------|
| Resumen cola (pending/failed/last) en settings | `GetPrintSettingsUseCase`, `EloquentPrintJobRepository` |
| Test print admin | `CreateTestPrintJobUseCase`, `POST /print-devices/{id}/test-print` |
| Tipo TEST | `PrintJobType::Test` |

Sin cambios a reglas de comanda, liquidaciones, caja ni CBA.

---

## 6. Checklist debug operativo

```sql
-- Jobs recientes
SELECT id, type, source_id, status, last_error, device_id, created_at, printed_at
FROM print_jobs ORDER BY id DESC LIMIT 20;

-- Settings sucursal
SELECT id, name, auto_print_order_command FROM branches;

-- Dispositivos
SELECT id, branch_id, name, printer_name, enabled, status, last_seen_at, last_error
FROM print_devices;
```

```powershell
# Agente consola sin imprimir
NightPOSPrintAgent.exe --dry-run

# Logs
notepad C:\ProgramData\NightPOS\PrintAgent\logs\agent.log
```

---

## 7. Resultado

| Check | Estado |
|-------|--------|
| ORDER_COMMAND se crea | ✅ Confirmado en DB |
| Agente toma jobs | ✅ Jobs PRINTED con device_id=1 |
| Device online | ✅ last_seen reciente |
| Pipeline tests | ✅ LocalPrintAgentTest |

El incidente típico en sucursal será **config agente** (URL/key/printer) o **agente no instalado como servicio**, no bug de SendOrderToBar.
