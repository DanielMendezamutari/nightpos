# WINDOWS_SERVICE_INSTALLATION_REPORT.md

**Fecha:** 2026-06-25  
**Componente:** `agent/` — NightPOS Print Agent (Go EXE v2.0.0)

---

## Resumen

El agente se empaqueta como **NightPOSPrintAgent.exe** e instala un **servicio Windows nativo** vía `kardianos/service` (no requiere NSSM en la ruta principal). Scripts `.bat` envuelven los comandos CLI para operadores de sucursal.

---

## Arquitectura de instalación

```
install-service.bat
    └── NightPOSPrintAgent.exe --install
            ├── Copia EXE → C:\Program Files\NightPOS\PrintAgent\
            ├── Crea config.json en ProgramData (si no existe)
            ├── Instala servicio "NightPOSPrintAgent" (start=auto)
            ├── Configura recovery (3 reinicios)
            ├── Autostart bandeja (--tray)
            └── Inicia servicio
```

---

## Servicio Windows

| Propiedad | Valor |
|-----------|-------|
| Nombre interno | `NightPOSPrintAgent` |
| Display name | `NightPOS Print Agent` |
| Inicio | Automático |
| Recovery | Reinicio tras fallo (sc failure) |
| Consola | No — ejecutado por SCM |

---

## Configuración

Archivo: `%ProgramData%\NightPOS\PrintAgent\config.json`

Campos:

| Campo | Descripción |
|-------|-------------|
| `backend_url` | Base API `/api/v1` |
| `device_key` | Bearer `npd_live_...` |
| `printer_name` | Nombre exacto WinSpool |
| `poll_interval_ms` | Default 1500 |
| `dry_run` | true = no imprime, guarda archivos |
| `log_level` | debug \| info \| warn \| error |

---

## API agente (sin JWT staff)

| Método | Ruta | Rol |
|--------|------|-----|
| POST | `/print-devices/heartbeat` | Ping / online |
| GET | `/print-jobs/pending` | Poll jobs |
| POST | `/print-jobs/{id}/claim` | Reservar job |
| POST | `/print-jobs/{id}/printed` | Confirmar |
| POST | `/print-jobs/{id}/failed` | Error + retry |

Auth: `Authorization: Bearer {device_key}`

---

## Modos de ejecución

| Modo | Comando | Consola |
|------|---------|---------|
| Producción | Servicio Windows | No |
| Debug | `--run` | Sí |
| Sin impresora | `--dry-run` | Sí |
| Bandeja | `--tray` | No (interno) |

---

## Compilación

Requisito: **Go 1.22+** en PATH (`go version`).

```cmd
cd agent
build.bat
```

Alternativa PowerShell (política de scripts bloqueada):

```powershell
powershell -ExecutionPolicy Bypass -File .\build.ps1
```

Si Go no está instalado, el build fallará con mensaje explícito — instalar desde https://go.dev/dl/

---

## Alternativa NSSM

La instalación **recomendada** usa servicio nativo integrado. Si se prefiere NSSM en entornos legacy, apuntar a:

```
nssm install NightPOSPrintAgent "C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe"
```

Nota: `--install` ya cubre el caso estándar; NSSM no es necesario en NightPOS V1.

---

## Scripts entregados

| Script | Acción |
|--------|--------|
| `install-service.bat` | `--install` |
| `uninstall-service.bat` | `--uninstall` |
| `restart-service.bat` | `--restart` |

---

## Validación post-instalación

1. `NightPOSPrintAgent.exe --status` → RUNNING
2. Admin impresoras → Online (< 30 s desde heartbeat)
3. `POST /print-devices/{id}/test-print` → job TEST → PRINTED
4. Log `agent.log` sin errores de conexión

---

## Referencias

- `agent/README.md` — documentación desarrollador
- `agent/README_WINDOWS.md` — guía sucursal
- `backend/PRINT_AGENT_ORDER_COMMAND_DEBUG_REPORT.md` — debug ORDER_COMMAND
