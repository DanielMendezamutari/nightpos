# Auditoría operativa final — NightPOS

**Fecha:** 2026-06-04  
**Alcance:** Simulación de una noche real (código + UX actual). **No incluye implementación.**  
**Contexto:** Quick Actions Fase A y B aplicadas; 163 tests backend pasando.

---

## 1. Escenario simulado

| Rol | Usuario demo (seeder) | PIN | Notas |
|-----|----------------------|-----|--------|
| Administrador | `admin.demo` | `2468` | Password `AdminDemo123!`; catálogo, habitaciones, precios, personal |
| Cajera | `cajero.demo` / cajera | `1234` | Caja, cobro, liquidaciones, quick girl/show |
| Garzón 1 | `garzon.demo` | `5678` | Comandas; **sin** `sales.charge` ni `cash.access` |
| Garzón 2 | *(no existe en seeder)* | — | Debe crearse vía `POST /staff/quick-waiters` (Fase B) |
| Chica 1 | `chica.centro` | — | Una sola en demo; +2 vía quick girl |
| Chica 2–3 | — | — | Alta rápida en comanda / manillas / shows |
| Limpieza | `limpieza.demo` | `3333` | Control piezas, `room_services.cleaning_view` |

**Sucursal:** `casa-demo` / `CENTRO` (cookies de login PIN).

**Flujo nocturno modelado:** login → contexto → apertura caja (cajera) → comandas (garzones/cajera) → servicios → cobros → liquidación → cierre caja por usuario → cierre turno (admin).

---

## 2. Resumen ejecutivo

| Área | ¿Puede completarse la noche? | Riesgo global |
|------|------------------------------|---------------|
| Apertura | Sí, con fricción de contexto/caja por usuario | Medio |
| Comandas + cobro | Sí, salvo catálogo vacío o precios faltantes | Medio–Alto |
| Caja | Sí; pago mixto y cambio implementados | Bajo–Medio |
| Servicios | Sí con quick actions Fase A/B | Bajo |
| Habitaciones | Sí; **bloqueo si limpieza no libera** | Alto (operativo) |
| Limpieza | Sí si pantalla abierta; débil si offline | Medio |
| Liquidaciones | Sí; piezas ACTIVE no liquidan (avisado Fase B) | Medio |
| Superadmin | Sí; wizard Fase B reduce pasos | Bajo |

**Conclusión:** La operación **no se rompe** en el happy path con datos precargados. Los riesgos restantes son **comisiones de garzón mal asignadas**, **habitaciones en CLEANING**, **catálogo/producto nuevo sin quick action**, y **cierre coordinado** (caja + turno).

---

## 3. Auditoría por fase de la noche

### 3.1 Apertura

| Paso | Estado actual | ¿Bloquea? | Pasos / clics |
|------|---------------|-----------|----------------|
| Login PIN | `tenant_slug` + `branch_code` en login (recordables en cookie) | No si contexto recordado | 1 pantalla |
| Cambio sucursal | Navbar / `useContextStore` + refresh operativo | No; superadmin debe elegir sucursal | 2–3 clics |
| Apertura caja | Página Caja o **QuickOpenCashDialog** desde cobro (Fase A) | Sí sin caja → cobro bloqueado (mitigado) | 1 modal o 1 página |
| Turno oficial | `EnsureOperationalShift` en primera operación (caja, comanda, servicio) | No requiere abrir turno manual | 0 clics extra |

**Hallazgos**

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| A-01 | Caja es **por usuario** (`findOpenForUser`); otra cajera no comparte sesión | Medio | Caja por sucursal/caja física o handoff explícito | P2 | Dos cajeras en pico | Documentar “una caja activa por cajero” |
| A-02 | Cierre de **turno oficial** exige **todas** las sesiones de caja cerradas | Medio | Dashboard “quién tiene caja abierta” + alerta | P2 | Retraso al cerrar noche | Cerrar cajas antes de turno |
| A-03 | Superadmin sin sucursal: operación limitada hasta elegir branch | Bajo | Wizard + guía en dashboard (parcial Fase B) | P3 | Solo onboarding | Usar `/nightpos/platform/setup` |

---

### 3.2 Comandas

