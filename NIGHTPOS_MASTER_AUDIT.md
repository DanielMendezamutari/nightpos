# NIGHTPOS_MASTER_AUDIT.md

**Proyecto:** NightPOS SaaS (migración desde `restaurant_bolivia-1`)  
**Tipo:** Auditoría maestra final  
**Fecha:** 2026-06-02  
**Implementación Fase Final 1:** ver `NIGHTPOS_FINAL_PHASE1_REPORT.md` (jun 2026)  
**Alcance:** Documentación obligatoria del repositorio + verificación puntual del código (`backend/`, `frontend/`)

---

## 0. Respuesta directa

### ¿Qué le falta a NightPOS para ser utilizado en un boliche real?

**Para una noche operativa de prueba o piloto controlado (datos precargados, personal capacitado, 1 caja, barra sin impresión obligatoria):** el núcleo **ya puede funcionar** — login, comandas, bebidas SOLO/CON_ACOMPANANTE, cobro, caja, piezas/manillas/shows, liquidaciones, turno, SaaS multi-tenant.

**Para producción comercial sostenida en un boliche real:** faltan principalmente:

1. **Impresión / tickets** (barra y caja) si el local depende de papel.
2. **Reportes y cierre documentado** (PDF/Excel, arqueo exportable) para dueño y contabilidad.
3. **Auditoría / trazabilidad** de movimientos críticos (más allá de historial de estado de comanda).
4. **Infraestructura de producción** (backups, monitoreo, despliegue, HTTPS, rate limit, política de contraseñas/PIN).
5. **Inventario / kardex** si el negocio descuenta stock al vender (no existe en API).
6. **Pulido operativo** (PWA garzón, push limpieza, caja compartida o handoff explícito, onboarding tenant sin datos vacíos).
7. **Deuda del sistema heredado no migrado** (delivery, compras, proveedores, créditos, cotizaciones, ~70% del legacy según `CURRENT_SYSTEM_AUDIT.md`).

**Veredicto global:** **MVP operativo avanzado (~65–70% del camino hacia producción boliche)**. No es un “sistema terminado”; es un **POS nocturno funcional** con huecos típicos de salida a producción.

---

## 1. Estado actual del repositorio

### 1.1 Documentación vs realidad

| Documento | Estado del doc | Realidad en código (jun 2026) |
|-----------|----------------|-------------------------------|
| `CURRENT_SYSTEM_AUDIT.md` | Describe backend “scaffold mínimo” | **Desactualizado** — Laravel hexagonal con ~70 rutas API, 19 contextos Application, 330+ tests Feature |
| `ROADMAP.md` | Fases 1–15 “pendientes” | **Desactualizado** — implementadas fases 4–18 + C1–C4 + Quick Actions A/B |
| `ARCHITECTURE_REPORT.md` | Fase 3 “sin api.php” | **Desactualizado** — arquitectura implementada en `app/Domain`, `Application`, `Infrastructure` |
| `MIGRATION_PLAN.md` | Plan incremental | **Válido como norte**; muchos módulos del plan legacy siguen sin migrar |
| Reportes `PHASE_*` / `PHASE_C*` / `PHASE_R*` | Coherentes con entregas | **Fuente principal** de verdad por módulo |
| `NIGHTPOS_OPERATION_AUDIT.md` | Simulación de noche | **Vigente** en riesgos operativos (C-01 mitigado en C1; resto parcialmente) |

### 1.2 Métricas de implementación (snapshot)

| Dimensión | Indicador |
|-----------|-----------|
| Backend tests | ~198 casos Pest (194+ passing en última corrida; revisar 4 fallos antes de release) |
| API | Prefijo `/api/v1`, JWT, middleware tenant/branch/permisos |
| Frontend | Vue 3 + Materialize; módulos `nightpos/*` + modo garzón móvil |
| Seeder demo | `NightPosSeeder`: tenant, roles, 13 bebidas, ambientes, comandas W-DEMO-*, turno abierto |
| Legacy | `restaurant_bolivia-1/` intacto; no es runtime del SaaS nuevo |

---

## 2. Clasificación global de módulos

### 2.1 COMPLETADO (listo para operación demo / piloto con capacitación)

