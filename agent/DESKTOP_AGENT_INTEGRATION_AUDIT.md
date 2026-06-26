# DESKTOP ↔ PRINT AGENT — AUDITORÍA DE INTEGRACIÓN

**Fecha:** 2026-06-25  
**Estado:** Auditoría — sin implementación  
**Alcance:** Cómo conviven NightPOS Desktop (futuro) y NightPOS Print Agent (actual)

---

## 1. Resumen ejecutivo

Hoy existen **dos procesos independientes** en la PC de sucursal:

| Proceso | Tecnología | Función |
|---------|------------|---------|
| **NightPOS Web/PWA** | Navegador → Vue SPA | Operación caja/admin/garzón vía HTTPS al hosting |
| **NightPOS Print Agent** | Go EXE + servicio Windows | Poll jobs de impresión → impresora USB ESC/POS |

**No hay canal directo browser ↔ agente.** Ambos hablan con el **backend Laravel** por HTTP(S).

Esta separación es **correcta para V1** y debe mantenerse hasta V1.1.

---

## 2. Arquitectura actual (evidencia código)

```
┌─────────────────────────────────────────────────────────────┐
│                    PC SUCURSAL (Windows)                     │
│                                                              │
│  ┌──────────────────┐         ┌──────────────────────────┐  │
│  │ Browser / PWA /  │  HTTPS  │ NightPOSPrintAgent.exe   │  │
│  │ Electron (futuro)│ ──────► │ (Windows Service + Tray) │  │
│  └────────┬─────────┘         └────────────┬─────────────┘  │
│           │                                 │                │
└───────────┼─────────────────────────────────┼────────────────┘
            │                                 │
            ▼                                 ▼
     ┌──────────────────────────────────────────────┐
     │         Backend Laravel (hosting)             │
     │  /api/v1/...          /api/v1/print-jobs/...  │
     └──────────────────────────────────────────────┘
            ▲
            │ HTTPS (celulares garzones, otros PCs)
            │
     ┌──────┴──────┐
     │   Garzones   │
     └─────────────┘
```

### Agente — configuración local

**Ruta:** `C:\ProgramData\NightPOS\PrintAgent\config.json`

```json
{
  "backend_url": "https://cliente.com/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500
}
```

**Ruta EXE:** `C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe`

### Agente — estado local

**Ruta:** `C:\ProgramData\NightPOS\PrintAgent\status.json`

Campos (`internal/status/status.go`):

- `state`: `connected` | `no_internet` | `printer_error` | `config_error` | `starting`
- `message`, `last_error`, `printer_name`, `backend_url`
- `service_running`, `last_job_id`, `updated_at`

Actualizado en cada tick del poll. Leído por **bandeja del sistema** (tray) y CLI `--status`.

### Agente — loop

`internal/agent/runtime.go`:

1. Verifica impresora Windows
2. Poll interval configurable (default 1500 ms)
3. Claim jobs vía API con `device_key`
4. Imprime ESC/POS o dry-run

### Frontend — impresión

- Admin registra dispositivo → `device_key` (`settings/printers/index.vue`)
- Instrucciones manuales de instalación agente (no status en vivo en UI)
- Jobs creados por backend (comandas, tickets, etc.); agente los consume

**Conclusión:** integración es **backend-centric**. Desktop no necesita hablar con agente para imprimir.

---

## 3. Opciones de integración Desktop ↔ Agente

### Opción 1 — Mantener separado (recomendada V1)

| Aspecto | Detalle |
|---------|---------|
| Instalación | Dos pasos documentados (PWA/browser + agent service) |
| Estado impresión | Bandeja Windows + `status.json` + logs |
| Desktop muestra | Nada o link “Ver guía impresora” |
| Ventaja | Cero cambios agente, cero riesgo seguridad browser→filesystem |

**Alineado con requerimiento usuario:** “Para V1 quizá mantener separado.”

### Opción 2 — Desktop muestra estado del agente (V1.1)

Desktop (Electron o app nativa ligera) lee `status.json`:

```javascript
// Solo posible fuera del browser puro
const status = JSON.parse(fs.readFileSync(
  'C:\\ProgramData\\NightPOS\\PrintAgent\\status.json', 'utf8'
))
```

UI sugerida en cajera/admin:

- Chip: 🟢 Agente conectado / 🔴 Sin internet / 🟠 Error impresora
- Link abrir logs: `agent.log`
- Botón “Reiniciar servicio” (ejecuta `restart-service.bat` con permisos)

**PWA pura no puede** leer `status.json` por sandbox del navegador.

### Opción 3 — Agente expone HTTP local (NO recomendado V1)

Ejemplo: `http://127.0.0.1:9245/status`

| Pros | Contras |
|------|---------|
| Browser podría consultar | Superficie de ataque, CORS, firewall, otro puerto que mantener |
| | Duplica info ya en `status.json` |

**Veredicto:** posponer; preferir lectura archivo vía Electron V1.1.

### Opción 4 — Instalador único (V1.1)

`NightPOS-Setup.exe`:

1. Instala Print Agent + servicio + tray
2. Instala Desktop Electron o crea acceso PWA
3. Wizard único:
   - URL backend
   - `device_key` (pegar desde admin)
   - `printer_name` (lista impresoras Windows)
4. Escribe ambos configs en `ProgramData`

---