| Paso | Estado actual | ¿Bloquea? |
|------|---------------|-----------|
| Crear comanda | `table_label` obligatorio; garzón = usuario logueado | No |
| Agregar producto | Modal fullscreen; precio desde catálogo | Sin precio → bloqueo + alerta (Fase B) |
| CON_ACOMPANANTE | Asignar chica al enviar (`AssignGirlModal` + **QuickGirlCreateDialog**) | Sin chica → no envía / no cobra |
| Cobrar | `ChargeOrderModal`; caja abierta; chicas asignadas | Garzón sin permiso cobro |

**Hallazgos**

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| C-01 | **Cajera** que abre comanda deja `waiter_user_id` = usuario logueado (cajera), no garzón real | **Alto** | Selector garzón en `orders/new.vue` + quick waiter (Fase B solo en manillas) | **P1** | Comisión garzón incorrecta en liquidación | Garzones deben abrir sus comandas con su PIN |
| C-02 | No hay **QuickProductCreate**; catálogo vacío = no vende | Alto | `QuickProductCreateDialog` (Fase C) | P1 | Producto del día | Precargar catálogo antes del turno |
| C-03 | Configurar precio en comanda requiere `product_prices.quick_create` o `products.update` (cajera no tiene por defecto) | Medio | Delegar permiso a cajera o rol “precio turno” | P2 | Bloqueo pico sin admin | Admin en sala o permiso delegado |
| C-04 | Agregar producto: modal **fullscreen** + búsqueda + modalidad = muchos clics | Medio | Panel inline o favoritos | P2 | Velocidad en pico | Aceptable si catálogo corto |
| C-05 | Garzón no cobra (`sales.charge`) — traspaso a cajera | Bajo (diseño) | Flujo “solicitar cobro” opcional | P3 | Coordinación | Por diseño RBAC |
| C-06 | Mesa/cliente = **texto libre** (`table_label`) | Bajo | Catálogo mesas (Fase C) | P4 | Reportes | OK operativamente |

**¿Se puede terminar venta sin salir del flujo?**  
**Sí**, si: caja abierta (o quick open), precios existentes, chicas asignadas en CON_ACOMPANANTE. **No** si falta producto/precio sin permiso admin.

**Acciones repetitivas:** modalidad + producto en cada línea; asignación de chica por ítem en envío.

---

### 3.3 Caja

| Operación | Implementación | Salir de pantalla |
|-----------|----------------|-------------------|
| Abrir | Caja / quick desde cobro | No (con Fase A) |
| Ingreso / Egreso | Modal en `cash/index.vue`; `payment_method` default CASH en API | No |
| Cobro | Desde comanda; registra movimientos INCOME | No |
| Pago mixto | `MIXED` + validación suma + cambio en efectivo | No |

**Hallazgos**

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| K-01 | Movimientos manuales: **motivo** solo en `description` (texto libre) | Medio | Catálogo motivos ingreso/egreso | P2 | Arqueo / auditoría | Fase C3 |
| K-02 | UI movimiento **no expone** `payment_method` (siempre CASH backend) | Bajo | Campo opcional en modal | P3 | Reportes caja | Mejora admin |
| K-03 | Métodos de cobro comanda: enum fijo CASH/QR/CARD/MIXED | Bajo | Config por tenant (Fase C) | P4 | — | Suficiente hoy |
| K-04 | Información duplicada: totales comanda + desglose pago | Bajo | — | P4 | — | Aceptable |

---

### 3.4 Servicios (manillas, piezas, shows)

| Servicio | Quick actions | ¿Abandonar flujo? |
|----------|---------------|-------------------|
| Manillas | Chica + garzón (quick) | No |
| Piezas | Chica + habitación (quick) | No si hay AVAILABLE o `rooms.create` |
| Shows | Chica + tipo show (quick) | No si hay tipos o se crean al vuelo |

**Hallazgos**

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| S-01 | Cajera sin `rooms.create`: no puede **QuickRoom** si no hay habitación | Medio | Delegar permiso o habitaciones precargadas | P2 | Pico sin admin | Admin crea habitaciones al inicio |
| S-02 | Pieza con `room_id`: al **terminar** → habitación `CLEANING` (correcto) | — | — | — | Flujo esperado | Limpieza debe marcar limpia |
| S-03 | Pieza sin `room_id` (solo `room_label`): no sincroniza estado habitación | Bajo | Forzar selector habitación | P3 | Inventario | Usar habitación catalogada |
| S-04 | Show types vacíos en tenant nuevo hasta crear | Medio | Seeder tipos demo + quick (Fase B) | P2 | Primer show | Crear tipos en apertura |
| S-05 | Garzón en manilla es **opcional** — OK | Bajo | — | — | — | — |

