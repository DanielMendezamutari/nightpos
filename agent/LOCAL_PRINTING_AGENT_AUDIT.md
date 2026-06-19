# Auditoría / Diseño — Agente local de impresión NightPOS

**Fecha:** 2026-06-17  
**Ubicación futura:** `agent/nightpos-print-agent/` (no creado aún)  
**Estado:** **NO IMPLEMENTADO**

---

## 1. Rol del agente

Programa **Windows** en la PC principal de cada sucursal que:

1. Se autentica con `device_key` (sin usuario/contraseña).
2. Consulta trabajos pendientes del backend NightPOS.
3. Imprime en impresora USB/local configurada.
4. Confirma éxito o reporta error.
5. Envía heartbeat (online/offline).

```
┌─────────────┐     HTTPS      ┌──────────────────┐
│ Garzón      │ ──────────────►│ NightPOS hosting │
│ (celular)   │   create order │ print_jobs queue │
└─────────────┘                └────────┬─────────┘
                                        │ GET pending
                                        ▼
                               ┌──────────────────┐
                               │ Agente Windows   │
                               │ (PC sucursal)    │
                               └────────┬─────────┘
                                        │ USB
                                        ▼
                               ┌──────────────────┐
                               │ Impresora térmica│
                               │ 58/80 mm ESC/POS │
                               └──────────────────┘
```

---

## 2. Comparativa tecnologías (decisión V1)

| Opción | Veredicto V1 |
|--------|--------------|
| **A — Node.js** | ✅ **Elegido** |
| B — Python | ✅ Alternativa válida; empaquetado más pesado |
| C — Electron | V1.1 si se necesita UI rica instalador |
| D — QZ Tray | ❌ Dependencia externa, certs, no ownership cola |

### Por qué Node.js V1

- Librerías: `node-thermal-printer`, `escpos`, `printer` (Windows spooler).
- Empaquetado: `pkg` → single `.exe` ~40–60 MB.
- Polling HTTP simple con `axios` / `fetch`.
- Mismo lenguaje que muchos equipos dev frontend.
- `--dry-run` trivial (write file).

### Stack propuesto

```
nightpos-print-agent/
├── src/
│   ├── index.ts           # entry, tray opcional V1.1
│   ├── config.ts          # config.json loader
│   ├── api-client.ts      # pending, claim, printed, failed, heartbeat
│   ├── printer/
│   │   ├── windows-spool.ts
│   │   └── escpos.ts
│   ├── loop.ts            # poll scheduler
│   └── logger.ts
├── config.example.json
├── package.json
└── README.md
```

---

## 3. Configuración local (`config.json`)

```json
{
  "api_base_url": "https://api.nightpos.example.com/api/v1",
  "device_key": "npd_live_xxxxxxxxxxxxxxxx",
  "poll_interval_ms": 1500,
  "printer_name": "EPSON TM-T20",
  "paper_width_mm": 80,
  "dry_run": false,
  "dry_run_output_dir": "C:\\NightPOS\\print-out",
  "max_jobs_per_poll": 5,
  "log_level": "info"
}
```

**Primera instalación (V1):**

1. Admin crea device en web → copia key.
2. Operador descarga `NightPosPrintAgent.exe` + `config.example.json`.
3. Edita config (Notepad) o installer V1.1.
4. Ejecuta agente → Windows Task Scheduler «At startup» o servicio NSSM.

---

## 4. Loop principal

```
every poll_interval_ms:
  POST /print-devices/heartbeat
  GET  /print-jobs/pending?limit=5
  for each job (FIFO):
    POST /print-jobs/{id}/claim
    try:
      print(job.content_text)
      POST /print-jobs/{id}/printed
    catch err:
      POST /print-jobs/{id}/failed { error: err.message }
      if paper out → stop batch (optional)
```

**Backoff:** si API error 5xx → intervalo x2 hasta 30 s max.

---

## 5. Impresión ESC/POS

### V1: texto plano + cut

```javascript
// Pseudocódigo
buffer = iconv.encode(content_text, 'cp437') // o UTF-8 según impresora
buffer += ESC + 'd' + '\x03'  // feed 3 lines
buffer += GS + 'V' + '\x00'   // cut
spooler.write(printer_name, buffer)
```

### Ancho

| Papel | Chars/línea `content_text` |
|-------|----------------------------|
| 58 mm | ~32 |
| 80 mm | ~48 |

Backend genera según `print_device.paper_width_mm`.

### V1.1

