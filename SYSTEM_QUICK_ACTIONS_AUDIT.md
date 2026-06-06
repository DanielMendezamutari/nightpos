# Auditoría funcional — Altas rápidas y flujos operativos

**Proyecto:** NightPOS SaaS  
**Fecha:** 2026-06-02  
**Alcance:** Revisión de bloqueos operativos cuando falta un dato relacionado en pantalla (sin implementar cambios).

---

## Resumen ejecutivo

| Estado | Hallazgo |
|--------|----------|
| **Implementado** | Alta rápida de chica en **Registrar pieza** (`POST /staff/quick-girls`, `QuickGirlCreateDialog.vue`). |
| **Crítico (Alta)** | Mismo patrón falta en **comandas CON_ACOMPANANTE**, **manillas**, **shows**, **cobro sin caja**, y **registrar pieza sin habitación**. |
| **Medio** | Catálogo (categoría/precio desde producto), habitación rápida, abrir caja inline. |
| **Bajo** | Cliente, mesa/ambiente catalogado, tipos de show configurables, wizard SaaS. |

La operación diaria depende de **selectores sin CTA de alta rápida** y de **salir a otro módulo** (Personal, Catálogo, Caja, Habitaciones) o de **IDs manuales** (garzón sin `admin.users.list`).

---

## Tabla de auditoría