**Datos obligatorios innecesarios:** `registered_at` opcional en show/manilla — adecuado.

---

### 3.5 Habitaciones

Estados: `AVAILABLE` → `OCCUPIED` (pieza) → `CLEANING` (fin pieza) → `AVAILABLE` (`mark-clean`); `MAINTENANCE` aparte.

| Pregunta | Respuesta |
|----------|-----------|
| ¿Puede quedar bloqueada? | **Sí**, en `CLEANING` o `MAINTENANCE` sin acción de limpieza/admin |
| ¿Estados imposibles? | Backend evita doble `OCCUPIED` (`roomNotAvailable`); no hay auto-recuperación |

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| H-01 | Habitación en **CLEANING** bloquea nueva pieza hasta `mark-clean` | **Alto** | Alerta en registrar pieza + lista alternativas | **P1** | Sin habitaciones vendibles | Priorizar cola limpieza |
| H-02 | Error `roomNotAvailable` sin sugerir otras habitaciones | Medio | Modal con link a `/rooms/available` | P2 | Fricción cajera | Fase C |
| H-03 | `room_type` enum STANDARD/VIP/SUITE (catálogo fijo) | Bajo | Config tipos habitación | P4 | — | OK |

---

### 3.6 Limpieza

| Función | Implementación |
|---------|----------------|
| Vista | `room-control` — activas, vencidas, terminadas hoy |
| Polling | 30 s + refresh al cambiar contexto |
| Sonido | `/sounds/room-due.mp3` + beep fallback; silencio en localStorage |
| Notificaciones | API `notifications` + contador no leídas |
| Acciones | Marcar revisada (`check`), terminar pieza (`finish`) |

| Pregunta | Respuesta |
|----------|-----------|
| ¿Limpieza desconectada? | Piezas vencen en UI al reconectar; **sin push** nativo; sonido solo con pestaña abierta |
| ¿Pieza ya terminada? | Aparece en “terminadas hoy”; `finish` solo ACTIVE |

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| L-01 | Sin pestaña abierta: **no hay alerta sonora** fiable | Medio | Push / WhatsApp (config `NIGHTPOS_WHATSAPP_PHONE_CLEANING`) | P2 | Piezas vencidas tarde | Turno limpieza con tablet fija |
| L-02 | `finish` en control termina pieza **y** pone habitación en limpieza — rol limpieza hace doble función | Medio | Separar “fin servicio” (cajera) vs “limpia” | P3 | Proceso local | Capacitar roles |
| L-03 | IDs “vistos” en localStorage — otro dispositivo repite alerta | Bajo | Server-side ack | P4 | — | — |

---

### 3.7 Liquidaciones

Fuentes: ventas (comisión garzón), ítems CON_ACOMPANANTE, manillas, piezas **FINISHED**, shows. Protección: `sourceAlreadySettled` / `saleItemAlreadySettled`.

| Concepto | ¿Entra? | Notas |
|----------|---------|-------|
| Garzones | Sí, si % en perfil y ventas con `waiter_user_id` correcto | Ver C-01 |
| Chicas consumo | Sí, con `can_receive_girl_commissions` |
| Manillas / shows | Sí, al generar |
| Piezas ACTIVE | **No** | Banner Fase B en resumen |
| Piezas FINISHED no generadas aún | Cuentan en `unpaid_finished_*` (API pending-sources) |

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| Li-01 | Garzón mal asignado en comanda (cajera) distorsiona comisiones | **Alto** | Corregir C-01 | **P1** | Pago incorrecto | Validar antes de generate |
| Li-02 | Chica sin `can_receive_girl_commissions` — líneas omitidas | Medio | Banner en `settlements/girls.vue` (audit original) | P2 | Chica no cobra | Checklist admin |
| Li-03 | Regenerar liquidación: skip fuentes ya liquidadas — **bajo riesgo duplicado** | Bajo | — | — | — | OK |
| Li-04 | `mark-paid` sin doble pago automático si ya PAID | Bajo | Idempotencia UI | P3 | — | Procedimiento |
| Li-05 | Piezas activas: cajera puede no entender monto “faltante” | Medio | Banner Fase B (hecho) + tooltip en generate | P2 | Confianza | Explicar en capacitación |

