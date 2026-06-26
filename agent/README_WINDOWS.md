# NightPOS Print Agent — Instalación Windows

Guía operativa para cada sucursal. El agente es un **único EXE Go** (sin Node.js). Se instala como **servicio Windows nativo** y corre sin consola.

---

## Requisitos

| Requisito | Detalle |
|-----------|---------|
| SO | Windows 10/11 |
| Backend | NightPOS accesible desde la PC del local (LAN o localhost) |
| Impresora | Térmica USB ESC/POS instalada en Windows |
| Permisos | Administrador solo para `--install` |
| Compilar EXE | Go 1.22+ en PATH — ver sección abajo |

---

## Compilar el EXE (solo una vez por versión)

1. Instale Go: https://go.dev/dl/ (Windows amd64, MSI)
2. Cierre y abra PowerShell/CMD
3. Verifique: `go version`
4. En la carpeta `agent/`:

```cmd
build.bat
```

Si prefiere PowerShell y le bloquea scripts:

```powershell
powershell -ExecutionPolicy Bypass -File .\build.ps1
```

Debe aparecer `NightPOSPrintAgent.exe` en la misma carpeta.

---

## Estructura de archivos (distribución)

```
agent/
  NightPOSPrintAgent.exe   ← compilar con build.ps1
  config.example.json
  install-service.bat
  uninstall-service.bat
  restart-service.bat
  README_WINDOWS.md
```

Tras instalar, el sistema usa:

| Qué | Ruta |
|-----|------|
| EXE | `C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe` |
| Config | `C:\ProgramData\NightPOS\PrintAgent\config.json` |
| Logs | `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log` |

---

## Paso 1 — Registrar dispositivo en NightPOS

1. Admin → **Configuración → Impresoras**
2. **Generar device_key** (nombre ej. `PC Barra Centro`)
3. **Copiar device_key** (solo se muestra una vez)

---

## Paso 2 — Instalar servicio

1. Copie `NightPOSPrintAgent.exe` y los `.bat` a la PC del local
2. Clic derecho → **Ejecutar como administrador** en `install-service.bat`

   O manualmente:

   ```powershell
   NightPOSPrintAgent.exe --install
   ```

---

## Paso 3 — Configurar config.json

> Importante: este archivo no aparece dentro de "Programas" ni en la carpeta de instalación del EXE. El agente lo guarda en `C:\ProgramData\NightPOS\PrintAgent\config.json`, que es una carpeta oculta de Windows.
>
> Si no lo encuentra, abra el Explorador de archivos y pegue esta ruta en la barra de direcciones: `%ProgramData%\NightPOS\PrintAgent`.
> O bien, desde la carpeta del agente, ejecute `edit-config.bat` para abrirlo directamente en Bloc de notas.

Edite `C:\ProgramData\NightPOS\PrintAgent\config.json`:

```json
{
  "backend_url": "http://nightpos.test/api/v1",
  "device_key": "npd_live_PEGAR_AQUI",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500,
  "dry_run": false,
  "dry_run_dir": "C:\\ProgramData\\NightPOS\\PrintAgent\\dry-run-output",
  "log_level": "info"
}
```

### backend_url (ejemplos)

| Entorno | URL |
|---------|-----|
| XAMPP vhost | `http://nightpos.test/api/v1` |
| XAMPP sin vhost | `http://localhost/nightpos/backend/public/api/v1` |
| Producción | `https://su-dominio.com/api/v1` |

### printer_name

Debe coincidir **exactamente** con el nombre en **Configuración → Dispositivos e impresoras → Impresoras**.

---

## Paso 4 — Reiniciar y verificar

```powershell
restart-service.bat
# o
NightPOSPrintAgent.exe --restart
```

Verificar:

1. Icono 🟢 en bandeja del sistema
2. Admin → Impresoras → dispositivo **Online**
3. Botón **Probar impresión** → ticket de prueba

---

## Comandos útiles

| Comando | Uso |
|---------|-----|
| `--status` | Estado del servicio |
| `--restart` | Reiniciar tras cambiar config |
| `--run` | Modo consola (debug) |
| `--dry-run` | No imprime; guarda RAW en dry-run-output |
| `--uninstall` | Quitar servicio |

---

## Logs esperados

```
[INFO] NightPOS Agent iniciado v2.0.0
[INFO] Backend: http://nightpos.test/api/v1
[INFO] Impresora: POS-80
[INFO] Job #123 ORDER_COMMAND recibido
[INFO] Job #123 CLAIMED
[INFO] Imprimiendo job #123 en POS-80
[INFO] Job #123 PRINTED (842 bytes)
```

Errores comunes:

| Log | Causa |
|-----|-------|
| `Error conexión backend` | URL incorrecta, firewall, Apache apagado |
| `Heartbeat failed 401` | device_key incorrecta o rotada |
| `Printer verify failed` | printer_name no existe en Windows |
| `Job #N FAILED — impresora` | Papel, driver, impresora apagada |

---

## Desinstalar

```powershell
uninstall-service.bat
```

Los datos en `ProgramData` se conservan (config + logs).

---

## QA operativo (checklist sucursal)

- [ ] Agente instalado como servicio (`--status` → RUNNING)
- [ ] Dispositivo Online en admin
- [ ] Probar impresión OK
- [ ] Garzón envía comanda → job ORDER_COMMAND → PRINTED
- [ ] Reinicio Windows → agente inicia solo
- [ ] Sin consola abierta durante operación

Ver `WINDOWS_SERVICE_INSTALLATION_REPORT.md` para detalle técnico.
