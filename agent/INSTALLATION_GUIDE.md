# NightPOS — Guía oficial de instalación del Agente de Impresión Local

**Producto:** NightPOS V1  
**Componente:** Agente de Impresión Local (Print Agent)  
**Editor:** Ribersoft  
**Plataforma:** Windows 10/11  
**Versión del documento:** 1.0 — 2026-06-25

---

## Propósito de este documento

Esta guía permite a cualquier técnico de Ribersoft instalar, configurar y poner en marcha el agente de impresión de NightPOS en una sucursal **sin depender del equipo de desarrollo**.

Aplica a:

- Entornos de **desarrollo local** (XAMPP, vhost `nightpos.test`)
- Entornos de **producción** (servidor con dominio y HTTPS)
- **Nuevas sucursales**
- **Reinstalaciones** tras cambio de PC o formateo

---

## Índice

1. [Requisitos](#1-requisitos)
2. [Compilar el agente](#2-compilar-el-agente)
3. [Instalar el servicio Windows](#3-instalar-el-servicio-windows)
4. [Carpetas y archivos utilizados](#4-carpetas-y-archivos-utilizados)
5. [Configurar config.json](#5-configurar-configjson)
6. [Iniciar, detener, reiniciar y consultar estado](#6-iniciar-detener-reiniciar-y-consultar-estado)
7. [Registrar el dispositivo en NightPOS](#7-registrar-el-dispositivo-en-nightpos)
8. [Probar la impresión](#8-probar-la-impresión)
9. [Actualizar el agente](#9-actualizar-el-agente)
10. [Desinstalar el agente](#10-desinstalar-el-agente)
11. [Documentación relacionada](#11-documentación-relacionada)

---

## 1. Requisitos

### Hardware y sistema operativo

| Requisito | Detalle |
|-----------|---------|
| Sistema operativo | Windows 10 o Windows 11 (64 bits) |
| PC del local | Una PC fija en barra/caja con acceso de red al servidor NightPOS |
| Impresora | Térmica USB compatible ESC/POS |
| Cable USB | Impresora conectada y reconocida por Windows |
| Permisos | Cuenta con privilegios de **Administrador** para instalar el servicio |

### Software en la PC del local

| Componente | Obligatorio | Notas |
|------------|-------------|-------|
| Driver de impresora térmica | **Sí** | Instalado desde el fabricante; la impresora debe aparecer en *Configuración → Impresoras* |
| NightPOS Print Agent (`NightPOSPrintAgent.exe`) | **Sí** | Ejecutable único; no requiere Node.js |
| Acceso de red al backend NightPOS | **Sí** | HTTP o HTTPS según entorno |
| Go 1.22+ | Solo para **compilar** | No necesario en la PC del local si se entrega el EXE ya compilado |
| .NET Framework | **No** | El agente Go no depende de .NET |
| Visual C++ Redistributable | Recomendado | En equipos muy antiguos, instalar VC++ 2015–2022 x64 si el EXE no inicia |

### Software en el servidor NightPOS

| Componente | Obligatorio |
|------------|-------------|
| Backend Laravel operativo | Sí |
| API `/api/v1` accesible desde la PC del local | Sí |
| Migraciones de impresión aplicadas (`print_devices`, `print_jobs`) | Sí |
| MySQL (o BD configurada) activa | Sí |

### Roles en NightPOS

Para registrar dispositivos se requiere un usuario con permiso **`settings.printers.manage`** (típicamente administrador de sucursal o `tenant_owner`).

---

## 2. Compilar el agente

Solo necesario en el equipo de **desarrollo** o cuando Ribersoft distribuye una **nueva versión** del agente. La PC del local puede recibir el EXE ya compilado.

### Opción A — Script `build.bat` (recomendada)

Abrir **CMD** o **PowerShell** en la carpeta del repositorio:

```powershell
cd C:\xampp\htdocs\nightpos\agent
.\build.bat
```

> **PowerShell:** siempre usar `.\` delante del nombre del script (`.\build.bat`, no `build.bat`).

Si PowerShell bloquea scripts `.ps1`, use `build.bat` o:

```powershell
powershell -ExecutionPolicy Bypass -File .\build.ps1
```

**Resultado esperado:** archivo `NightPOSPrintAgent.exe` (~7 MB) en la carpeta `agent\`.

### Opción B — Compilación manual con Go

Requisito previo: Go instalado y en PATH (`go version`).

```powershell
cd C:\xampp\htdocs\nightpos\agent
go mod tidy
go build -ldflags "-s -w" -o NightPOSPrintAgent.exe .
```

### Opción C — Usar EXE precompilado

Ribersoft puede entregar `NightPOSPrintAgent.exe` por USB, carpeta compartida o descarga interna. En ese caso **omitir** la compilación y pasar directamente a la [sección 3](#3-instalar-el-servicio-windows).

---

## 3. Instalar el servicio Windows

La instalación registra un **servicio Windows nativo** que inicia automáticamente con el sistema. No requiere consola abierta ni NSSM.

### Paso 1 — Abrir terminal como Administrador

Clic derecho en **PowerShell** o **CMD** → **Ejecutar como administrador**.

### Paso 2 — Ir a la carpeta del agente

```powershell
cd C:\xampp\htdocs\nightpos\agent
```

### Paso 3 — Ejecutar la instalación

**Opción recomendada (script):**

```powershell
.\install-service.bat
```

**Opción directa (EXE):**

```powershell
.\NightPOSPrintAgent.exe --install
```

### Qué hace `--install`

| Acción | Detalle |
|--------|---------|
| Copia el EXE | A `C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe` |
| Crea carpetas de datos | En `C:\ProgramData\NightPOS\PrintAgent\` |
| Genera config de ejemplo | `config.json` en ProgramData (si no existe) |
| Registra servicio Windows | Nombre interno: `NightPOSPrintAgent` |
| Configura inicio automático | `start=auto` |
| Configura recuperación | Reinicio automático si el servicio falla |
| Inicia bandeja del sistema | Icono 🟢/🟡/🔴 en la barra de tareas |
| Inicia el servicio | Comienza polling al backend |

**Mensaje de éxito esperado:**

```
NightPOS Print Agent instalado correctamente.
  Ejecutable: C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe
  Configuración: C:\ProgramData\NightPOS\PrintAgent\config.json
  Logs: C:\ProgramData\NightPOS\PrintAgent\logs\agent.log
```

> **Importante:** Tras instalar, **editar** `config.json` en ProgramData antes de operar en producción.

---

## 4. Carpetas y archivos utilizados

### Tabla de rutas

| Uso | Ruta | ¿La usa el servicio? |
|-----|------|----------------------|
| Código fuente / build (repositorio) | `C:\xampp\htdocs\nightpos\agent\` | **No** |
| `config.json` del repositorio | `...\agent\config.json` | **No** — solo referencia de desarrollo |
| EXE instalado (producción) | `C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe` | **Sí** |
| **Configuración activa** | `C:\ProgramData\NightPOS\PrintAgent\config.json` | **Sí** |
| Logs | `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log` | **Sí** (escritura) |
| Estado bandeja | `C:\ProgramData\NightPOS\PrintAgent\status.json` | **Sí** |
| Salida dry-run | `C:\ProgramData\NightPOS\PrintAgent\dry-run-output\` | Solo si `dry_run: true` |
| Scripts de mantenimiento | `...\agent\install-service.bat`, `restart-service.bat`, etc. | Solo durante instalación |

### Regla de oro

> El servicio Windows **solo** lee configuración desde **`C:\ProgramData\NightPOS\PrintAgent\config.json`**.  
> Editar el `config.json` dentro del repositorio **no tiene efecto** en el servicio instalado.

---

## 5. Configurar config.json

> Importante: el archivo no está en la carpeta de "Programas" ni en la carpeta del EXE. Se guarda en `C:\ProgramData\NightPOS\PrintAgent\config.json` (carpeta oculta de Windows).
>
> Si no lo ve, abra el Explorador y escriba `%ProgramData%\NightPOS\PrintAgent` en la barra de direcciones.
> También puede ejecutar `edit-config.bat` desde la carpeta del agente para abrirlo directamente en Bloc de notas.

Abrir con Bloc de notas o editor de texto:

```powershell
notepad C:\ProgramData\NightPOS\PrintAgent\config.json
```

### Campos

| Campo | Obligatorio | Descripción |
|-------|-------------|-------------|
| `backend_url` | **Sí** | URL base de la API NightPOS, **incluyendo** `/api/v1`. Sin barra final. |
| `device_key` | **Sí** | Clave del dispositivo (`npd_live_...`). Se obtiene una sola vez al registrar en NightPOS. |
| `printer_name` | **Sí** (salvo dry_run) | Nombre **exacto** de la impresora en Windows. |
| `poll_interval_ms` | No | Intervalo de consulta al backend en milisegundos. Default: `1500`. |
| `dry_run` | No | `true` = no imprime en impresora; guarda contenido en archivo. Default: `false`. |
| `dry_run_dir` | No | Carpeta de salida cuando `dry_run` es true. |
| `log_level` | No | Nivel de log: `debug`, `info`, `warn`, `error`. Default: `info`. |

### Ejemplo — Desarrollo local (XAMPP)

```json
{
  "backend_url": "http://nightpos.test/api/v1",
  "device_key": "npd_live_REEMPLAZAR",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500,
  "dry_run": false,
  "dry_run_dir": "C:\\ProgramData\\NightPOS\\PrintAgent\\dry-run-output",
  "log_level": "info"
}
```

**Alternativa sin vhost:**

```json
{
  "backend_url": "http://localhost/nightpos/backend/public/api/v1",
  "device_key": "npd_live_REEMPLAZAR",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500,
  "dry_run": false,
  "log_level": "info"
}
```

### Ejemplo — Producción (HTTPS)

```json
{
  "backend_url": "https://nightpos.cliente.com/api/v1",
  "device_key": "npd_live_REEMPLAZAR",
  "printer_name": "BARRA_CENTRO",
  "poll_interval_ms": 1500,
  "dry_run": false,
  "log_level": "info"
}
```

### Tras editar config.json

Siempre reiniciar el servicio:

```powershell
cd C:\xampp\htdocs\nightpos\agent
.\restart-service.bat
```

---

## 6. Iniciar, detener, reiniciar y consultar estado

Ejecutar desde la carpeta donde está el EXE o usar la ruta de Program Files.

| Comando | Acción |
|---------|--------|
| `NightPOSPrintAgent.exe --start` | Inicia el servicio |
| `NightPOSPrintAgent.exe --stop` | Detiene el servicio |
| `NightPOSPrintAgent.exe --restart` | Reinicia el servicio (usar tras cambiar config) |
| `NightPOSPrintAgent.exe --status` | Muestra RUNNING / STOPPED y rutas de config/logs |

**Ejemplo en PowerShell:**

```powershell
cd C:\xampp\htdocs\nightpos\agent
.\NightPOSPrintAgent.exe --status
```

**Respuesta esperada:**

```
Servicio NightPOSPrintAgent: RUNNING
Config: C:\ProgramData\NightPOS\PrintAgent\config.json
Logs: C:\ProgramData\NightPOS\PrintAgent\logs\agent.log
```

### Modos de diagnóstico (consola)

| Comando | Uso |
|---------|-----|
| `--run` | Ejecuta el agente en primer plano con consola visible |
| `--dry-run` | Igual que `--run` pero fuerza `dry_run` (no imprime en impresora) |

---

## 7. Registrar el dispositivo en NightPOS

El agente necesita un `device_key` válido generado desde el panel de administración.

### Pasos

1. Iniciar sesión en NightPOS con usuario administrador de sucursal.
2. Ir a **Configuración → Impresoras** (`/nightpos/settings/printers`).
3. Activar **Imprimir comanda al enviar a barra** si se desea impresión automática.
4. En **Registrar dispositivo**, ingresar un nombre descriptivo (ej. `PC Barra Centro`).
5. Clic en **Generar device_key**.
6. **Copiar la clave inmediatamente** — solo se muestra una vez.
7. Pegar la clave en `C:\ProgramData\NightPOS\PrintAgent\config.json` → campo `device_key`.
8. Reiniciar el servicio (`.\restart-service.bat`).
9. Verificar en la misma pantalla que el dispositivo aparece **Online** (heartbeat cada ~1,5 s).

### Prefijo de la clave

Las claves válidas comienzan con `npd_live_`.

---

## 8. Probar la impresión

### Prueba 1 — Desde el panel NightPOS

1. Admin → **Configuración → Impresoras**.
2. Confirmar dispositivo **Online**.
3. Clic en **Probar** (Probar impresión) en la fila del dispositivo.
4. Verificar que sale ticket de prueba en la impresora.
5. En **Historial de impresión**, el job debe quedar en estado **PRINTED**.

### Prueba 2 — Comanda desde garzón

1. Garzón crea pedido y presiona **Enviar a barra**.
2. Admin → Impresoras → Historial: debe aparecer job tipo **ORDER_COMMAND** → **PRINTED**.
3. Ticket impreso en la impresora USB del local.

### Prueba 3 — Dry-run (sin impresora)

En `config.json`, temporalmente:

```json
"dry_run": true
```

Reiniciar servicio. Los jobs se guardan en `dry-run-output\` en lugar de imprimir.

### Verificación en logs

```powershell
notepad C:\ProgramData\NightPOS\PrintAgent\logs\agent.log
```

Líneas esperadas:

```
[INFO] NightPOS Agent iniciado v2.0.0
[INFO] Backend: http://nightpos.test/api/v1
[INFO] Job #123 ORDER_COMMAND recibido
[INFO] Job #123 CLAIMED
[INFO] Imprimiendo job #123 en POS-80
[INFO] Job #123 PRINTED (842 bytes)
```

---

## 9. Actualizar el agente

Para instalar una nueva versión **sin perder configuración**:

1. Compilar o copiar el nuevo `NightPOSPrintAgent.exe`.
2. Detener el servicio:

   ```powershell
   .\NightPOSPrintAgent.exe --stop
   ```

3. Reemplazar el EXE en:

   ```
   C:\Program Files\NightPOS\PrintAgent\NightPOSPrintAgent.exe
   ```

   O volver a ejecutar `--install` como Administrador (copia automáticamente).

4. **No modificar** `C:\ProgramData\NightPOS\PrintAgent\config.json` — se conserva.

5. Reiniciar:

   ```powershell
   .\NightPOSPrintAgent.exe --restart
   ```

6. Verificar `--status` y estado **Online** en NightPOS.

---

## 10. Desinstalar el agente

Como **Administrador**:

```powershell
cd C:\xampp\htdocs\nightpos\agent
.\uninstall-service.bat
```

O:

```powershell
.\NightPOSPrintAgent.exe --uninstall
```

**Qué elimina:** servicio Windows y entrada de bandeja.  
**Qué conserva:** `C:\ProgramData\NightPOS\PrintAgent\` (config, logs) para posible reinstalación.

Para borrar datos por completo, eliminar manualmente la carpeta ProgramData tras desinstalar.

---

## 11. Documentación relacionada

| Documento | Contenido |
|-----------|-----------|
| [TROUBLESHOOTING_GUIDE.md](./TROUBLESHOOTING_GUIDE.md) | Solución de problemas |
| [DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md) | Checklist nueva sucursal |
| [README_WINDOWS.md](./README_WINDOWS.md) | Resumen operativo sucursal |
| [README.md](./README.md) | Referencia técnica del componente |

---

**Ribersoft — NightPOS V1**  
*Documento oficial de instalación. Mantener actualizado ante cambios de versión del agente.*