Se considera **terminado** cuando: API + reglas de negocio + UI mínima + tests Feature dedicados o cubiertos en flujos Phase C, y documentado en reporte de fase.

| Módulo | Evidencia | Por qué “terminado” |
|--------|-----------|---------------------|
| **SaaS multi-tenant** | Fase 4–5, admin platform | Tenants, branches, JWT, aislamiento tenant en tests |
| **Auth PIN + password** | Fase 4–5, `AuthApiTest` | Login por casa/sucursal, PIN único, superadmin sin tenant |
| **Roles y permisos** | Fase 4–5, middleware | RBAC por slug; garzón sin cobro por diseño |
| **Usuarios / personal admin** | Fase 10, 12 | CRUD admin users, reset PIN/password, branches |
| **Productos y categorías** | Fase 6, 12 | CRUD, precios SOLO / CON_ACOMPANANTE, `girl_amount` / `house_amount` |
| **Quick product / price / category** | C1, Quick Actions B | Alta en pico sin salir del flujo |
| **Comandas (orders)** | Fase 7, C1 | Crear, ítems, enviar barra, cancelar, historial estado |
| **Cobro / ventas** | Fase 9 | Charge, pagos mixtos, snapshot venta, movimiento caja |
| **Caja** | Fase 8 | Apertura/cierre por usuario, movimientos, arqueo |
| **Turnos oficiales** | Fase 13, Shift reporting | OPEN/CLOSE, auto-turno, DAY/NIGHT, FK en órdenes/ventas |
| **Consola de turno** | C2 | `shift-console/current` agregado operativo |
| **Liquidaciones** | Fase 14, 16 | Garzones, chicas, manillas, piezas FINISHED, shows; anti-duplicado |
| **Servicios: manillas, piezas, shows** | Fase 15, 17 | CRUD operativo + quick girl/room/show type |
| **Habitaciones (rooms)** | Fase 18 | Estados, mark-clean, bloqueo OCCUPIED/CLEANING |
| **Limpieza / control piezas** | Fase 17, notificaciones | `room-control`, polling, sonido en pestaña |
| **Notificaciones in-app** | Fase 17 | API list/read; no push nativo |
| **Datos maestros C3** | C3 | Motivos caja, métodos pago, ambientes, tipos habitación catálogo, checklist |
| **Modo garzón móvil** | C4 + UX refinement | Dashboard, comandas propias, `/waiter/*`, layout blank |
| **Quick Actions A/B** | Reports + operation audit | Chica, caja desde cobro, habitación, garzón, wizard SaaS |
| **Platform setup** | Quick Actions B | Empresa + sucursal + admin transaccional |
| **Seguridad multi-tenant** | Tests dedicados | No cross-tenant en órdenes/shifts |

### 2.2 PARCIALMENTE TERMINADO

| Módulo | Qué funciona | Qué falta |
|--------|--------------|-----------|
| **Modo garzón** | Flujo móvil completo en happy path; seed demo; KPI | PWA instalable, push, categorías en selector, turno en header, offline, E2E |
| **Turnos** | Auto-create, cierre con validación cajas | UX “quién tiene caja abierta”; reporte PDF cierre; turno compartido visible para todos los roles |
| **Consola operativa** | Resumen turno vía API | Una sola pantalla “noche en curso” unificada (hoy disperso entre módulos) |
| **Caja** | Cobro, mixto, quick open | Caja por **usuario** (no por caja física compartida); motivos en UI; export arqueo |
| **Comandas admin** | Desktop completo | Favoritos garzón admin; impresión ticket; integración cocina si aplica |
| **Configuración** | C3 catálogos API + pantallas settings | Onboarding “primera noche” guiado post-wizard; tipos habitación aún enum en piezas |
| **Limpieza** | Control + mark-clean | Push/WhatsApp; tablet dedicada; ack server-side de alertas |
| **Reportes** | `shifts/{id}/summary`, listados ventas | PDF/Excel, dashboard gerencial histórico, comparativos |
| **Impresión** | Contratos Domain (`Printing/*`) | Sin API, sin agente, sin cola print jobs |
| **Inventario** | Flag `track_inventory` en producto | Sin kardex, sin descuento stock al cobrar |
| **Frontend shell** | R1–R4 mejoras navbar/dashboard | Algunos widgets demo; módulos `src/modules/` no aplicados; 403 interceptor mejorado pero deuda UX |
| **Superadmin SaaS** | CRUD tenants/branches/users, setup | Facturación suscripción, planes límites, métricas plataforma, self-service cliente |
| **Chicas / comisiones** | Liquidación + flags | Portal chica, firma conformidad, export pagos |
| **Auditoría** | `order_status_history` | Sin bitácora global admin (logs.php legacy) |

