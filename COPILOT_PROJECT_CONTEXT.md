# COPILOT_PROJECT_CONTEXT.md

**Fecha de consolidacion:** 2026-06-30
**Fuente de contexto:** auditoria de documentacion Markdown del repositorio, con lectura prioritaria de documentos maestros, reportes backend/frontend/agent/desktop y auditorias de QA real.
**Objetivo:** servir como contexto tecnico operativo de referencia para GitHub Copilot antes de modificar NightPOS.

---

## 1. Resumen ejecutivo del sistema

NightPOS es un POS SaaS multi-tenant para boliches y negocios nocturnos operado por Ribersoft. El sistema ya tiene nucleo funcional muy amplio: autenticacion multi-tenant, caja, turnos, comandas, venta directa, productos con precios por modalidad, servicios de boliche, habitaciones, limpieza movil, liquidaciones, reportes basicos, modo garzon, SSE operativo y una primera capa de observabilidad SaaS.

La conclusion consolidada de la documentacion es estable:

- NightPOS V1 esta muy avanzado como sistema operativo de piloto real.
- No esta todavia completamente endurecido para produccion comercial SaaS sin supervision.
- El foco ya no es construir el core, sino hardening operativo, consistencia de cierres, monitoreo, hosting, impresion y disciplina de despliegue.

Estado sintetico:

| Dimension | Estado consolidado |
|-----------|--------------------|
| Operacion V1 en local real controlado | Alta viabilidad |
| Hardening preproduccion | Parcial |
| SaaS core multi-tenant | Implementado |
| SaaS comercial completo (billing/enforcement) | Incompleto |
| Impresion automatica plenamente cerrada | Parcial |
| Riesgo operativo principal | Cierre de turno, liquidaciones, hosting e impresion |

---

## 2. Arquitectura backend

El backend sigue una arquitectura hexagonal con separacion estricta por capas:

- `Domain/`: reglas de negocio puras, entidades, value objects, repositorios por contrato.
- `Application/`: casos de uso, DTOs, orquestacion.
- `Infrastructure/`: Eloquent, Laravel, JWT, presentacion tecnica, persistencia.
- `Shared/`: kernel comun multi-tenant.

Contextos principales documentados:

- Tenant
- Branch
- Auth
- User
- Shift
- Cash
- Order
- Sale
- Product
- Printing
- StaffSettlement
- Reports
- Inventory como contexto previsto, no maduro funcionalmente en V1

Decisiones backend criticas:

- Multi-tenant en base unica con `tenant_id` y `branch_id`.
- El dominio no debe depender de Laravel.
- Toda venta relevante debe pasar por caja abierta y transaccion.
- La impresion no se ejecuta en el backend directamente; se encola en `print_jobs`.
- Las secuencias documentales deben pasar por `DocumentSequenceService`.

Base API:

- Canonica de codigo: `/api/v1`
- Base temporal operativa actual de hosting: `/backend/public/api/v1`

---

## 3. Arquitectura frontend

El frontend esta construido con Vue 3, Vite, Pinia y layout administrativo/material. La app ya esta separada por dominios operativos y roles.

Pilares del frontend:

- Autenticacion JWT con contexto tenant/sucursal.
- Estado central en Pinia: auth, context, notify, operational.
- Navegacion por roles y permisos.
- Flujos moviles dedicados para garzon y limpieza.
- SSE mediante composables reutilizables para refresco operativo.
- POS-CAT como selector unificado de productos.

Areas funcionales ya estructuradas:

- Caja
- Cajera
- Catalogo/productos
- Limpieza
- Finanzas/reportes/liquidaciones
- Operacion/turno
- Comandas
- Rooms
- Services
- Settings
- Waiter
- Platform/SaaS

Regla de frontend no negociable:

- El frontend envia intencion; no decide reglas de precio, caja, liquidacion ni comision.

---

## 4. Arquitectura agente de impresion

El agente de impresion es un binario Go para Windows, ejecutable como servicio nativo. Su modelo de integracion actual es desacoplado del navegador y del frontend.

Arquitectura resumida:

- Backend Laravel crea `print_jobs`.
- Agente Windows consulta por polling el backend.
- El agente reclama trabajos, imprime por spooler de Windows y reporta estado.
- La app web/PWA no habla directamente con el agente.

Restricciones actuales del agente:

- Usa `device_key` por sucursal/dispositivo.
- La URL operativa actual es legacy: `/backend/public/api/v1`.
- El polling en produccion debe mantenerse alto para no sobrecargar hosting.
- Los errores de impresion no deben revertir cobros ni pagos.

Desktop:

- El desktop V1 real es PWA instalada.
- No hay wrapper Electron/Tauri productivo obligatorio en esta fase.
- La lectura de estado local del agente desde UI no esta cerrada en V1.