---

### 3.8 Superadmin / SaaS

| Paso | Estado (post Fase B) |
|------|----------------------|
| Crear empresa + sucursal + admin | `/nightpos/platform/setup` transaccional |
| Pantallas clásicas | Siguen existiendo |
| Operar contexto | `applyContext` + cookies |

| # | Problema | Riesgo | Solución propuesta | Prioridad | Impacto | Recomendación |
|---|----------|--------|-------------------|-----------|---------|---------------|
| Sa-01 | Tenant nuevo sin productos/habitaciones/tipos show | Medio | Wizard paso “datos demo” o checklist | P2 | Primera noche vacía | Script setup post-wizard |
| Sa-02 | Puede quedar sin contexto branch | Medio | Alert dashboard (parcial) | P2 | 403 en APIs | Siempre “Operar en esta empresa” |
| Sa-03 | Pasos duplicados si no usa wizard | Bajo | Promover setup en nav | P3 | — | OK |

---

## 4. Auditoría especial

### 4.1 Datos maestros — texto libre vs catálogo

| Dato | Tipo actual | Catálogo / enum |
|------|-------------|-----------------|
| Mesa / cliente | Texto libre | `table_label` |
| Métodos de pago (cobro) | Enum fijo | CASH, QR, CARD, MIXED |
| Métodos de pago (mov. caja manual) | Default CASH; opcional en API | Parcial |
| Tipos de show | **Catálogo** `show_types` (Fase B) | Sí |
| Tipos de habitación | Enum `STANDARD/VIP/SUITE` | Fijo en código |
| Estados pieza | ACTIVE, FINISHED, CANCELLED | Fijo |
| Estados habitación | AVAILABLE, OCCUPIED, CLEANING, MAINTENANCE | Fijo |
| Motivos ingreso/egreso caja | Texto libre (`description`) | No |
| Categoría producto | Catálogo | Sí |
| Precios producto | Catálogo por modalidad | Sí |
| Garzón / chica | Usuarios + quick create | Sí (operativo) |
| Cliente entidad | No existe | — |
| Turno oficial | Auto + cierre manual | Semi |

### 4.2 Riesgos de operación (clasificación)

| Riesgo | ID | Descripción |
|--------|-----|-------------|
| **Alto** | C-01 | Comanda abierta por cajera con garzón = cajera |
| **Alto** | H-01 | Habitaciones en CLEANING sin limpieza → sin venta piezas |
| **Alto** | C-02 | Sin producto en catálogo no hay venta rápida |
| **Medio** | C-03 | Precio rápido sin permiso cajera |
| **Medio** | A-01 / A-02 | Caja por usuario y cierre turno vs cajas abiertas |
| **Medio** | Li-01 / Li-02 | Comisiones y flags chica |
| **Medio** | L-01 | Limpieza sin pantalla activa |
| **Medio** | S-01 | Quick habitación sin permiso cajera |
| **Bajo** | C-05, K-02, Sa-03 | Diseño / UX menor |

### 4.3 Cuellos de botella (clics y fricción)

| Cuello | Severidad | Detalle |
|--------|-----------|---------|
| Modal fullscreen agregar producto | Medio | 4–6 interacciones por línea |
| Asignar chica por ítem al enviar | Medio | Necesario por negocio |
| Ir a Caja solo si no usan quick open | Bajo (mitigado Fase A) |
| Cierre: N cajeros → N cierres de caja + 1 cierre turno | Medio | Proceso fin de noche |
| Navegación entre módulos (comandas ↔ servicios ↔ liquidaciones) | Medio | Sin “consola única” |
| Login PIN con cambio tenant poco frecuente | Bajo | Cookies ayudan |

---

## 5. Matriz flujo × rol (noche simulada)

| Actividad | Admin | Cajera | Garzón | Limpieza |
|-----------|-------|--------|--------|----------|
| Abrir caja | Sí | Sí | No | No |
| Crear comanda | Sí | Sí (⚠ garzón) | Sí (OK) | No |
| Cobrar | Sí | Sí | No | No |
| Quick chica | Sí | Sí | No | No |
| Quick habitación | Sí | Solo si permiso | No | No |
| Registrar pieza | Sí | Sí | No | No |
| Marcar habitación limpia | Sí | No | No | Sí |
| Generar liquidación | Sí | Sí | No | No |
| Cerrar turno | Sí | No (típico) | No | No |

