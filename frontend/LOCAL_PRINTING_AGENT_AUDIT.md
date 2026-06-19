# Auditoría / Diseño — Impresión local automática (Frontend)

**Fecha:** 2026-06-17  
**Tipo:** Auditoría + diseño UX  
**Estado:** **NO IMPLEMENTADO**

---

## 1. Estado actual

### Impresión V1-97 (implementada)

| Ruta | Documento | Trigger |
|------|-----------|---------|
| `/nightpos/print/order/:id` | Comanda barra | Botón «Imprimir barra» |
| `/nightpos/print/precheck/order/:id` | Precuenta | Garzón detalle comanda |
| `/nightpos/print/sale/:id` | Ticket venta | Ventas / diálogo detalle |
| `/nightpos/print/cash` | Arqueo caja | Mi caja |
| `/nightpos/print/cash-session/:id` | Fiscalización | Admin |
| `/nightpos/print/shift/:id` | Cierre turno | Cierre / historial |

**Mecanismo:** `useNightPosPrint()` → `window.open()` + `?print=1` + `window.print()`.

**Limitación:** Desde celular del garzón abre impresión en **el celular** (inútil para USB en PC). No hay auto-print hacia PC sucursal.

### Componentes reutilizables (futuro)

| Componente | Datos |
|------------|-------|
| `PrintableOrderTicket.vue` | order, waiter, items, allocations |
| `PrintablePrecheckTicket.vue` | precheck API |
| `PrintableSaleTicket.vue` | sale, payments |
| `PrintableCashSessionReport.vue` | caja |
| `PrintableTicketShell.vue` | 58/80 mm CSS |

**Nota diseño:** Backend generará `content_text`; frontend Vue **no** duplica render para agente. Vue sigue para vista manual/PDF navegador.

### Configuración

| Pantalla | Estado |
|----------|--------|
| `settings/printers/index.vue` | **Placeholder** — «Integración impresoras térmicas — pendiente» |
| Nav R4 enlace Impresoras | Visible sin permiso estricto |

### Auto print

| Feature | Estado |
|---------|--------|
| `triggerAutoPrint` | Solo en **pestaña impresión abierta en mismo dispositivo** |
| Cola print_jobs UI | ❌ |
| Notificación fallo impresión | ❌ |
| Indicador «impreso en barra» | ❌ |

---

## 2. Limitación UX celular → PC

```
Garzón celular → Send to bar → ✅ comanda en sistema
                             → ❌ no imprime en PC barra (hoy)

Solución: backend crea print_job → agente PC imprime
Garzón NO necesita acción extra si auto_print activo
```

**Flujo ideal garzón:**

1. Confirma comanda / envía a barra.
2. Toast: «Comanda enviada — impresión en barra».
3. Si falla agente: toast warning + badge en detalle (SSE).

---

## 3. UI administración propuesta

### Configuración → Impresoras (`settings/printers`)

Reemplazar placeholder.

**Sección A — Dispositivo local**

| Elemento | Descripción |
|----------|-------------|
| Lista devices | Nombre, sucursal, online/offline, última conexión, impresora Windows |
| Botón «Registrar dispositivo» | Modal: nombre → API devuelve **device_key** (copiar una vez) |
| Instrucciones | Descargar agente, pegar key, seleccionar impresora |
| Rotar key / Desactivar | Acciones admin |

**Sección B — Políticas sucursal**

| Toggle | Default |
|--------|---------|
| Auto imprimir comanda al enviar a barra | ON |
| Auto imprimir ticket al cobrar | OFF |
| Auto imprimir al cerrar caja | OFF |
| Ancho papel | 80 mm |

**Sección C — Historial**

Tabla jobs recientes: hora, tipo, comanda/venta, estado, error, reimprimir.

### Permisos UI sugeridos

| Permiso | UI |
|---------|-----|
| `printing.devices.manage` | CRUD devices, ver keys |
| `printing.jobs.view` | Historial |
| `printing.reprint` | Botón reimprimir |
| `printing.configure` | Toggles auto-print |

Cajera básica: solo ver estado «impreso/pendiente/error» en comanda (lectura).

---

## 4. Cambios en pantallas operativas

### Garzón — detalle comanda (`waiter/orders/[id].vue`)