- `content_escpos_base64` para logos, negrita, QR.

---

## 6. Polling vs WebSocket (pregunta 2)

| | Polling V1 | WebSocket V1.1 |
|---|------------|----------------|
| Latencia | 1–2 s | <500 ms |
| Infra | Ninguna extra | WS server / Laravel Reverb |
| NAT/firewall | ✅ Siempre funciona | Puede complicar |
| Batería PC | Irrelevante (enchufada) | — |

**V1: polling 1500 ms.** Suficiente para barra (garzón ya espera confirmación UI).

**V1.1:** endpoint `GET /print-devices/stream` o MQTT — agente mantiene polling como fallback.

---

## 7. Seguridad agente

| Regla | Implementación |
|-------|----------------|
| Key en config | Permisos archivo restringidos; no loguear key |
| TLS | Validar certificado HTTPS |
| Scope | API responde solo jobs del branch del device |
| Revocación | 401 → agente muestra «Key inválida — contactar admin» y stop |
| Updates | V1 manual; V1.1 check version en heartbeat |

---

## 8. Escenarios error (preguntas 9–11)

| Escenario | Agente | Backend/UI |
|-----------|--------|------------|
| PC apagada | — | Jobs PENDING acumulan |
| PC enciende | Poll → imprime backlog | — |
| Sin internet | Log error, retry backoff | Jobs PENDING |
| Sin papel | failed «paper out» | Notificación cajera |
| Impresora apagada | failed timeout | Reintento |
| Job corrupto | failed | Admin ve error |
| Dos agentes misma key | Solo uno claim exitoso | Segundo 409 |

---

## 9. Evitar doble impresión (pregunta 8)

1. Backend: claim atómico `UPDATE ... WHERE status=PENDING`.
2. Agente: tras `printed`, no reprocess mismo id (cache local ids 24h opcional).
3. Idempotency key en auto jobs.

---

## 10. Desarrollo sin impresora (pregunta 17)

| Modo | Comportamiento |
|------|----------------|
| `--dry-run` | Escribe `job_{id}_{timestamp}.txt` en carpeta |
| `--stdout` | Imprime en consola |
| Impresora PDF virtual | «Microsoft Print to PDF» como `printer_name` |
| Tests CI | Mock spooler interface |

---

## 11. Multisucursal (agente)

- **Un agente = una sucursal** (key ligada a branch).
- Empresa con 3 sucursales = 3 PCs = 3 configs = 3 keys.
- No soportar multi-branch en un agente V1 (evita errores operativos).

---

## 12. Varias impresoras por sucursal (pregunta 14)

**V1:** Un `printer_name` en config — todo va a esa impresora.

**V1.1:**

- Múltiples `print_devices` same branch con `destination` (BAR, KITCHEN).
- Router en backend asigna job a device por reglas producto/categoría.
- Agente solo poll su cola (device_id filter).

---

## 13. Plan implementación agente

| Paso | Entregable |
|------|------------|
| 1 | Scaffold Node + config loader |
| 2 | API client + auth header |
| 3 | Poll loop + claim/printed/failed |
| 4 | Windows spooler print |
| 5 | dry-run + logging |
| 6 | pkg build → exe |
| 7 | README instalación sucursal |
| 8 | QA impresora EPSON/BStar real |

**Estimado:** 3–4 días dev + 1 día QA hardware.

---

## 14. V1 vs V1.1 vs V2 (agente)

| V1 | V1.1 | V2 |
|----|------|-----|
| CLI + config.json | Tray icon Electron-lite | Instalador MSI |
| Polling | WebSocket + polling fallback | |
| Text + cut | ESC/POS rich | |
| Manual update | Auto-update github releases | |
| Single printer | Multi-device same PC | |
| Windows only | Windows + Linux (CUPS) opcional | |

---

## 15. Entregables cuando se implemente

- `NightPosPrintAgent.exe`
- `config.example.json`
- `INSTALACION_SUCURSAL.md` (1 página operador)
- Checksum / versión en heartbeat

---

## 16. Recomendación final

**Prioridad producto:** Implementar agente **antes** o **en paralelo** con kardex si el boliche exige ticket automático en barra desde celular.

**MVP mínimo:** Solo `ORDER_COMMAND` on send-to-bar + reprint + admin devices.

**No usar QZ Tray** como solución principal — NightPOS necesita cola server-side, multisucursal y auditoría propias.

---

*Documento de diseño del agente. Sin código en repo aún.*