| Módulo | Pantalla | Dato requerido | Qué pasa si no existe | ¿Necesita alta rápida? | Rol autorizado (sugerido) | Prioridad | Endpoint requerido | Componente frontend sugerido | Riesgo si no se implementa |
|--------|----------|----------------|------------------------|-------------------------|---------------------------|-----------|-------------------|------------------------------|----------------------------|
| Comandas | Nueva comanda | Mesa/ambiente (`table_label`) | Validación UI; no hay catálogo de mesas | Opcional (texto libre hoy) | Cajera, admin | Baja | — o `POST /tables` futuro | `QuickTableLabelDialog` | Baja fricción; no bloquea si se escribe texto |
| Comandas | Nueva comanda | Garzón | Se asigna usuario logueado automáticamente | No | — | — | — | — | Sin bloqueo |
| Comandas | Agregar producto | Producto activo con precio | Lista vacía o sin precio → no se puede agregar; error backend si precio inactivo | **Sí** (cajera en pico) | Admin (`products.create`); cajera solo si se delega permiso | Media | Reutilizar `POST /products` + precio o `POST /products/quick` | `QuickProductCreateDialog` | Bloqueo si catálogo vacío o producto nuevo del día |
| Comandas | Agregar producto | Precio SOLO/CON_ACOMPANANTE | Preview vacío; backend rechaza sin precio activo | **Sí** (desde detalle producto o modal) | Admin, cajera con `products.create` | Media | `POST /products/{id}/prices` | `QuickProductPriceDialog` | No se vende ítem hasta ir a Precios |
| Comandas | Enviar a barra (CON_ACOMPANANTE) | Chica por ítem | Modal `AssignGirlModal`; sin lista → **ID numérico manual**; sin chica → error al enviar/cobrar | **Sí** | Cajera, admin (`staff.quick_create_girl`) | **Alta** | Existe: `POST /staff/quick-girls` | Reutilizar `QuickGirlCreateDialog` | Bloqueo operativo; garzón sin permiso de lista |
| Comandas | Cobrar | Caja abierta (sesión usuario) | Botón visible; confirm deshabilitado + hint; backend `cashSessionRequired` | **Sí** | Cajera, admin (`cash.access`) | **Alta** | Existe: `POST /cash/session/open` | `QuickOpenCashDialog` en `ChargeOrderModal` | Cobro detenido; flujo más usado del local |
| Comandas | Cobrar | Chicas en ítems CON_ACOMPANANTE | Backend rechaza cobro | **Sí** (mismo que enviar) | Cajera, admin | **Alta** | `POST /staff/quick-girls` | `QuickGirlCreateDialog` en flujo cobro | Venta no se cierra |
| Comandas | Cobrar | Turno oficial | Auto `EnsureOperationalShift` en backend | No (auto) | — | — | — | — | Sin bloqueo |
| Comandas | Cobrar | Método de pago | Enum fijo CASH/QR/CARD/MIXED | No | — | — | — | — | Controlado |
| Productos | Crear producto | Categoría | `VSelect` vacío si falla fetch; categoría opcional (`clearable`) | **Sí** | Admin (`products.create`) | Media | `POST /product-categories` | `QuickCategoryCreateDialog` | Productos sin categoría OK; reporting menos claro |
| Productos | Crear producto | Tipo/unidad | Valores por defecto en formulario | No | — | — | — | — | Sin bloqueo |
| Productos | Detalle / precios | Precio activo | Alert "Sin precios registrados" | **Sí** (en misma pantalla) | Admin | Media | `POST /products/{id}/prices` | Botón + dialog en `[id]/prices` | Producto no vendible en comanda |
| Productos | Listado | Productos en catálogo | Tabla vacía sin CTA | Mejora UX | Admin | Baja | — | Empty state con link crear | Onboarding lento |
| Categorías | Crear categoría | — | Formulario independiente | No obligatorio | Admin | Baja | Existe | — | — |
| Servicios | Registrar manilla | Chica | `VSelect` vacío → no puede guardar; **sin QuickGirl** | **Sí** | Cajera, admin | **Alta** | Existe: `POST /staff/quick-girls` | `QuickGirlCreateDialog` | Mismo bloqueo que piezas antes de Fase 18 |
| Servicios | Registrar manilla | Garzón (opcional) | Opcional; lista desde admin users | Opcional | Admin (`admin.users.create`) | Baja | `POST /admin/users` o quick waiter | `QuickWaiterCreateDialog` | No bloquea registro |
| Servicios | Registrar pieza | Chica | **Implementado** — autocomplete + nueva chica | Hecho | Cajera, admin | — | Existe | `QuickGirlCreateDialog` | — |
| Servicios | Registrar pieza | Habitación AVAILABLE | Alert si no hay; no puede registrar | **Sí** | Admin (`rooms.create`); cajera si se delega | **Alta** | `POST /rooms` (existe) | `QuickRoomCreateDialog` | Pieza detenida si no hay habitación libre |
| Servicios | Registrar pieza | Precio/duración | Se precargan de habitación; editables | Aviso si 0 | — | Media | — | Validación UI | Error de negocio |
| Servicios | Registrar show | Chica | `VSelect` sin alta rápida | **Sí** | Cajera, admin | **Alta** | Existe: `POST /staff/quick-girls` | `QuickGirlCreateDialog` | Bloqueo igual que manillas |
| Servicios | Registrar show | Tipo show | Enum fijo PRIVATE/STAGE/SPECIAL en UI | Opcional | Admin | Baja | Catálogo futuro `show_types` | `QuickShowTypeDialog` | No bloquea hoy |
| Servicios | Control piezas | Habitación en limpieza | Vista por API control; sin crear desde aquí | No | Limpieza | — | — | — | Flujo separado en Habitaciones |
| Habitaciones | Registrar pieza (origen) | Habitación disponible | Redirige mentalmente a módulo Habitaciones | **Sí** | Admin; cajera lectura | **Alta** | `POST /rooms` | `QuickRoomCreateDialog` | Ver fila pieza arriba |
| Habitaciones | Listado / dashboard | Habitaciones | Tablas vacías sin CTA fuerte | Mejora | Admin | Media | `POST /rooms` | Empty state + crear | Setup inicial lento |
| Habitaciones | Limpieza | — | Lista CLEANING; acción marcar limpia | No | Limpieza | — | Existe `mark-clean` | — | — |
| Habitaciones | Mantenimiento | — | Solo MAINTENANCE; liberar a AVAILABLE | No | Admin | — | Existe | — | Estados claros en chips |
| Habitaciones | Pieza en habitación ocupada | AVAILABLE | Backend 422 `roomNotAvailable` | No (correcto) | — | — | — | Mostrar `/rooms/available` link | Doble asignación evitada; UX: sugerir otra habitación |
| Personal | Usuarios / crear | Rol, sucursales, PIN | Formulario completo admin | Parcial por módulo | Admin (`admin.users.create`) | Media | Varios | Formularios completos | Fuera de turno operativo |
| Personal | Alta rápida chica | Nombre, PIN opcional | Solo piezas hoy | **Sí** (extender) | Cajera, admin | **Alta** | Existe | `QuickGirlCreateDialog` | Ver comandas/manillas/shows |
| Personal | Garzón rápido | Nombre, % comisión, PIN | No existe; crear usuario completo | **Sí** (comandas pico) | Admin | Media | `POST /staff/quick-waiters` propuesto | `QuickWaiterCreateDialog` | Admin debe precargar garzones |
| Personal | Cajera / limpieza rápida | — | Solo admin vía usuarios | Baja | Admin | Baja | `POST /admin/users` | — | Poco frecuente en turno |
| Caja | Abrir sesión | Monto apertura | Diálogo en página Caja | **Sí** (desde cobro) | Cajera, admin | **Alta** | `POST /cash/session/open` | `QuickOpenCashDialog` | Cobro bloqueado |
| Caja | Movimientos | Sesión abierta | Alert en página | No (ir a abrir) | Cajera | Media | — | Link desde cobro | Misma raíz que caja |
| Ventas | Listado | Ventas en sesión | Empty: "Cobre una comanda" | No | Cajera | — | — | — | Coherente |
| Ventas | Cobro (comanda) | Permiso `sales.charge` | Garzón no cobra | No (RBAC) | Cajera, admin | — | — | — | Por diseño |
| Liquidaciones | Generar turno | Turno + datos shift | Auto turno; genera desde ventas/servicios | No | Cajera, admin | — | Existe | — | — |
| Liquidaciones | Garzones | % en perfil WAITER | Sin %: admin no puede crear WAITER; ventas sin comisión | Alerta admin | Admin | Media | Validación en UI generate | Banner en `waiters.vue` | Comisión 0; no bloquea generate |
| Liquidaciones | Chicas | `can_receive_girl_commissions` | Chica mal configurada: ítems pueden no generarse | Alerta | Admin | Media | GET users + flags | Banner en `girls.vue` | Pago incorrecto |
| Liquidaciones | Piezas activas | Solo FINISHED en liquidación | Piezas ACTIVE no entran; sin aviso explícito en UI generate | **Sí** (aviso) | Cajera, admin | Media | — | `VAlert` en settlements index | Cajera cree que falta dinero |
| Liquidaciones | Marcar pagado | Settlement PENDING | Flujo normal | No | Cajera, admin | — | — | — | — |
| Plataforma SaaS | Crear empresa | Empresa | Formulario; redirect a detalle | Wizard opcional | Super admin | Media | `POST /admin/tenants` | `TenantOnboardingWizard` | Sin sucursal operativa después |
| Plataforma SaaS | Crear empresa | Primera sucursal | **No** se crea en mismo paso | **Sí** | Super admin | Media | `POST /admin/branches` encadenado | Wizard empresa+sucursal+admin | Empresa inutilizable hasta otra pantalla |
| Plataforma SaaS | Crear sucursal | Tenant context | Pantalla separada | **Sí** (en wizard) | Super admin | Media | Existe branches API | Mismo wizard | Fricción onboarding SaaS |
| Plataforma SaaS | Contexto operativo | Empresa + sucursal | Selector navbar; sin sucursal → operación limitada | Aviso | Super admin | Media | — | Guía en selector | Confusión superadmin |
| Turnos | Operación sin turno manual | Turno oficial | Auto-create en primera operación | No | — | — | `EnsureOperationalShift` | Chip info en servicios | Documentado |
| Turnos | Cerrar turno | Cajas cerradas | Error si hay sesión OPEN | No | Admin | — | — | Link a Caja | Correcto |
| Notificaciones | Limpieza piezas | — | Polling room-control | No | Limpieza | — | — | — | — |