### 2.3 NO IMPLEMENTADO (respecto al legacy y al ROADMAP original)

| Área | Referencia |
|------|------------|
| Inventario / kardex / stock mínimo | ROADMAP fase 9, `DOMAIN_DESIGN` Inventory |
| Compras / proveedores / cuentas por pagar | Legacy + ROADMAP 10 |
| Traspasos entre sucursales | ROADMAP 11 |
| Delivery | Legacy (prioridad baja boliche) |
| Clientes / créditos / cotizaciones | Legacy |
| Combos / recetas / ingredientes | Legacy restaurante |
| Comandas cocina / repostería separadas | Legacy flags sucursal |
| Notas de crédito formales | Legacy |
| Reportes gerenciales completos | ROADMAP 12 |
| Suscripción SaaS comercial (cobro, límites) | ROADMAP 14 |
| Auditoría sistema global | ROADMAP 15 |
| Backup/restore UI | Legacy `backup.php` |
| Integración impresoras / Print Agent | ROADMAP 7.5, ARCHITECTURE |
| PWA / service worker | Pendiente C4, operation audit |
| Export Excel/PDF | Mencionado en fases, sin librerías ni rutas |
| Bitácora de usuario (quién cambió precio, anuló venta) | No |
| WhatsApp real | Stub `WhatsAppNotificationChannel` |
| Migración datos legacy → NightPOS | No iniciada (solo greenfield) |

---

## 3. Auditoría por área operativa

### 3.1 Operación — ¿Puede operar una noche completa?

| Veredicto | **Sí, con condiciones** |
|-----------|-------------------------|
| **Justificación** | Flujo documentado en `NIGHTPOS_OPERATION_AUDIT.md`: apertura → comandas → servicios → cobro → liquidación → cierre caja/turno. C1 corrige garzón obligatorio en comandas de cajera. Quick Actions reducen bloqueos. Seed demo + turno abierto facilitan prueba. |
| **Condiciones** | Catálogo precargado; habitaciones no atascadas en CLEANING; limpieza con dispositivo activo; cajera y garzones con PINs correctos; entender caja **por usuario**; cerrar todas las cajas antes de cerrar turno. |
| **Rompe la noche si** | Sin productos/precios; todas las habitaciones en limpieza; sin personal chica para CON_ACOMPANANTE; impresión obligatoria en barra (no existe). |

### 3.2 Caja — ¿Puede operar una cajera real?

| Veredicto | **Sí, con limitaciones** |
|-----------|-------------------------|
| **Funciona** | Abrir/cerrar sesión, movimientos, cobro comanda CASH/QR/CARD/MIXED, quick open desde cobro, ventas del turno. |
| **Falta** | Caja compartida entre dos cajeras; export PDF arqueo; motivos estructurados en UI (API C3 existe); dashboard “sesiones abiertas por sucursal”; reversión/anulación venta con auditoría. |

### 3.3 Garzones — ¿Puede trabajar toda la noche desde celular?

| Veredicto | **Sí, en piloto; parcial para producción exigente** |
|-----------|-------------------------|
| **Funciona** | Modo `/nightpos/waiter`, PIN, nueva comanda, bebidas, enviar barra, listados por estado, endpoints `/waiter/girls`, `/waiter/service-areas`, UX móvil refinada, `dev:mobile`. |
| **Falta** | PWA instalada; notificaciones push estado comanda; impresión comanda barra; offline; robustez red lenta; capacitación “no cobrar” (RBAC correcto). |

### 3.4 Chicas — ¿Puede administrarse correctamente?

