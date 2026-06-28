# NightPOS — Guía oficial de solución de problemas del Agente de Impresión

**Producto:** NightPOS V1  
**Componente:** Agente de Impresión Local (Print Agent)  
**Editor:** Ribersoft  
**Versión del documento:** 1.0 — 2026-06-25

---

## Propósito

Este documento recopila los errores más frecuentes durante la instalación y operación del agente de impresión NightPOS, con **causa**, **diagnóstico** y **solución** paso a paso.

Para instalación inicial, consultar [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md).  
Para checklist de sucursal nueva, consultar [DEPLOYMENT_CHECKLIST.md](./DEPLOYMENT_CHECKLIST.md).

---

## Índice de errores

1. [Error de DNS / backend inalcanzable](#1-error-de-dns--backend-inalcanzable)
2. [Error de certificado HTTPS](#2-error-de-certificado-https)
3. [Dispositivo Offline en NightPOS](#3-dispositivo-offline-en-nightpos)
4. [No imprime (sin error visible)](#4-no-imprime-sin-error-visible)
5. [print_job queda PENDING](#5-print_job-queda-pending)
6. [print_job queda FAILED](#6-print_job-queda-failed)
7. [print_job queda CLAIMED (atascado)](#7-print_job-queda-claimed-atascado)
8. [Errores de PowerShell al ejecutar scripts](#8-errores-de-powershell-al-ejecutar-scripts)
9. [Confusión ProgramData vs config del repositorio](#9-confusión-programdata-vs-config-del-repositorio)
10. [No encuentra la impresora](#10-no-encuentra-la-impresora)
11. [Servicio no instalado / no inicia](#11-servicio-no-instalado--no-inicia)
12. [Error SQLite o 500 en backend (desarrollo)](#12-error-sqlite-o-500-en-backend-desarrollo)
13. [Firewall y antivirus](#13-firewall-y-antivirus)
14. [device_key inválida o rotada](#14-device_key-inválida-o-rotada)
15. [Connection reset / wsarecv / stream CANCEL (hosting)](#15-connection-reset--wsarecv--stream-cancel-hosting)
16. [Preguntas frecuentes (FAQ)](#16-preguntas-frecuentes-faq)

---

## 1. Error de DNS / backend inalcanzable

### Síntoma en logs

```
[WARN] Error conexión backend: lookup tu-dominio-nightpos.com: no such host
```

O en consola del agente:

```
Heartbeat failed: dial tcp: lookup ...
```

### Causa

El campo `backend_url` en `config.json` apunta a un dominio que **no existe** o no resuelve desde la PC del local. Suele ocurrir cuando se deja el valor de ejemplo del instalador.

### Solución

1. Abrir `C:\ProgramData\NightPOS\PrintAgent\config.json`.
2. Corregir `backend_url` al valor real:

   | Entorno | Valor típico |
   |---------|--------------|
   | XAMPP con vhost | `http://nightpos.test/api/v1` |
   | XAMPP sin vhost | `http://localhost/nightpos/backend/public/api/v1` |
   | Producción | `https://dominio-real-del-cliente.com/api/v1` |

3. Probar desde la PC del local en el navegador o con curl:

   ```powershell
   curl.exe http://nightpos.test/api/v1/auth/login-context/tenants
   ```

   Debe responder HTTP 200 con JSON.

4. Reiniciar agente: `.\restart-service.bat`.

---

## 2. Error de certificado HTTPS

### Síntoma en logs

```
certificate signed by unknown authority
x509: certificate signed by unknown authority
```

### Causa

- HTTPS con certificado autofirmado o no confiable en desarrollo.
- Certificado expirado o cadena incompleta en producción.

### Solución

| Entorno | Acción |
|---------|--------|
| **Desarrollo local** | Usar **HTTP** en `backend_url` (ej. `http://nightpos.test/api/v1`). |
| **Producción** | Instalar certificado válido (Let's Encrypt, CA comercial) en el servidor. No desactivar verificación TLS en el agente. |
| **Red interna con HTTPS privado** | Instalar el certificado raíz en Windows (*certmgr.msc*) o usar HTTP en LAN si la política del cliente lo permite. |

---

## 3. Dispositivo Offline en NightPOS

### Síntoma

En **Configuración → Impresoras**, el chip del dispositivo muestra **Offline**. `last_seen_at` vacío o antiguo (> 30 segundos).

### Checklist de diagnóstico

| # | Verificar | Cómo |
|---|-----------|------|
| 1 | Servicio corriendo | `.\NightPOSPrintAgent.exe --status` → RUNNING |
| 2 | Config en ProgramData | Editar `C:\ProgramData\NightPOS\PrintAgent\config.json` (no el del repo) |
| 3 | `backend_url` correcto | Probar URL en navegador desde la PC del local |
| 4 | `device_key` correcta | Debe empezar con `npd_live_`; no espacios ni comillas extra |
| 5 | Dispositivo habilitado | Admin → Impresoras → no debe estar Desactivado |
| 6 | Apache/servidor activo | Backend responde |
| 7 | Logs del agente | `agent.log` — buscar "Heartbeat failed" |

### Probar heartbeat manualmente

```powershell
curl.exe -X POST http://nightpos.test/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_TU_CLAVE" `
  -H "Content-Type: application/json" `
  -d "{\"printer_name\":\"POS-80\",\"agent_version\":\"2.0.0\"}"
```

Respuesta **200** → el backend acepta la clave. Si el admin sigue Offline, reiniciar servicio.

Respuesta **401** → `device_key` incorrecta o rotada.

---

## 4. No imprime (sin error visible)

### Síntoma

Dispositivo **Online**, jobs en **PRINTED** en NightPOS, pero no sale papel.

### Revisar en orden

1. **`printer_name` incorrecto** — nombre en config no coincide con Windows (ver [sección 10](#10-no-encuentra-la-impresora)).
2. **Impresora apagada o sin papel** — revisar panel físico y cola de Windows.
3. **Driver incorrecto** — usar driver ESC/POS del fabricante, no driver genérico "Text Only".
4. **`dry_run: true`** en config — el agente guarda archivos, no imprime. Poner `false` y reiniciar.
5. **Job PRINTED pero spooler falló** — revisar cola de impresión Windows por jobs atascados.
6. **Impresora compartida de red** — el agente imprime en la PC local vía WinSpool; la impresora debe estar instalada **en esa PC**.

### Logs a buscar

```
[INFO] Imprimiendo job #123 en NOMBRE_IMPRESORA
[ERROR] Job #123 FAILED — impresora: ...
```

---

## 5. print_job queda PENDING

### Síntoma

En Admin → Impresoras → Historial, jobs permanecen en **PENDING** sin pasar a PRINTED.

### Causas y soluciones

| Causa | Solución |
|-------|----------|
| Agente apagado o servicio STOPPED | `.\NightPOSPrintAgent.exe --start` o `--restart` |
| Dispositivo Offline | Ver [sección 3](#3-dispositivo-offline-en-nightpos) |
| `backend_url` incorrecto | Corregir config y reiniciar |
| No se creó job (sin dispositivo registrado) | Registrar dispositivo en NightPOS antes de enviar comanda |
| `auto_print_order_command` desactivado | Activar en Admin → Impresoras |
| Branch incorrecto | El agente solo toma jobs de la sucursal del `device_key` |

> Los jobs PENDING **se imprimen automáticamente** cuando el agente vuelve a estar Online.

---

## 6. print_job queda FAILED

### Síntoma

Estado **FAILED** en historial con `last_error` en la columna Error.

### Causas frecuentes

| last_error típico | Acción |
|-------------------|--------|
| Impresora no encontrada | Corregir `printer_name` |
| Acceso denegado spooler | Ejecutar servicio con cuenta que tenga permiso de impresión |
| Error de driver | Reinstalar driver ESC/POS |
| `content_text vacío` | Reportar a Ribersoft (error de backend) |

Tras corregir, usar **Reimprimir** desde la operación correspondiente o **Probar impresión** en admin.

---

## 7. print_job queda CLAIMED (atascado)

### Síntoma

Job en estado **CLAIMED** durante mucho tiempo; no pasa a PRINTED ni FAILED.

### Causa

El agente reclamó el job (`claim`) pero crasheó o se detuvo antes de marcar `printed` o `failed`.

### Solución operativa

1. Reiniciar servicio del agente.
2. Si persiste, contactar soporte Ribersoft — puede requerir intervención en base de datos para liberar el job.
3. Mientras tanto, usar **Reimprimir comanda** desde admin (genera nuevo job con nueva idempotencia).

---

## 8. Errores de PowerShell al ejecutar scripts

### Error: "la ejecución de scripts está deshabilitada"

Al ejecutar `.\build.ps1`.

**Solución:** usar `.\build.bat` o:

```powershell
powershell -ExecutionPolicy Bypass -File .\build.ps1
```

### Error: "build.bat no se reconoce"

**Causa:** falta el prefijo `.\` en PowerShell.

| Incorrecto | Correcto |
|------------|----------|
| `build.bat` | `.\build.bat` |
| `install-service.bat` | `.\install-service.bat` |
| `\NightPOSPrintAgent.exe` | `.\NightPOSPrintAgent.exe` |

### Error: "d C:\..." no se reconoce

**Causa:** escribió `d` en lugar de `cd`.

**Correcto:** `cd C:\xampp\htdocs\nightpos\agent`

### Error: ejecutó script desde `C:\Windows\system32`

**Causa:** no cambió de carpeta antes de ejecutar.

**Solución:** siempre `cd` a la carpeta del agente primero, o usar ruta completa:

```powershell
C:\xampp\htdocs\nightpos\agent\install-service.bat
```

---

## 9. Confusión ProgramData vs config del repositorio

### Síntoma

Se editó `C:\xampp\htdocs\nightpos\agent\config.json` pero el agente no cambia de comportamiento.

### Causa

El **servicio Windows** lee exclusivamente:

```
C:\ProgramData\NightPOS\PrintAgent\config.json
```

El archivo en el repositorio es solo referencia para desarrollo.

### Solución

1. Editar el archivo de **ProgramData**.
2. Reiniciar servicio.
3. Opcional: copiar contenido correcto del repo al de ProgramData (no al revés en producción).

---

## 10. No encuentra la impresora

### Síntoma en logs

```
[ERROR] Printer verify failed: ...
[ERROR] Job #N FAILED — impresora: open printer: ...
```

### Obtener el nombre correcto

1. **Configuración → Bluetooth y dispositivos → Impresoras y escáneres** (Windows 11).
2. O **Panel de control → Dispositivos e impresoras**.
3. Copiar el nombre **exacto** (mayúsculas, espacios, guiones).

Ejemplos válidos: `POS-80`, `EPSON TM-T20`, `CAJA`.

4. Pegar en `printer_name` de config.json.
5. Reiniciar servicio.

### Verificar impresora operativa

Imprimir página de prueba desde Windows antes de probar NightPOS.

---

## 11. Servicio no instalado / no inicia

### Síntoma

```
Error: the service is not installed
```

Al ejecutar `--status` **antes** de instalar. Es **normal** si aún no se ejecutó `--install`.

### Síntoma

Servicio instalado pero STOPPED o no arranca.

### Checklist

1. Ejecutar `--install` como **Administrador**.
2. Verificar config válida (backend_url y device_key no vacíos).
3. Revisar `agent.log` tras intento de inicio.
4. Verificar en `services.msc` que existe **NightPOS Print Agent**.
5. Reinstalar: `--uninstall` → `--install`.

---

## 12. Error SQLite o 500 en backend (desarrollo)

### Síntoma en log Laravel

```
Database file at path [...\database.sqlite] does not exist
```

### Causa

Backend en modo HTTP usando SQLite por config cacheada, mientras `.env` tiene MySQL.

### Solución (servidor, no agente)

```powershell
cd C:\xampp\htdocs\nightpos\backend
php artisan optimize:clear
```

Verificar MySQL activo en XAMPP y `DB_CONNECTION=mysql` en `.env`.

> El agente depende de que el **backend responda**. Este error impide heartbeat aunque el agente esté bien configurado.

---

## 13. Firewall y antivirus

### Síntoma

Heartbeat falla solo desde la PC del local; el backend responde en el servidor.

### Verificar

| Elemento | Acción |
|----------|--------|
| Firewall Windows | Permitir salida HTTP/HTTPS del EXE o puerto 80/443 hacia el servidor |
| Antivirus | Excluir `C:\Program Files\NightPOS\PrintAgent\` si bloquea el servicio |
| Red local | PC del local debe alcanzar IP/hostname del servidor (ping, curl) |
| HTTPS | Puerto 443 abierto hacia el servidor en producción |

El agente **solo hace peticiones salientes** (polling). No abre puertos entrantes.

---

## 14. device_key inválida o rotada

### Síntoma

Heartbeat HTTP **401** en logs o curl manual.

### Causa

- Clave copiada incompleta.
- Se rotó la clave en NightPOS y no se actualizó config.json.
- Espacios o saltos de línea al pegar en JSON.

### Solución

1. Admin → Impresoras → **Rotar clave** (si se sospecha compromiso).
2. Copiar nueva clave **una sola vez**.
3. Actualizar `device_key` en ProgramData config.json.
4. Reiniciar servicio.

---

## 15. Connection reset / wsarecv / stream CANCEL (hosting)

### Síntoma en logs

```
[WARN] Error conexión backend: Post ".../print-devices/heartbeat":
read tcp ... wsarecv: Se ha forzado la interrupción...
```

O:

```
stream error: stream ID 1; CANCEL; received from peer
```

### Causa

LiteSpeed/cPanel puede cerrar conexiones HTTP/2 o bloquear el User-Agent por defecto de Go. También puede ocurrir si el hosting está saturado (entry processes).

### Qué hace el agente v2.0+

- Fuerza **HTTP/1.1** (no negocia HTTP/2).
- Envía `User-Agent: NightPOSPrintAgent/2.0`.
- Tras fallo de **red**, aplica **backoff**: 30s → 60s → 120s → 300s máx.
- Tras heartbeat OK, vuelve al `poll_interval_ms` normal.

En logs debe aparecer:

```
[INFO] Backoff hosting: próximo intento en 30s (fallo de red #1)
```

### Diagnóstico

1. Verificar `backend_url` legacy: `https://…/backend/public/api/v1`
2. `poll_interval_ms`: **15000** en producción cPanel
3. Probar desde la misma PC:

```powershell
curl.exe -i -X POST https://nightpos.ribersoft.com/backend/public/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer SU_DEVICE_KEY" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"2.0.0\",\"printer_name\":\"CAJA\"}"
```

| Resultado curl | Acción |
|----------------|--------|
| JSON success | Recompilar/instalar EXE v2.0+ con fix HTTP/1.1 |
| 401 JSON | Revisar `device_key` |
| Reset | Escalar hosting (ModSecurity / entry processes) |

4. Recompilar EXE tras actualizar código — ver `PRINT_AGENT_HTTP1_BACKOFF_FIX_REPORT.md`

---

## 16. Preguntas frecuentes (FAQ)

### ¿Por qué el agente usa ProgramData?

Windows reserva `C:\ProgramData\` para datos de aplicación por máquina, accesibles al servicio del sistema sin depender del usuario logueado. La configuración y logs deben persistir aunque cambie el usuario de Windows.

### ¿Puedo mover el EXE a otra carpeta?

En operación normal el EXE vive en `C:\Program Files\NightPOS\PrintAgent\`. Moverlo manualmente **rompe** el registro del servicio. Use `--uninstall` y `--install` para reinstalar correctamente.

### ¿Cómo cambio de impresora?

1. Instalar/configurar la nueva impresora en Windows.
2. Actualizar `printer_name` en ProgramData `config.json`.
3. Reiniciar servicio.
4. Probar impresión desde admin.

### ¿Qué pasa si cambia el device_key?

El agente deja de autenticarse (401) hasta actualizar `config.json`. Rotar clave en NightPOS invalida la anterior.

### ¿Qué pasa si no hay internet?

El agente pasa a estado 🟡 (sin conexión) y aplica **backoff** (30s→300s) antes de reintentar — no martilla cada 15 s. Los jobs quedan **PENDING** en el servidor. Al volver la conexión, se imprimen en orden.

### ¿Qué pasa si la PC se apaga?

No hay impresión mientras esté apagada. Al encender, el servicio inicia automáticamente (start=auto) y procesa jobs pendientes.

### ¿Qué pasa si la impresora está desconectada?

Los jobs pasan a **FAILED** con mensaje en `last_error`. Al reconectar la impresora, reimprimir desde admin o reenviar operación.

### ¿Cómo reviso los logs?

```
C:\ProgramData\NightPOS\PrintAgent\logs\agent.log
```

Desde bandeja: menú → **Ver logs**.  
Desde PowerShell: `notepad C:\ProgramData\NightPOS\PrintAgent\logs\agent.log`

### ¿Cómo cambio de sucursal?

Cada `device_key` está ligada a **una sucursal**. Para otra sucursal:

1. Registrar **nuevo dispositivo** en NightPOS (en la sucursal correcta).
2. Usar la nueva `device_key` en el config del agente de esa PC.
3. Reiniciar servicio.

### ¿El garzón necesita el agente en el celular?

**No.** El garzón usa el navegador/móvil. Solo la **PC del local** con impresora USB necesita el agente.

### ¿Puedo usar el agente Node.js (`agent/src/`)?

**No.** La versión Node quedó obsoleta. Usar únicamente `NightPOSPrintAgent.exe` (Go).

### ¿Necesito NSSM?

**No.** El agente instala servicio Windows nativo con `--install`.

---

**Ribersoft — NightPOS V1**  
*Documento oficial de soporte. Ante incidentes no cubiertos aquí, escalar al equipo de desarrollo Ribersoft con logs adjuntos.*