---

## Hallazgos por módulo

### 1. Comandas

| Pregunta auditoría | Respuesta |
|--------------------|-----------|
| ¿Producto rápido si no existe? | **No.** Lista en modal; debe existir en catálogo con precio activo. |
| ¿Chica rápida? | **No en UI.** Backend listo (`staff.quick_create_girl`). Garzón usa ID manual. |
| ¿Mesa/ambiente rápido? | Texto libre; no hay entidad mesa. |
| ¿Cliente rápido? | No hay entidad cliente; `table_label` mezcla conceptos. |

**Impacto:** CON_ACOMPANANTE y cobro son los cuellos de botella diarios junto con caja cerrada.

### 2. Productos

| Pregunta | Respuesta |
|----------|-----------|
| ¿Categoría rápida desde producto? | **No.** Select vacío silencioso si falla API. |
| ¿Precio advertido? | **Sí** en detalle; **no** al agregar en comanda (solo preview fallido). |
| ¿Crear precio rápido? | Solo en flujo completo de precios. |

### 3. Servicios

| Pregunta | Respuesta |
|----------|-----------|
| ¿Chica rápida? | **Solo piezas.** Manillas y shows pendientes. |
| ¿Habitación rápida desde pieza? | **No.** Alert + ir a Habitaciones. |
| ¿Tipo show rápido? | Enum fijo; no bloquea. |
| ¿Duración sugerida? | Precarga desde habitación; sin habitación manual sin defaults claros. |
| ¿Habitación ocupada? | API 422; sin sugerencia de alternativas en UI. |