## 4. Configuración propuesta unificada (V1.1)

Estructura sugerida (no implementada):

```
C:\ProgramData\NightPOS\
├── PrintAgent\
│   ├── config.json      ← agente (existente)
│   ├── status.json      ← agente (existente)
│   └── logs\
└── Desktop\
    └── config.json      ← nuevo wrapper desktop
```

**Desktop `config.json`:**

```json
{
  "app_url": "https://pos.casa22.com",
  "tenant_slug": "casa22",
  "branch_code": "CENTRO",
  "print_agent_enabled": true,
  "print_agent_status_path": "..\\PrintAgent\\status.json",
  "auto_launch": true
}
```

**Agente `config.json`** sigue siendo fuente de verdad para impresión (no mezclar `device_key` en frontend).

---

## 5. Sincronización URL backend

Escenario típico de inconsistencia:

| Componente | URL configurada |
|------------|-----------------|
| Browser/PWA | Origen `https://pos.casa22.com` (implícito) |
| Print Agent | `backend_url` en config.json |

**Regla operativa V1:** al cambiar dominio hosting, actualizar **manualmente** `config.json` del agente y reinstalar PWA desde nuevo origen.

**V1.1 wizard:** un solo campo “URL NightPOS” que escribe:

- Desktop `app_url`
- Agent `backend_url` = `{app_url}/api/v1` (normalizado)

---

## 6. Comunicación: qué NO hacer

| Anti-patrón | Por qué |
|-------------|---------|
| Frontend JS llamando impresora USB | Imposible en browser; rompe modelo |
| Duplicar cola print en desktop | Dos consumidores de jobs |
| Guardar `device_key` en cookies web | Secreto de dispositivo — solo en agent config |
| Agente sirviendo la SPA | Acopla releases, complica updates |

---

## 7. Garzón móvil y agente

Los garzones **no instalan** print agent. Sus comandas generan print jobs en backend asignados a dispositivos de **barra/caja** en la sucursal.

Integración desktop↔agente es tema **solo PC fija de sucursal**.

---

## 8. Riesgos integración

| Riesgo | Severidad | Notas |
|--------|-----------|-------|
| `device_key` filtrada en UI admin | Alta | Mostrar una vez; rotar en admin |
| Agente apunta backend viejo tras migración | Alta | Checklist migración hosting |
| Firewall bloquea poll HTTPS | Alta | Probar `agent.log` |
| Impresora renombrada en Windows | Media | `printer_error` en status |
| Desktop PWA no ve estado agente | Baja UX | Esperado V1; Electron V1.1 |
| Dos versiones agent/desktop desincronizadas | Media | Semver en About + docs |

---

## 9. Qué instalar en sucursal (checklist)

- [ ] `NightPOSPrintAgent.exe` + `install-service.bat` (admin)
- [ ] `config.json` con `backend_url`, `device_key`, `printer_name`
- [ ] Impresora térmica USB
- [ ] `device_key` registrada en Admin → Impresoras
- [ ] PWA Desktop o acceso directo al POS web
- [ ] Internet saliente HTTPS al hosting
- [ ] Verificar `status.json` → `state: connected`

Opcional V1.1: instalador unificado.

---

## 10. Actualización

| Componente | Procedimiento |
|------------|---------------|
| **Agente** | Detener servicio → reemplazar EXE → `restart-service.bat` |
| **Config agente** | Editar JSON; reiniciar servicio |
| **Frontend** | Deploy hosting (automático para browser/PWA) |
| **Desktop Electron** | Reinstalar o auto-update V2 |

El agente **no embebe** el frontend — updates web no requieren recompilar agente salvo cambios API breaking.

---

## 11. Respuestas directas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Es posible integrar? | **Sí** — estado vía archivo local o instalador unificado V1.1 |
| 2 | V1 | **Separado** — documentación + systray agente |
| 3 | V1.1 | Desktop lee `status.json` + wizard config unificado |
| 4 | Rápido | Nada que codear V1; guía operativa ya en `README_WINDOWS.md` |
| 5 | Riesgos | URL desincronizada, key leak, firewall |
| 6 | Por sucursal | Agent service + impresora (+ PWA desktop aparte) |
| 7 | Actualizar | Agent manual; web via hosting |
| 8 | URL/contexto | Agent `config.json`; web cookies/origen |
| 9 | Conexión | **Indirecta vía backend** — no socket browser-agent |
| 10 | No prometer V1 | Instalador único, status en PWA, HTTP local agent |

---

## 12. Archivos referencia

| Path | Contenido |
|------|-----------|
| `agent/main.go` | CLI install/run/tray |
| `agent/internal/agent/runtime.go` | Poll loop |
| `agent/internal/status/status.go` | `status.json` |
| `agent/internal/paths/paths.go` | Rutas ProgramData |
| `agent/README_WINDOWS.md` | Guía instalación |
| `frontend/src/pages/nightpos/settings/printers/index.vue` | UI admin device_key |
| `backend/app/Application/Printing/**` | Jobs, claim, devices |

---

## 13. Recomendación final

1. **V1:** mantener agente separado; completar PWA/desktop web; operador monitorea tray + logs.
2. **V1.1:** Electron desktop lee `status.json` y ofrece instalador opcional bundled.
3. **Nunca** duplicar lógica de impresión en el frontend — el agente sigue siendo el único consumidor local de `print_jobs`.
