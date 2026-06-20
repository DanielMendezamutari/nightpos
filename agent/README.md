# NightPOS Print Agent (Windows EXE)

Agente de impresión local **un solo ejecutable**, sin Node.js, sin NSSM, sin PowerShell.

- Servicio Windows nativo (inicio automático, reinicio si falla)
- Bandeja del sistema (🟢 / 🟡 / 🔴)
- Impresión RAW/ESC-POS vía WinSpool API
- Logs en `C:\ProgramData\NightPOS\PrintAgent\logs\`

---

## Requisitos para compilar

- Windows 10/11
- [Go 1.22+](https://go.dev/dl/)

```powershell
cd agent
.\build.ps1
```

Genera `NightPOSPrintAgent.exe`.

---

## Instalación en el local (sin consola)

1. Copie `NightPOSPrintAgent.exe` a la PC del local.
2. **Clic derecho → Ejecutar como administrador:**

```powershell
NightPOSPrintAgent.exe --install
```

3. Edite la configuración (se abre desde bandeja o manualmente):

```
C:\ProgramData\NightPOS\PrintAgent\config.json
```

```json
{
  "backend_url": "https://su-dominio.com/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "CAJA",
  "poll_interval_ms": 1500,
  "dry_run": false
}
```

4. Reinicie el servicio desde bandeja o:

```powershell
NightPOSPrintAgent.exe --restart
```

El icono aparece en la bandeja. **El usuario no necesita consola.**

---

## Comandos

| Comando | Descripción |
|---------|-------------|
| `--install` | Copia a Program Files, instala servicio, autostart bandeja, inicia |
| `--uninstall` | Quita servicio y bandeja |
| `--start` | Inicia servicio |
| `--stop` | Detiene servicio |
| `--restart` | Reinicia servicio |
| `--status` | Estado del servicio + rutas |
| `--tray` | Modo bandeja (uso interno) |

---

## Bandeja del sistema

| Icono | Estado |
|-------|--------|
| 🟢 Verde | Conectado al backend |
| 🟡 Amarillo | Sin internet |
| 🔴 Rojo | Error impresora / configuración |

Menú:

- **Reiniciar agente** — reinicia servicio Windows
- **Ver logs** — abre `agent.log`
- **Cambiar impresora** — abre configuración de impresoras Windows
- **Abrir configuración** — edita `config.json`
- **Salir icono bandeja** — cierra solo el icono (servicio sigue)

---

## Comportamiento del servicio

- Inicio **automático** con Windows (`start=auto`)
- **Reinicio** si falla (3 reintentos vía `sc failure`)
- Polling backend cada 1,5 s (configurable)
- **Heartbeat** → actualiza `last_seen` en NightPOS
- Sin internet → estado 🟡, reintenta solo
- Al volver internet → imprime jobs pendientes
- **No marca PRINTED** si WinSpool rechaza el job

---

## Rutas

| Qué | Ruta |
|-----|------|
| EXE instalado | `C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe` |
| Config | `C:\ProgramData\NightPOS\PrintAgent\config.json` |
| Logs | `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log` |
| Estado (bandeja) | `C:\ProgramData\NightPOS\PrintAgent\status.json` |

---

## Impresora USB

`printer_name` debe coincidir **exactamente** con el nombre en Windows (ej. `CAJA`).

Impresión vía **RAW ESC/POS** (no comando `print` de CMD).

Si no imprime con spooler OK:

1. Driver ESC/POS del fabricante
2. Puerto USB correcto
3. Cola de impresión sin errores

---

## Desinstalar

```powershell
NightPOSPrintAgent.exe --uninstall
```

---

## Legacy Node.js

La versión anterior Node (`agent/src/`) quedó obsoleta. Use solo el EXE Go.