| Veredicto | **Sí, operativamente; parcial administrativamente** |
|-----------|-------------------------|
| **Funciona** | Perfiles GIRL, quick create, asignación en ítems, liquidación CON_ACOMPANANTE / manillas / piezas / shows, flags comisión. |
| **Falta** | Portal propio; reporte individual exportable; validación fuerte pre-liquidación en UI; control de duplicados de identidad. |

### 3.5 Limpieza — ¿Puede controlar piezas?

| Veredicto | **Sí, si hay pantalla dedicada** |
|-----------|--------------------------------|
| **Funciona** | `room-control`, check/finish, mark-clean habitación, notificaciones API, sonido con pestaña abierta. |
| **Falta** | Push nativo; múltiples dispositivos sin repetir alertas; separación clara “fin servicio” (cajera) vs “habitación limpia”. |

### 3.6 Administración — ¿Puede controlar ingresos y gastos?

| Veredicto | **Parcial** |
|-----------|-------------|
| **Funciona** | Movimientos caja, resumen turno, liquidaciones, configuración maestros C3, productos/precios. |
| **Falta** | Reportes gerenciales, P&L, export contable, gráficos dashboard reales (widgets R1 parcialmente estáticos), comparación entre turnos. |

### 3.7 Superadmin SaaS — ¿Puede vender el sistema?

| Veredicto | **Parcial (técnico sí, comercial no)** |
|-----------|----------------------------------------|
| **Funciona** | Crear tenant, branch, usuarios, setup wizard, superadmin login. |
| **Falta** | Billing, planes, límites por tenant, onboarding cliente final, documentación operativa, SLA, multi-región, métricas MRR/churn. |

---

## 4. Auditoría de producción

### 4.1 CRÍTICO (bloquea o alto riesgo salir a producción)

| ID | Tema | Riesgo |
|----|------|--------|
| P1 | **Sin impresión barra/caja** si el local opera con tickets | Operación manual o caos en barra |
| P2 | **Sin backups automatizados** documentados/operativos | Pérdida total de datos |
| P3 | **Sin auditoría de acciones sensibles** (anulaciones, cambios precio, cierres) | Fraude / disputas irresolubles |
| P4 | **Despliegue producción** (HTTPS, `.env`, secrets, CORS, queue) no documentado como checklist | Exposición y caídas |
| P5 | **Tests con fallos** antes de tag release | Regresiones en cobro/turnos |
| P6 | **Comisiones incorrectas** si se saltan reglas C1 (capacitación) | Dinero mal pagado (mitigado en código, no en hábito) |
| P7 | **Habitaciones CLEANING** sin proceso limpieza | Venta de piezas detenida (`H-01`) |
| P8 | **Tenant nuevo vacío** sin wizard datos operativos | Primera noche inutilizable |

### 4.2 IMPORTANTE (puede salir con plan de mitigación)

| ID | Tema |
|----|------|
| I1 | Reportes PDF cierre caja y turno |
| I2 | PWA / URL fija garzón en móvil |
| I3 | Panel cajas abiertas + alerta cierre turno |
| I4 | Export liquidaciones (Excel) |
| I5 | Monitoreo y logs centralizados (más allá de `Log::info` puntual) |
| I6 | Rate limiting / lockout PIN |
| I7 | Navbar frontend 100% operativo (sin restos demo en rutas nightpos) |
| I8 | Inventario si el negocio exige control de botellas |

### 4.3 OPCIONAL (V2)

| ID | Tema |
|----|------|
| O1 | Delivery, compras, proveedores |
| O2 | Clientes / CRM / créditos |
| O3 | Combos y recetas |
| O4 | Push WhatsApp limpieza |
| O5 | Portal chica / garzón estadísticas |
| O6 | Tipos habitación 100% configurables |
| O7 | Migración datos legacy automatizada |
| O8 | Multi-idioma (plantilla trae EN/FR/AR demo) |

---

## 5. Especial — estado de capacidades transversales