### 4. Habitaciones

| Pregunta | Respuesta |
|----------|-----------|
| ¿Crear rápida desde pieza? | **No.** |
| ¿Estados claros? | **Sí** (chips, pestañas, limpieza/mantenimiento). |
| ¿Cambio rápido de estado? | Acciones en pantallas dedicadas; no inline en pieza. |

### 5. Usuarios / Personal

| Alta rápida | Estado | Roles |
|-------------|--------|-------|
| Chica | Parcial (pieza + API) | Cajera, admin |
| Garzón | No | Admin completo |
| Cajera / limpieza | No | Admin |
| Campos mínimos chica | Nombre; PIN y notas opcionales | — |

### 6. Caja / Ventas

| Pregunta | Respuesta |
|----------|-----------|
| ¿Abrir caja sin salir al cobrar? | **No.** Hint en modal; página Caja separada. |
| ¿Turno automático? | **Sí** en backend. |
| ¿Pago mixto / cambio? | **Sí** en `ChargeOrderModal` (validación suma y efectivo recibido). |

### 7. Liquidaciones

| Pregunta | Respuesta |
|----------|-----------|
| ¿Chica mal configurada? | No hay banner; depende de flags en usuario. |
| ¿Garzón sin %? | Validación al crear WAITER; ventas antiguas sin snapshot → sin línea. |
| ¿Piezas activas? | No se liquidan; **sin aviso** al generar. |
| ¿Servicios sin turno? | Turno se asegura al generar. |

### 8. Plataforma SaaS

| Pregunta | Respuesta |
|----------|-----------|
| ¿Sucursal al crear empresa? | **No** en un paso. |
| ¿Wizard empresa+sucursal+admin? | **No** (recomendado Fase B). |
| ¿Superadmin sin sucursal? | Context selector; requiere capacitación. |

---

## Inventario de componentes y endpoints existentes

| Recurso | Estado |
|---------|--------|
| `QuickGirlCreateDialog.vue` | Implementado |
| `POST /api/v1/staff/quick-girls` | Implementado |
| `GET /api/v1/staff/girls` | Implementado |
| Permiso `staff.quick_create_girl` | Cajera + admin |
| `POST /api/v1/admin/users` | Completo; requiere `admin.users.create` |
| `POST /api/v1/rooms` | Completo; requiere `rooms.create` |
| `POST /api/v1/products` | Completo; `products.create` |
| `POST /api/v1/cash/session/open` | Existe; `cash.access` |

**No existen** endpoints `quick-*` para: habitación, categoría, producto, garzón, caja inline, empresa+sucursal.

---

## Plan propuesto — Quick Actions

### Fase A — Críticas (bloquean operación diaria)

| # | Acción | Pantallas | Endpoint | Componente |
|---|--------|-----------|----------|------------|
| A1 | Nueva chica desde comanda CON_ACOMPANANTE | `orders/[id].vue`, `AssignGirlModal` | `POST /staff/quick-girls` (reutilizar) | `QuickGirlCreateDialog` |
| A2 | Nueva chica desde manillas | `bracelets/create.vue` | Idem | Idem |
| A3 | Nueva chica desde shows | `shows/create.vue` | Idem | Idem |
| A4 | Nueva habitación desde registrar pieza | `room-services/create.vue` | `POST /rooms` o `POST /rooms/quick` | `QuickRoomCreateDialog` |
| A5 | Nueva categoría desde crear producto | `products/create.vue` | `POST /product-categories` | `QuickCategoryCreateDialog` |
| A6 | Abrir caja desde cobro | `ChargeOrderModal.vue` | `POST /cash/session/open` | `QuickOpenCashDialog` |