---

## 6. Qué ya está resuelto (Fase A + B)

No re-auditar como bloqueantes:

- Quick chica en comanda, manillas, shows, piezas  
- Quick habitación en pieza  
- Quick categoría en producto  
- Abrir caja desde cobro  
- Quick precio en comanda (con permiso)  
- Quick garzón en manillas  
- Quick tipo show + precio sugerido  
- Wizard SaaS empresa + sucursal + admin  
- Banner piezas activas en liquidaciones  

---

## 7. Roadmap propuesto (sin implementar)

### Fase C1 — Correcciones críticas (operación noche)

| Item | Objetivo |
|------|----------|
| C1-1 | Selector de **garzón** en nueva comanda (+ quick waiter reutilizado) |
| C1-2 | Validación UI: si quien abre comanda no es WAITER, obligar elegir garzón |
| C1-3 | **QuickProductCreateDialog** mínimo (nombre + precio SOLO/CON_ACOMPANANTE) |
| C1-4 | Panel habitaciones: piezas en CLEANING + CTA “marcar limpia” / alternativas al error 422 |
| C1-5 | Banner liquidaciones: garzones sin % y chicas sin flag comisión |

**Impacto:** Evita dinero mal liquidado y venta detenida por inventario.

---

### Fase C2 — Mejoras operativas

| Item | Objetivo |
|------|----------|
| C2-1 | Consola turno: cajas abiertas, piezas activas, comandas pendientes cobro |
| C2-2 | Agregar producto más compacto (drawer / favoritos) |
| C2-3 | Push o sonido persistente limpieza (service worker / integración) |
| C2-4 | Delegación permisos: `rooms.create` / `product_prices.quick_create` a cajera senior |
| C2-5 | Seeder / plantilla: 2 garzones, 3 chicas, tipos show, precios demo |

---

### Fase C3 — Mejoras administrativas

| Item | Objetivo |
|------|----------|
| C3-1 | Catálogo **motivos** ingreso/egreso caja |
| C3-2 | Métodos de pago configurables por tenant |
| C3-3 | Catálogo **mesas/ambientes** |
| C3-4 | Post-setup: checklist “primera noche” (productos, habitaciones, personal) |
| C3-5 | Tipos de habitación configurables |

---

### Fase C4 — Reportes

| Item | Objetivo |
|------|----------|
| C4-1 | Cierre de caja / arqueo PDF |
| C4-2 | Resumen turno (ventas, servicios, liquidaciones) |
| C4-3 | Comisiones por garzón/chica exportables |

---

### Fase C5 — Impresión automática

| Item | Objetivo |
|------|----------|
| C5-1 | Ticket comanda / barra |
| C5-2 | Ticket cobro |
| C5-3 | Comanda limpieza (habitación vencida) |

---

## 8. Validación manual sugerida (noche de prueba)

1. Login cada rol (PIN) con `casa-demo` / `CENTRO`.  
2. Cajera: abrir caja → comanda CON_ACOMPANANTE → quick chica → cobrar (mixto).  
3. Garzón: abrir comanda propia → agregar producto → cajera cobra.  
4. Registrar pieza → terminar → limpieza marca limpia → nueva pieza misma habitación.  
5. Manilla + show con quick types.  
6. Generar liquidación → revisar garzones/chicas → marcar pagado.  
7. Cerrar cajas → cerrar turno.  
8. Superadmin: wizard tenant nuevo (staging).  

Registrar tiempos y cualquier 422 en consola.

---

## 9. Cierre

NightPOS está **operable para una noche demo** con datos precargados y Quick Actions A/B. Los puntos que aún pueden **romper o distorsionar** la operación real son principalmente: **asignación de garzón en comandas**, **inventario de habitaciones en limpieza**, y **altas de producto/precio sin permiso**. El resto es fricción (clics, capacitación, cierre coordinado).

**Siguiente paso:** Esperar instrucciones; no se ha implementado código en esta auditoría.

---

*Documentos relacionados: `SYSTEM_QUICK_ACTIONS_AUDIT.md`, `frontend/QUICK_ACTIONS_PHASE_A_REPORT.md`, `frontend/QUICK_ACTIONS_PHASE_B_REPORT.md`, `backend/PHASE_18_REPORT.md`, `backend/ROOM_SERVICE_NOTIFICATIONS_REPORT.md`.*
