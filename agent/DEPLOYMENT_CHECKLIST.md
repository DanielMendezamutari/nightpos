# NightPOS — Checklist de despliegue del Agente de Impresión

**Producto:** NightPOS V1  
**Componente:** Agente de Impresión Local  
**Editor:** Ribersoft  
**Versión del documento:** 1.0 — 2026-06-25

---

## Uso de este documento

Completar **todos** los ítems antes de dar por cerrada la instalación en una sucursal nueva o reinstalación. Marcar cada casilla al verificar.

**Responsable de instalación:** _______________________  
**Sucursal:** _______________________  
**Fecha:** _______________________  
**PC del local (hostname):** _______________________  
**Impresora (modelo / nombre Windows):** _______________________

---

## A. Servidor NightPOS (backend)

| ☐ | Verificación | Notas |
|---|--------------|-------|
| ☐ | Backend accesible desde la red del local | URL: _________________ |
| ☐ | API responde: `GET /api/v1/auth/login-context/tenants` → HTTP 200 | |
| ☐ | MySQL (o BD configurada) activa | |
| ☐ | Migraciones aplicadas (`php artisan migrate`) | |
| ☐ | Tablas `print_devices` y `print_jobs` existen | |
| ☐ | Cache de config limpia si hubo cambios de `.env` (`php artisan optimize:clear`) | |
| ☐ | Usuario admin de sucursal puede iniciar sesión | |

---

## B. Registro en NightPOS (panel web)

| ☐ | Verificación | Notas |
|---|--------------|-------|
| ☐ | **Auto impresión comanda** activada (si aplica) | |
| ☐ | **Auto impresión ticket cobro** activada (si aplica) | |
| ☐ | Dispositivo registrado en **Configuración → Impresoras** | Nombre: _________ |
| ☐ | `device_key` copiada y guardada en lugar seguro | Prefijo `npd_live_` |
| ☐ | Resumen de cola visible (pendientes / fallidos) | |

---

## C. PC del local — hardware y Windows

| ☐ | Verificación | Notas |
|---|--------------|-------|
| ☐ | Windows 10/11 operativo | |
| ☐ | Impresora térmica USB conectada | |
| ☐ | Driver ESC/POS instalado | |
| ☐ | Impresora visible en Windows con nombre conocido | Nombre exacto: _________ |
| ☐ | Página de prueba de Windows imprime OK | |
| ☐ | PC tiene red al servidor NightPOS | ping / curl OK |

---

## D. Agente de impresión

| ☐ | Verificación | Notas |
|---|--------------|-------|
| ☐ | `NightPOSPrintAgent.exe` disponible (compilado o entregado) | Versión: _________ |
| ☐ | Servicio instalado (`--install` como Administrador) | |
| ☐ | `config.json` editado en **ProgramData** (no en repo) | Ruta verificada |
| ☐ | `backend_url` correcto para este entorno | |
| ☐ | `device_key` pegada correctamente | |
| ☐ | `printer_name` = nombre exacto Windows | |
| ☐ | `dry_run` = `false` en producción | |
| ☐ | Servicio **RUNNING** (`--status`) | |
| ☐ | Dispositivo **Online** en admin NightPOS | |
| ☐ | Log `agent.log` sin errores de heartbeat | |

---

## E. Pruebas de impresión por tipo de job

Ejecutar cada prueba y confirmar estado **PRINTED** en Admin → Impresoras → Historial (salvo indicación contraria).

| ☐ | Tipo | Cómo probar | Job type | PRINTED |
|---|------|-------------|----------|---------|
| ☐ | **Prueba admin** | Botón **Probar** en Configuración → Impresoras | TEST | ☐ |
| ☐ | **Comanda barra** | Garzón → Enviar a barra | ORDER_COMMAND | ☐ |
| ☐ | **Precuenta** | Garzón o cajera → Precuenta | PRECHECK | ☐ |
| ☐ | **Ticket cobro** | Cobrar venta/comanda con auto-print activo | SALE_RECEIPT | ☐ |
| ☐ | **Movimiento caja** | Registrar ingreso/egreso con impresión | CASH_MOVEMENT | ☐ |
| ☐ | **Cierre caja** | Cerrar sesión de caja con ticket | CASH_CLOSE | ☐ |
| ☐ | **Cierre turno** | Cerrar turno oficial con ticket | SHIFT_CLOSE | ☐ |
| ☐ | **Pieza / room service** | Operación de pieza con ticket | ROOM_SERVICE | ☐ |
| ☐ | **Show** | Operación show con ticket | SHOW_TICKET | ☐ |
| ☐ | **Reimpresión comanda** | Admin → Reimprimir comanda | ORDER_COMMAND | ☐ |

> Si algún tipo no aplica a la sucursal, marcar N/A y anotar motivo.

---

## F. Pruebas de resiliencia (recomendadas)

| ☐ | Escenario | Resultado esperado | OK |
|---|-----------|-------------------|-----|
| ☐ | Apagar agente → enviar comanda → encender agente | Job PENDING luego PRINTED | ☐ |
| ☐ | Desconectar impresora → enviar comanda | Job FAILED con mensaje claro | ☐ |
| ☐ | Reconectar impresora → reimprimir | Job PRINTED | ☐ |
| ☐ | Reiniciar Windows | Servicio inicia solo; dispositivo Online | ☐ |
| ☐ | Sin consola abierta → comanda garzón | Imprime normalmente | ☐ |

---

## G. Cierre y entrega

| ☐ | Verificación | Notas |
|---|--------------|-------|
| ☐ | Logs revisados sin errores críticos | |
| ☐ | Backup de `C:\ProgramData\NightPOS\PrintAgent\config.json` | Ubicación backup: _________ |
| ☐ | Técnico del local capacitado: dónde ver Online/Offline | |
| ☐ | Técnico sabe cómo reiniciar servicio (`restart-service.bat`) | |
| ☐ | Documentación entregada: INSTALLATION_GUIDE + TROUBLESHOOTING | |
| ☐ | Contacto soporte Ribersoft comunicado al cliente | |

---

## H. Registro de incidencias (si aplica)

| # | Incidencia | Resolución | Fecha |
|---|------------|------------|-------|
| 1 | | | |
| 2 | | | |
| 3 | | | |

---

## Firma de conformidad

**Instalación completada y verificada:**

| Rol | Nombre | Firma | Fecha |
|-----|--------|-------|-------|
| Técnico Ribersoft | | | |
| Responsable sucursal | | | |

---

**Documentos de referencia**

- [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md)
- [TROUBLESHOOTING_GUIDE.md](./TROUBLESHOOTING_GUIDE.md)
- [README_WINDOWS.md](./README_WINDOWS.md)

---

**Ribersoft — NightPOS V1**  
*Checklist oficial de despliegue. Archivar copia firmada por sucursal.*
