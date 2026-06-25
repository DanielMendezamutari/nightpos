# PRINT_AGENT_ORDER_COMMAND_DEBUG_REPORT.md (Frontend)

**Fecha:** 2026-06-25

---

## Síntoma

Garzón envía comanda desde celular; operador espera ticket en impresora USB del local.

El frontend garzón **no imprime directamente** — solo llama `POST /orders/{id}/send-to-bar`. La impresión es **asíncrona** vía agente Windows.

---

## Flujo frontend garzón

1. Waiter UI → Enviar a barra
2. API `send-to-bar` → backend crea `print_jobs` si hay dispositivo registrado
3. PC local con agente → poll → imprime

El garzón **no necesita** agente en el celular.

---

## UI admin actualizada

Ruta: `/nightpos/settings/printers`

| Feature | Estado |
|---------|--------|
| Online/Offline chip | ✅ |
| Última conexión | ✅ |
| Resumen cola (pendientes/fallidos/último job) | ✅ Nuevo |
| Último job por dispositivo | ✅ Nuevo |
| Probar impresión | ✅ Nuevo (`POST test-print`) |
| Copiar device_key | ✅ |
| Instrucciones instalación Go EXE | ✅ (reemplaza texto Node.js obsoleto) |

Archivo: `frontend/src/pages/nightpos/settings/printers/index.vue`

API: `frontend/src/api/printDevices.js` → `testPrintDevice()`

---

## Mensajes obsoletos corregidos

- Antes: *"Instale el agente Node.js"*
- Ahora: *NightPOSPrintAgent.exe* + servicio Windows + rutas ProgramData

---

## Qué ver en admin cuando falla

| UI | Significado |
|----|-------------|
| Offline + jobs PENDING subiendo | Agente apagado o mal configurado |
| Online + PENDING=0 + no imprime | printer_name incorrecto (job FAILED en historial) |
| Sin dispositivos | No se crean jobs ORDER_COMMAND |
| Auto impresión OFF | No se crean jobs al send-to-bar |

---

## Proxy dev

`VITE_BACKEND_URL=http://nightpos.test` — el garzón en dev usa proxy Vite; el **agente** debe apuntar al mismo backend en `config.json`.

---

## Referencias

- `backend/PRINT_AGENT_ORDER_COMMAND_DEBUG_REPORT.md`
- `agent/README_WINDOWS.md`