| Elemento | Cambio |
|----------|--------|
| Tras «Enviar a barra» | Toast incluye estado impresión |
| Badge | 🖨️ Impreso / ⏳ Pendiente / ⚠️ Error |
| «Imprimir precuenta» | Crea job `PRECHECK` **o** mantiene vista manual (ambos V1) |
| «Reimprimir comanda» | `POST /orders/{id}/reprint` (nuevo botón junto a enviar) |

**Recomendación:** Mantener botón manual «Ver ticket» como fallback si agente caído.

### Detalle comanda admin/cajera (`orders/[id].vue`)

- Mismo badge estado impresión.
- «Imprimir barra» → dual: crea job **+** opción «Abrir vista» (legacy).

### Ventas / cobro

- Opcional V1: checkbox «Imprimir ticket» al cobrar (crea `SALE_RECEIPT` job).
- Si auto_print_sale: sin checkbox, automático.

### Mi caja / cierre

- V1.1: job `CASH_CLOSE` + botón manual existente.

### Consola / dashboard

- Widget opcional: «Impresora barra: online» (heartbeat).

---

## 5. Notificaciones fallo (pregunta 12)

| Canal | Cuándo |
|-------|--------|
| SSE `print_job.failed` | Tiempo real |
| Snackbar cajera | Si está en cola cobro / detalle comanda relacionada |
| Lista jobs admin | Siempre |
| Badge rojo device | Admin impresoras |

**Copy sugerido:** «No se pudo imprimir comanda #152 en barra — revisar impresora.»

Acción: «Reimprimir».

---

## 6. Reimpresión UX (pregunta 7)

1. Botón «Reimprimir» visible con permiso.
2. Confirmación opcional: «¿Imprimir otra copia?»
3. Nuevo job → agente imprime.
4. Historial muestra copia 1, 2, 3.

---

## 7. Multisucursal (frontend)

- Admin superadmin/owner: selector sucursal en settings impresoras.
- Device siempre ligado a **una** sucursal — UI muestra branch name claro.
- Usuario operativo solo ve devices de su contexto (`X-Branch-Code`).

---

## 8. Formato ticket — impacto frontend

**V1:** Frontend **no renderiza** para agente. Solo muestra preview opcional en admin (monospace textarea readonly del `content_text` del job).

**Vista imprimible legacy:** Sigue usando CSS 80 mm — útil backup.

---

## 9. Plan por fases (frontend)

| Fase | Entregable | Días |
|------|------------|------|
| **PRINT-FE-1** | Reemplazar placeholder `settings/printers` | 2 |
| **PRINT-FE-2** | Badge + reprint en detalle comanda garzón/cajera | 1–2 |
| **PRINT-FE-3** | Historial jobs + SSE handlers | 1–2 |
| **PRINT-FE-4** | Cobro: checkbox ticket / auto | 1 |
| **PRINT-FE-5** | Wizard «Primera noche» paso impresora | 0.5 |

**Dependencia:** Backend PRINT-1…5 + agente PRINT-4.

---

## 10. Qué entra en V1 / V1.1 / V2

### V1 UI

- Admin impresoras completo (registro device, key, estado)
- Badge impresión en comanda
- Reimprimir comanda (job)
- Precheck → job manual
- Historial jobs
- Mantener **toda** impresión `window.print()` existente

### V1.1

- Preview `content_text` en admin
- Widget online en consola turno
- Auto sale receipt UI
- Push notification sonora fallo (opcional)

### V2

- Electron configurator embebido
- Mapa impresoras por categoría producto
- QR en ticket para tracking

---

## 11. Validación manual (con agente)

1. Registrar device en admin → copiar key.
2. Instalar agente PC → configurar key + impresora.
3. Garzón celular → enviar barra.
4. Ticket sale en USB **sin** abrir navegador en PC.
5. Badge «Impreso» en celular garzón.
6. Apagar impresora → failed → badge error → reimprimir OK.
7. Apagar PC → jobs pending → encender → imprime backlog.
8. Segunda sucursal no recibe tickets de la primera.

---

## 12. Riesgos UX

| Riesgo | Mitigación |
|--------|------------|
| Admin pierde device_key | Rotar key |
| Garzón no sabe si imprimió | Badge + toast |
| Confusión print manual vs auto | Copy claro; manual como «backup» |
| Placeholder impresoras confunde | Implementar o ocultar hasta PRINT-FE-1 |

---

*Complementa `backend/LOCAL_PRINTING_AGENT_AUDIT.md` y `agent/LOCAL_PRINTING_AGENT_AUDIT.md`.*