**Extras recomendados en A:** aviso en liquidaciones si hay piezas ACTIVE; empty state con CTA en manillas/shows cuando `girls.length === 0`.

### Fase B — Administrativas / onboarding

| # | Acción | Notas |
|---|--------|-------|
| B1 | Wizard crear empresa + primera sucursal + admin operativo | Super admin; reduce SaaS incompleto |
| B2 | Garzón rápido (nombre, %, PIN) | `POST /staff/quick-waiters`; solo admin o política estricta |
| B3 | Tipo de show configurable | Tabla `show_types` + quick add; hoy enum suficiente para demo |
| B4 | Precio rápido desde detalle producto / modal comanda | `POST /products/{id}/prices` |
| B5 | Banner liquidaciones: garzones sin comisión / chicas sin flag | Solo lectura + link a Personal |

### Fase C — Mejoras futuras

| # | Acción |
|---|--------|
| C1 | Cliente rápido (si se modela `customers`) |
| C2 | Mesa/ambiente como catálogo (mesas, salones) |
| C3 | Métodos de pago configurables por tenant |
| C4 | Producto rápido minimal desde comanda (nombre + precio + modo) |
| C5 | Quick waiter desde nueva comanda si se permite elegir garzón ajeno |
| C6 | Sugerencia de habitaciones alternativas cuando `roomNotAvailable` |

---

## Matriz rol × alta rápida (objetivo)

| Alta rápida | Admin | Cajera | Garzón | Limpieza |
|-------------|-------|--------|--------|----------|
| Chica | Sí | Sí | No | No |
| Habitación | Sí | Opcional* | No | No |
| Categoría | Sí | No** | No | No |
| Abrir caja | Sí | Sí | No | No |
| Garzón | Sí | No | No | No |
| Producto/precio | Sí | No** | No | No |
| Empresa/sucursal wizard | Super admin | — | — | — |

\*Recomendación: cajera solo si negocio permite ampliar habitaciones en turno; si no, solo admin.  
\*\*O ampliar permisos puntuales (`products.create` delegado).

---

## Impacto en reportes / liquidaciones / caja

| Alta rápida | Impacto |
|-------------|---------|
| Chica rápida | Aparece en `GIRL_*` liquidaciones si `can_receive_girl_commissions=true` (ya aplicado). |
| Habitación rápida | Mejor trazabilidad `room_id` en piezas y futuros reportes por habitación. |
| Categoría/producto rápido | Comandas y ventas inmediatas; comisiones según precio snapshot al cobrar. |
| Abrir caja rápida | Habilita cobro y movimientos; ventas en sesión actual. |
| Garzón rápido | Comisiones `WAITER_COMMISSION` si % configurado. |
| Wizard SaaS | No afecta caja del local; habilita contexto operativo. |

---

## Riesgos globales si no se ejecuta Fase A

1. **Cajera interrumpe servicio** para crear chica, habitación o abrir caja (3–5 minutos fuera de pantalla).
2. **Garzón** ingresa IDs incorrectos de chica en CON_ACOMPANANTE.
3. **Piezas** no se registran cuando no hay habitación AVAILABLE preconfigurada.
4. **Cobro** visible pero no ejecutable sin navegar a Caja.
5. **Percepción de bug** en liquidaciones por piezas aún activas.

---

## Próximo paso recomendado

Implementar **Quick Actions Fase A** en el orden: **A6 (caja)** → **A1 (comanda chica)** → **A2/A3 (manilla/show)** → **A4 (habitación)** → **A5 (categoría)**.

Documentos relacionados: `backend/QUICK_GIRL_CREATE_REPORT.md`, `frontend/QUICK_GIRL_CREATE_REPORT.md`, `backend/PHASE_18_REPORT.md`.

---

*Auditoría basada en código y reportes de fases 13–18. Sin cambios en backend ni frontend.*