---

## 5. Estado actual de NightPOS V1

Estado consolidado del proyecto:

- V1 operativo esta practicamente completo para piloto controlado.
- El mapa oficial lo ubica cerca del 99 % como V1 operativo post estabilizacion y POS-CAT.
- La auditoria de readiness contra la base real lo ubica como apto con riesgos, no como produccion endurecida.

Lectura correcta de ambos estados:

- Funcionalmente: el producto ya resuelve la operacion central del boliche.
- Operativamente: siguen existiendo riesgos reales de cierre, permisos, hosting, sesiones abiertas antiguas y deuda de liquidaciones.

Sintesis:

- V1 funcional: alto
- V1 endurecido: medio
- SaaS comercial: bajo a medio

---

## 6. Modulos completados

Los documentos convergen en que estos modulos ya existen con implementacion utilizable:

- SaaS base: tenants, branches, onboarding, contexto plataforma.
- Auth: password, PIN, seleccion de empresa/sucursal, superadmin.
- Roles y permisos RBAC.
- Productos, categorias y precios por modalidad.
- POS-CAT.
- Comandas con flujo operativo central.
- Correccion de comandas por cajera.
- Caja: apertura, cierre, movimientos, resumen, quick open.
- Venta directa.
- Pago mixto CASH/QR/CARD.
- Servicios: manillas, piezas, shows.
- Habitaciones y control de estados.
- Limpieza movil.
- Liquidaciones.
- Fiscalizacion multicaja/admin cash sessions.
- Turnos oficiales y consola de cierre.
- Reportes basicos operativos.
- SSE base + SSE operativo en pantallas clave.
- Health Center H1 minimo.

---

## 7. Modulos en riesgo

Los modulos o areas de mayor riesgo residual son:

1. Impresion y agente en hosting compartido.
2. Cierre de turno con liquidaciones PENDING.
3. Scope inconsistente de liquidaciones entre owner y cajera.
4. Turnos y cajas zombie en tenants reales o demo.
5. API/hosting con ruta temporal legacy.
6. SaaS comercial real: planes, suscripciones cobrables, enforcement.
7. Inventario/kardex, que no debe asumirse como funcional completo.
8. Modulo barra/pantalla operativa dedicado, que V1 deja fuera.
9. Observabilidad y autocorreccion completa del Health Center, aun no cerradas.

---

## 8. Bugs criticos ya resueltos

Los reportes documentan como ya resueltos o mitigados estos problemas importantes:

- Fix final de `DocumentSequenceService` para evitar 409 por correlativos y conciliacion de secuencias.
- Fix de timezone en piezas/habitaciones usando `America/La_Paz`.
- Fix de integracion de pago de liquidaciones con egreso de caja y expected cash.
- Fixes de SSE base y SSE-2 para refresco operativo.
- Fix de snackbar global y loading visible en frontend.
- Fixes de venta directa y pricing activo.
- Fixes de guardas y redirects de login/home.
- Fixes de colapso/retroceso PWA-hosting y rollback a ruta legacy estable.
- Fixes del agente para HTTP/1.1, backoff y sobrecarga de hosting.

Importante: algunos items quedaron mitigados operativamente mas que cerrados al 100 %, por ejemplo ciertos casos de habitaciones o UX de comanda/garzon.

---

## 9. Decisiones importantes tomadas

Decisiones de producto y arquitectura que ya fueron tomadas y no deben reinterpretarse sin revisar la documentacion:

- Single database multi-tenant.
- Arquitectura hexagonal y DDD-like.
- Caja por usuario como modelo V1 actual.
- No poner reglas de negocio ni precios en frontend.

---

## Actualizacion 2026-07-03: Compensacion de garzones Fase 1 y Fase 2

Se implemento el modelo de compensacion WAITER con dos modos:

- AUTO_PERCENT: toma porcentaje snapshot del item de venta.
- MANUAL: requiere monto manual asignado antes de pagar.

Puntos clave:

- Backend ahora genera liquidaciones WAITER cuando hay garzon en la venta, incluso si la comision snapshot es 0.00.
- Se agrego PATCH `/api/v1/settlements/{id}/manual-compensation` para asignar monto manual.
- `mark-paid` bloquea pago de WAITER en modo MANUAL sin `manual_amount_input`.
- DTO de settlement expone `compensation_mode`, `compensation_source`, `manual_amount_input`, `requires_manual_amount` y metadata de lock.
- Frontend garzones muestra columnas de modo y monto manual, boton `Asignar monto`, y deshabilita `Pagar` hasta completar monto manual.

Evidencia principal:

- `backend/WAITER_COMPENSATION_IMPLEMENTATION_REPORT.md`
- `frontend/WAITER_COMPENSATION_IMPLEMENTATION_REPORT.md`
- `backend/tests/Feature/Api/V1/SettlementsPhase14Test.php` (12 tests PASS)
- Venta/cobro solo con caja abierta.
- Impresion mediante cola `print_jobs`, no impresion directa desde Laravel.
- El fallo de impresion no debe tumbar la operacion de negocio.
- Modulo barra fuera de alcance operativo pleno en V1.
- PWA desactivada/rollbackeada hasta estabilizar hosting.
- Ruta API de hosting temporal en `/backend/public/api/v1`.
- Health Center H1 con diagnostico, no autocorreccion agresiva.
- Validacion real de bugs sensibles usando MySQL real importada, no seeders/factories.

---

## 10. Que NO se debe tocar sin autorizacion

No tocar sin revisar documentacion y sin autorizacion explicita:

- Reactivar PWA.
- Cambiar la API base de `/backend/public/api/v1` a `/api/v1` en hosting.
- Cambiar impresion/agente sin leer auditorias y fix reports del agente.
- Cambiar logica de liquidaciones sin revisar reportes de `document_sequences`, pagos y ajustes.
- Cambiar secuencias documentales o correlativos sin leer el fix final de 409.
- Asumir que inventario esta listo para produccion.
- Tocar `dist` o artefactos generados salvo instruccion explicita.
- Hacer commits o push.
- Validar bugs reales con seeders/factories si el problema viene de produccion.
- Romper el modelo de aislamiento por tenant/branch.
- Mover reglas de precio/comision al frontend.

---

## 11. Flujo operativo real

### Login

- Login por PIN/password segun rol.
- Resolucion de contexto tenant/sucursal.
- Superadmin opera fuera del tenant y entra por contexto plataforma.
- Garzon y limpieza tienen shells/rutas moviles dedicadas.

### Caja

- La caja se abre por usuario.
- Debe existir caja abierta para vender/cobrar/pagar liquidaciones.
- Cierre de caja usa close-check y arqueo declarado.

### Comandas

- Garzon o cajera crean comanda.
- Se agregan items via POS-CAT.
- Puede enviarse a barra como estado operativo, aunque no exista una pantalla de barra V1 completa.
- La cajera cobra la comanda y genera venta y movimientos.

### Piezas

- Servicios de habitacion/pieza ocupan room y generan flujo de limpieza posterior.
- La limpieza movil marca limpieza y libera habitacion segun flujo.
- Debe respetarse zona horaria Bolivia.

### Liquidaciones

- Se generan por turno/flujo correspondiente.
- Se pagan desde caja abierta.
- El pago crea movimiento de caja y puede generar ticket/print job.
- Hay riesgo documentado si quedan PENDING al cerrar turno.

### Cierre de caja

- Se valida por close-check.
- Debe haber monto declarado.
- Diferencias relevantes deben ser auditables.

### Cierre de turno

- Consola de turno con validaciones y warnings/blockers.
- Riesgo actual: las liquidaciones PENDING todavia aparecen como warning en varias capas, no siempre como blocker duro.

### Impresion

- La impresion real se soporta por tickets navegables/browser print y cola `print_jobs`.
- El agente local es complementario y no debe bloquear el negocio.

---

## 12. SaaS

### Superadmin

- Administra plataforma, tenants y monitoreo central.
- Opera fuera del contexto operativo del local.

### Tenants

- Empresa cliente del SaaS.
- Aislamiento obligatorio por `tenant_id`.

### Branches

- Sucursal o casa operativa.
- Aislamiento adicional por `branch_id` donde aplique.

### Planes

- Arquitectonicamente previstos.
- Comercialmente todavia incompletos como enforcement/billing productivo.

### Control Center

- Ya existe una capa de observabilidad de plataforma tipo SAAS-1.5.
- Debe verse como antecedente directo del Health Center de plataforma.

### Health Center H1

Estado importante ya implementado:

- Diagnostico sin autocorreccion.
- Score 0-100.
- Checks de APP_DEBUG, APP_ENV, DB, JWT, storage.
- Checks por sucursal sobre:
  - document_sequences
  - cajas antiguas
  - turnos antiguos
  - liquidaciones PENDING en turno cerrado
  - agente offline
  - print_jobs pendientes
- Rutas:
  - `/api/v1/admin/health-center/platform/summary`
  - `/api/v1/health-center/summary`
- UI:
  - `/nightpos/platform/health-center`
  - `/nightpos/settings/health`

---

## 13. Estado de hosting

Estado consolidado segun reportes recientes:

- PWA desactivada y rollbackeada.
- API temporal operativa: `/backend/public/api/v1`.
- Agente usando URL legacy alineada a esa ruta.
- Hubo problemas previos de `.htaccess`, rewrite y colision con fallback SPA.
- Los problemas de heartbeat/reset del agente se atribuyen sobre todo a hosting/capa HTTP, no a la logica core del backend.

Consecuencia practica:

- No migrar rutas ni reactivar PWA hasta validar end-to-end con frontend, backend, agente y health checks.

---

## 14. Pendientes priorizados

Prioridad alta inmediata:

1. Mover liquidaciones PENDING en cierre de turno a blocker real coherente en backend/UI/rotacion.
2. Resolver incoherencia de scope de liquidaciones entre owner y cajera.
3. Endurecer cierre de caja sin monto declarado y diferencias sin justificacion.
4. Monitorear y corregir cajas/turnos zombie.
5. Mantener estable hosting y agente con ruta legacy hasta migracion controlada.
6. Verificar en hosting `APP_ENV=production` y `APP_DEBUG=false`.
7. Formalizar rutina de preproduccion y primera noche.

Prioridad media:

1. Mejorar UX/visibilidad de estado SSE offline y estado de impresion.
2. Extender Health Center con mas checks y accionabilidad.
3. Aclarar politica de permisos de cierre de turno para cajera vs owner.
4. Endurecer procesos de force-close y TTL de sesiones/turnos.

Prioridad posterior:

1. SaaS billing y enforcement por planes.
2. Evolucion desktop/agent integration.
3. Inventario real si entra al alcance de producto.
4. Barra operativa si producto la vuelve a meter en alcance.

---

## 15. Reglas de trabajo para Copilot

Reglas operativas a respetar al trabajar en NightPOS:

- No asumir nada que no este respaldado por codigo o documentacion.
- Antes de tocar un area critica, leer su reporte maestro mas reciente.
- Para bugs reales de produccion, usar la base MySQL real importada cuando el usuario lo pida.
- No usar seeders/factories para “probar” problemas que vienen de hosting/produccion si eso deforma el caso real.
- No reactivar PWA.
- No cambiar API base a `/api/v1` sin autorizacion.
- No tocar impresion/agente sin revisar auditorias del agente.
- No tocar liquidaciones ni correlativos sin revisar document sequence y reportes de settlement.
- No modificar `dist` si no se solicita.
- No hacer commits ni push.
- Backend decide reglas; frontend no debe recalcular negocio.
- Todo cambio multi-tenant debe preservar scope por tenant/sucursal.
- En flujos sensibles, priorizar QA de comportamiento real por encima de soluciones cosmeticas.

---

## Documentos mas importantes para arrancar cualquier trabajo

Top de referencia rapida:

1. `NIGHTPOS_V1_DEVELOPMENT_MAP.md`
2. `NIGHTPOS_PRODUCTION_READINESS_AUDIT.md`
3. `NIGHTPOS_V1_HARDENING_ROADMAP.md`
4. `NIGHTPOS_HEALTH_CENTER_ARCHITECTURE.md`
5. `SAAS_ARCHITECTURE.md`
6. `DEVELOPMENT_RULES.md`
7. `backend/ARCHITECTURE_REPORT.md`
8. `backend/DOCUMENT_SEQUENCE_SERVICE_FINAL_FIX_REPORT.md`
9. `backend/HEALTH_CENTER_H1_IMPLEMENTATION_REPORT.md`
10. `backend/SETTLEMENT_PAYMENT_AUDITABLE_IMPLEMENTATION_REPORT.md`
11. `backend/SETTLEMENT_ADJUSTMENTS_ENGINE_IMPLEMENTATION_REPORT.md`
12. `backend/NIGHTPOS_REAL_DB_QA_AUDIT.md`
13. `frontend/FRONTEND_V1_COMPLETE_AUDIT_REPORT.md`
14. `frontend/SSE_1_REPORT.md`
15. `frontend/SSE_2_REPORT.md`
16. `frontend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
17. `agent/INSTALLATION_GUIDE.md`
18. `agent/PRINT_AGENT_HTTP1_BACKOFF_FIX_REPORT.md`
19. `agent/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`
20. `desktop/NIGHTPOS_DESKTOP_APP_AUDIT.md`

---

## Conclusion operativa

NightPOS ya tiene suficiente profundidad funcional para trabajar como producto serio de operacion nocturna. Lo que mas importa a partir de ahora no es “agregar muchas features”, sino tocar con precision las capas donde aun puede romperse la noche real: cierres, liquidaciones, correlativos, hosting, impresion, permisos y monitoreo.

Copilot debe tratar este proyecto como un sistema ya avanzado, con decisiones fuertes tomadas, y no como un scaffold para experimentar libremente.