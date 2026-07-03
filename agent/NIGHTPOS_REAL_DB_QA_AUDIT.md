# NightPOS — QA con base real del hosting (Print Agent)

**Fecha:** 2026-06-30  
**Base:** MySQL `nigtpos` + config local agente  
**Alcance:** Diagnóstico — sin cambios

---

## Dispositivos en base real

| id | name | branch_id | tenant | status | last_seen_at |
|----|------|-----------|--------|--------|--------------|
| 1 | CAJA | 2 (C22) | 2 | ACTIVE | 2026-06-30 **08:40:26** |
| 2 | CAJA | 3 (R) | 3 | ACTIVE | 2026-06-30 **08:40:25** |

Ambos dispositivos registrados. **Sin `last_error` en DB.**

---

## print_jobs — estado real

| Métrica | Valor |
|---------|-------|
| Total | 105 |
| PRINTED | 104 |
| PENDING | **1** |
| FAILED | 0 |

**Job pendiente:**

| id | type | source | created_at |
|----|------|--------|------------|
| **105** | SETTLEMENT_PAYMENT | staff_settlement (pago ~09:16) | 2026-06-30 09:16:15 |

`last_seen_at` dispositivo C22 = **08:40** → agente **apagado/desconectado** ~36 min antes del job pendiente.

---

## Config agente local (`agent/config.json`)

| Parámetro | Valor | Riesgo |
|-----------|-------|--------|
| backend_url | `http://nightpos.test/api/v1` | OK dev |
| poll_interval_ms | **100** | **P2** — muy agresivo vs prod 15s |
| dry_run | false | Imprime real |

---

## Comportamiento esperado vs observado

| Escenario | Esperado | Evidencia base real |
|-----------|----------|---------------------|
| Pago liquidación sin agente | **No bloquea pago** | Settlement 4 PAID 09:16 + job 105 PENDING |
| Ticket settlement impreso | PRINTED tras poll | Job 105 quedó PENDING |
| Jobs FAILED acumulados | 0 | OK |
| Agente caído bloquea caja | No | OK — pagos registrados |

---

## Tabla de hallazgos

| Problema | Sev. | Evidencia | Cómo reproducir | Recomendación |
|----------|------|-----------|-----------------|---------------|
| print_job 105 PENDING sin imprimir | **P2** | job 09:16, device seen 08:40 | Pagar liquidación con agente off | Reencolar/reprint; levantar agente |
| poll_interval 100ms en config local | **P3** | agent/config.json | Arrancar agente | Usar ≥15000 ms en prod |
| Sin jobs FAILED | — | count=0 | — | OK |
| Heartbeat stale | **P2** | last_seen >30 min vs último pago | Apagar agente, pagar liq. | Monitoreo SAAS Control Center |

---

## Checklist agente manual

- [ ] Levantar `NightPOSPrintAgent.exe` apuntando a base local/hosting
- [ ] Verificar heartbeat actualiza `print_devices.last_seen_at`
- [ ] Job 105 pasa a PRINTED o reprint settlement id=4
- [ ] Pagar nueva liquidación → job nuevo → imprime sin bloquear API
- [ ] Simular reset conexión — backoff HTTP/1.1 (fix previo agente)

---

## Conclusión agente

Impresión **no bloquea operación** (correcto). Hay **1 ticket de liquidación sin imprimir** por agente desconectado. No hay cola FAILED acumulada.

Ver backend: `backend/NIGHTPOS_REAL_DB_QA_AUDIT.md`