| Capacidad | Estado | Detalle |
|-----------|--------|---------|
| **Impresión automática** | **No iniciado** (solo dominio stub) | `PrintJobRepositoryInterface`, `PrintingUseCaseInterface`, sin rutas API ni agente |
| **Reportes** | **Parcial** | Resúmenes API (`shifts/summary`, ventas list); sin PDF/Excel ni módulo Reports Application implementado |
| **Auditoría de movimientos** | **Parcial** | Historial estados comanda; sin tabla `audit_logs` ni UI logs |
| **PWA** | **No iniciado** | Mencionado en C4; sin manifest/service worker en frontend |
| **Notificaciones** | **Parcial** | API in-app + polling limpieza; sin push FCM/WebPush |
| **Exportaciones Excel/PDF** | **No iniciado** | Solo menciones en reportes de fase |
| **Respaldos** | **No iniciado** en SaaS | Legacy tenía backup.php; delegar a infra (mysqldump, S3) |
| **Logs** | **Parcial** | Laravel log estándar; sin agregación ni audit trail negocio |

---

## 6. Riesgos consolidados

| Severidad | Riesgo | Mitigación actual |
|-----------|--------|-------------------|
| Alto | Piezas bloqueadas por CLEANING | Proceso limpieza + `rooms/cleaning` |
| Alto | Local exige ticket impreso | No hay — bloqueante producción |
| Alto | Sin backup | Procedimiento manual DBA |
| Medio | Dos cajeras, dos sesiones separadas | Documentar / Fase futura caja sucursal |
| Medio | Limpieza sin pestaña = sin alerta | Tablet fija |
| Medio | Tenant nuevo sin datos | Seeder manual post-setup |
| Medio | 4 tests fallando | Corregir antes release |
| Bajo | Documentación arquitectura desactualizada | Este master audit |
| Bajo | Deuda UX plantilla demo | R1–R4 parcial |

---

## 7. Roadmap final recomendado

> **Detener nuevas fases de features** hasta cerrar Fase Final 1 (producción segura).

### FASE FINAL 1 — Producción mínima viable boliche (urgente)

| # | Entrega | Objetivo |
|---|---------|----------|
| F1.1 | Checklist despliegue (HTTPS, env, DB, CORS, queue, scheduler) | Operar en servidor real |
| F1.2 | Backups automáticos + prueba restore | No perder datos |
| F1.3 | Impresión comanda barra (MVP: agente local o print browser) | Barra operativa |
| F1.4 | PDF cierre caja + resumen turno | Dueño cierra noche con papel |
| F1.5 | Auditoría mínima (venta anulada, cierre caja, cambio precio) | Trazabilidad |
| F1.6 | Corregir tests CI + smoke manual noche (`NIGHTPOS_OPERATION_AUDIT` §8) | Calidad release |
| F1.7 | Onboarding tenant: seed/checklist “primera noche” post-wizard | Tenant nuevo usable |
| F1.8 | Documentar operación caja por usuario y cierre coordinado | Capacitación |

**Criterio de salida:** Una noche real en sucursal piloto sin desarrollo ad hoc.

### FASE FINAL 2 — Operación robusta (segundo nivel)

| # | Entrega |
|---|---------|
| F2.1 | PWA garzón (manifest, icono, pantalla completa) |
| F2.2 | Push o WhatsApp limpieza (piezas vencidas) |
| F2.3 | Export Excel liquidaciones y ventas del turno |
| F2.4 | Panel único “consola noche” (comandas pendientes cobro, cajas abiertas, piezas activas) |
| F2.5 | Caja por sucursal/registro físico o handoff entre cajeras |
| F2.6 | Ticket cobro opcional |

### FASE FINAL 3 — Administración y SaaS comercial (tercer nivel)

| # | Entrega |
|---|---------|
| F3.1 | Reportes gerenciales (por fecha, sucursal, producto, garzón) |
| F3.2 | Inventario básico (descuento al cobrar si `track_inventory`) |
| F3.3 | Planes SaaS, límites, estado suscripción en UI |
| F3.4 | Bitácora admin completa |
| F3.5 | Mejoras superadmin (métricas tenants) |

### FASE FINAL 4 — V2 / legacy opcional (futuro)

| # | Entrega |
|---|---------|
| F4.1 | Compras / proveedores |
| F4.2 | Migración selectiva datos legacy |
| F4.3 | Delivery |
| F4.4 | CRM / créditos |
| F4.5 | Combos / cocina separada |
| F4.6 | Portal chica y analytics avanzados |

---

## 8. Matriz documentación revisada

| Documento | Revisado | Conclusión breve |
|-----------|----------|------------------|
| `CURRENT_SYSTEM_AUDIT.md` | Sí | Legacy completo; **NightPOS nuevo ya no es scaffold** |
| `DOMAIN_DESIGN.md` | Sí | Diseño válido; Inventory/Printing/Reports sin implementar |
| `ARCHITECTURE_REPORT.md` | Sí | Base hexagonal cumplida; ampliada con más contextos |
| `MIGRATION_PLAN.md` | Sí | ~40% módulos críticos boliche migrados |
| `ROADMAP.md` | Sí | Obsoleto en estados; orden de dependencias sigue válido |
| `PHASE_4`–`10` | Sí | Fundación SaaS a ventas — **hecho** |
| `PHASE_13`–`18` | Sí | Turnos, liquidaciones, servicios, habitaciones — **hecho** |
| `PHASE_C1`–`C3` | Sí | Correcciones P1, consola, maestros — **hecho** |
| `PHASE_C4` (be/fe) | Sí | Garzón móvil — **hecho**, pulido UX pendiente menor |
| `FRONTEND_GUIDELINES.md` | Sí | Reglas válidas; cumplimiento ~70% |
| `FRONTEND_AUDIT_REPORT.md` | Sí | Deuda UX; R1–R4 mitigó parte |
| `PHASE_R1`–`R4` | Sí | Reconstrucción shell — **hecho** |
| `WAITER_MOBILE_*` | Sí | Garzón usable móvil |
| `SYSTEM_QUICK_ACTIONS_AUDIT.md` | Sí | Mayoría cubierta Fase A/B |
| `NIGHTPOS_OPERATION_AUDIT.md` | Sí | **Biblia operativa** para prueba noche |

*Nota: `PHASE_11` no existe en el repositorio (salto 10 → 12).*

---

## 9. Recomendación para salir a producción

1. **No declarar “v1.0 producción”** sin Fase Final 1 completa.
2. **Piloto 2–4 semanas** en un boliche con: seed propio, impresión definida (aunque sea browser print), backup diario, roles reales.
3. **Congelar features** (no Fase 19+) hasta estabilizar tests y despliegue.
4. **Capacitación obligatoria:** garzón abre sus comandas; limpieza libera habitaciones; cajera cierra caja antes del turno.
5. **Actualizar** `CURRENT_SYSTEM_AUDIT.md` y `ROADMAP.md` en una tarea documental aparte (fuera de este alcance).

**Go / No-Go producción comercial:**

| Escenario | Go |
|-----------|-----|
| Piloto interno mismo dueño, 1 sucursal, sin impresión obligatoria | **Go** |
| Boliche exige ticket barra + contador | **No-Go** hasta F1.3 |
| Multi-sucursal SaaS vendido a terceros | **No-Go** hasta F1 + F3.3 |

---

## 10. Recomendación para V2

Priorizar después de estabilizar producción:

1. Reportes gerenciales y export contable.
2. Inventario botellas.
3. PWA + push operativos.
4. Billing SaaS.
5. Solo entonces: módulos legacy de restaurante (compras, delivery, combos) si el negocio lo exige.

---

## 11. Cierre

NightPOS **ya no es un proyecto en papel**: es un **SaaS operativo nocturno** con comandas, precios boliche, caja, ventas, servicios, habitaciones, liquidaciones y modo garzón móvil. La brecha hacia un **boliche real 24/7** no es “terminar el MVP visual”, sino **producción (impresión, reportes, auditoría, backups, despliegue)** y **hábitos operativos** (limpieza, caja, comisiones).

**Siguiente acción sugerida:** Ejecutar checklist Fase Final 1 en orden; no abrir nuevas fases de producto hasta cerrar F1.6 (tests + noche de prueba documentada).

---

*Documento generado por auditoría maestra. No incluye cambios de código. Referencia operativa: `NIGHTPOS_OPERATION_AUDIT.md`, técnica: reportes `PHASE_*` / `PHASE_C*` en `backend/` y `frontend/`.*
