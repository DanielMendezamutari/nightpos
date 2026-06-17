# NightPOS V1 — Auditoría de Producto (Backend)

**Fecha:** 2026-06-17  
**Tipo:** Radiografía de producto — **no auditoría de código**  
**Pregunta central:** ¿NightPOS V1 puede operar un boliche un viernes o sábado con máxima carga?

---

## 1. Estado general del sistema

| Dimensión | Veredicto |
|-----------|-----------|
| **¿Listo para viernes/sábado real sin soporte?** | **NO** |
| **¿Listo para piloto controlado (1 caja, datos precargados, dev/ops de guardia)?** | **SÍ, con reservas** |
| **¿Listo para venderse como SaaS comercial?** | **NO** (sin billing, enforcement límites, QA/preprod) |
| **% desarrollo funcional V1 (según mapa)** | ~99% features construidas |
| **% readiness producción** | ~70–75% (bloqueado por proceso + exclusiones de alcance) |
| **Suite tests backend** | 529 passing (evidencia automatizada; no sustituye QA operativo) |

### Respuesta explícita

**¿Podría instalar NightPOS hoy en un boliche real?**

- **Instalación técnica:** sí (API operativa, multi-tenant, flujos core).
- **Operación autónoma viernes noche:** **no** hasta cerrar hallazgos P0/P1 listados abajo.

El backend es un **núcleo POS nocturno maduro**, no un prototipo. La brecha no es “falta construir comandas/caja/liquidaciones”, sino **validación en piso**, **infraestructura de producción**, **expectativas de alcance** (barra, kardex, impresión automática) y **consistencia de permisos en tenants nuevos**.

---

## 2. Matriz por módulo

| Módulo | Terminado | Parcial | No existe | Riesgo operativo | Prioridad |
|--------|-----------|---------|-----------|------------------|-----------|
| **SaaS — empresas/sucursales** | Wizard, CRUD tenants/branches, platform setup | Planes/límites advisory | Billing recurrente, enforcement SAAS-4 | Límites no bloquean uso real | P2 |
| **SaaS — usuarios/roles/permisos** | ~95 slugs, middleware, roles locales | Drift provisioner vs demo seeder | — | Tenant nuevo ≠ tenant demo | **P1** |
| **SaaS — planes/suscripciones** | CRUD planes, usage vs limits | — | Cobro, renovación, suspensión | No es producto SaaS vendible aún | P2 |
| **Login — PIN/password** | ✅ | — | Recuperación contraseña | PIN sin rate limit visible | P2 |
| **Login — refresh/logout** | JWT 12h, refresh 14d, logout invalida | — | — | OK post-ajuste 2026-06-17 | P3 |
| **Garzón — mesas/comandas** | Mis mesas, open guard, POS-CAT, combos/manillas | Phase D copy/move manillas | — | Sin mesas asignadas = garzón parado | **P1** |
| **Garzón — precuenta** | Estado comanda + datos ticket | — | API precuenta formal | Solo vía frontend print | P2 |
| **Cajera — cobro/venta/caja** | Charge, direct sale, mixed, close-check, scope sesión | Combo con manillas solo vía comanda | — | Caja por usuario (no física) | **P1** |
| **Cajera — liquidaciones** | Generate, pay, partial, por método | Scope turno/sesión sensible | — | Bloquea cierre si desalineado | P1 (mitigado) |
| **Caja — arqueo/métodos** | expected_by_method, declared cash, QR/card en notas | declared_qr/card no en DB | — | Arqueo físico solo efectivo | P2 |
| **Productos/catálogo** | CRUD, precios SOLO/CON_ACOMP, POS-CAT | track_inventory inerte | Kardex | Flag inventario engañoso | P2/P3 |
| **Combos** | Allocations en comanda, cierre combo bracelets | Venta directa combo bloqueada | — | Cajera debe usar comanda | **P1** |
| **Barra** | send-to-bar → SENT_TO_BAR | — | Pantalla barra, IN_PREPARATION/READY | Local espera cocina = gap | **P0/P1*** |
| **Habitaciones/piezas** | CRUD, estados, room services, cleaning deduction | — | — | CLEANING atascado bloquea piezas | P1 |
| **Liquidaciones** | Waiter/girl/cleaning, segundo corte, comisiones | Regenerar con datos históricos | — | Complejidad turno multicaja | P1 |
| **Reportes** | 8 endpoints + CSV turno + conciliación | — | PDF backend | CSV + printable frontend OK V1 | P3 |
| **Kardex** | — | Conciliación productos (no stock) | Stock, movimientos, compras | Expectativa “inventario” = falsa | **P0*** |
| **Inventario** | Campo track_inventory | — | Todo el módulo | — | V2 |
| **Clientes/CRM** | table_label texto libre | — | Entidad cliente | — | V2 |
| **Impresión** | Enriquecimiento datos ticket | — | print_jobs, agente térmico | Depende de navegador | **P1*** |
| **SSE** | 17 eventos, token, filtros rol | Shift open/close, algunas rooms | — | Latencia poll ~2s | P2 |
| **Seguridad** | Multi-tenant testeado, branch scope | Audit parcial | Rate limit PIN | HTTPS/backups sin formalizar | **P0** |
| **Performance** | POS-CAT server-side | SSE DB poll, shift console pesado | Load tests documentados | Pico viernes sin evidencia | P2 |

\* P0 solo si el local **exige** ese módulo (barra, kardex, impresión automática).

---

## 3. Qué está terminado

- Autenticación multi-tenant: PIN, password, contexto empresa/sucursal, JWT + refresh.
- Ciclo comanda completo: crear → ítems → combo/manillas/acompañantes → enviar barra → cobrar (mixto).
- Venta directa con pago mixto (excepto combos con asignación manillas).
- Sesiones de caja: apertura, movimientos por método, cierre con close-check operativo.
- Liquidaciones garzones/chicas/limpieza: generar, pagar, parciales, egreso por método en caja.
- Turnos oficiales, auto-rotación, consola, cierre con KPIs y export CSV.
- Servicios: manillas, piezas, shows; habitaciones con estados y limpieza.
- Garzón Fase B: mesas, mis mesas, anti-duplicado apertura.
- Reportes operativos (8) + conciliación productos + combo bracelets en cierre.
- SSE infra + 17 eventos de negocio conectados.
- SaaS: wizard, tenants, branches, planes con límites (informativos).
- RBAC completo en rutas API.
- 529 tests automatizados verdes.

---

## 4. Qué está parcialmente terminado

| Área | Qué funciona | Qué falta |
|------|--------------|-----------|
| **Cierre de caja** | Efectivo arqueado, resumen por método, blockers | QR/tarjeta declarados solo en notas |
| **Permisos tenant nuevo** | Provisioner crea roles base | No sincronizado con `SeedsNightPosFoundation` (drift) |
| **Auditoría** | Charge, ventas, correcciones, roles, cierres | Apertura caja, send-to-bar, generate settlements, PIN reset |
| **SSE** | Comandas, caja, piezas, liquidaciones | Algunos eventos room/shift; latencia |
| **SaaS comercial** | Planes y usage | Billing, enforcement, onboarding self-service |
| **Impresión** | Datos en API para tickets | Cola, agente, auto-print |
| **Barra** | Flag SENT_TO_BAR + cobro | Flujo cocina/preparación |
| **Productos** | track_inventory en schema | Descuento automático en venta |
| **QA / Preprod** | Checklists definidos en mapa | Ejecución 0% / ~20% |

### Drift permisos crítico (provisioner vs demo)

| Permiso / capacidad | `casa-demo` (seeder) | Tenant wizard (`TenantDefaultRolePermissions`) |
|---------------------|----------------------|-----------------------------------------------|
| Cajera `shifts.close` | No | **Sí** |
| Cajera `products.quick_create` | Sí | **No** |
| Cajera `settings.service_tables` | Sí | **No** |
| Owner `shift_console`, `settings.*`, `roles.*` | Sí (amplio) | **Reducido** |
| Garzón `waiter.my_tables` | En seeder vía permisos | Sí en provisioner |
| Chica `settlements.access` | Sí en demo | **No** en provisioner |

**Riesgo:** primer cliente creado por wizard ≠ comportamiento probado en `casa-demo`.

---

## 5. Qué no existe (V1 por diseño)

Documentado en `NIGHTPOS_V1_DEVELOPMENT_MAP.md` §5:

- **Kardex / inventario operativo** (stock, compras, mermas, ajustes, conteos).
- **Módulo barra** (pantalla preparación, estados IN_PREPARATION/READY).
- **CRM / clientes** (ficha, créditos, historial).
- **Impresión automática** (print_jobs, agente local, térmica silenciosa).
- **Facturación electrónica**.
- **Suscripciones SaaS** (cobro recurrente, SAAS-2).
- **Enforcement límites plan** (SAAS-4).
- **BI gerencial avanzado**, multi-moneda, app nativa.

### Kardex — respuesta explícita

| Pregunta | Respuesta |
|----------|-----------|
| ¿Existe kardex operativo? | **No** |
| ¿Descuenta stock? | **No** (`track_inventory` no se usa en cobros) |
| ¿Reconstruye movimientos? | Solo **conciliación** comandado vs vendido (control de cierre) |
| ¿Puede hacerse inventario? | **No** |
| ¿Qué falta? | Tabla stock, movimientos, entradas/salidas, integración venta → egreso stock |

---

## 6. Riesgos

### Operativos (viernes noche)

1. **Garzones sin mesas asignadas** — no pueden abrir comandas.
2. **Caja por usuario** — dos cajeras = dos sesiones; confusión de arqueo.
3. **Cierre bloqueado** por piezas activas, liquidaciones pendientes o comandas sin cobrar — correcto pero sorprende sin capacitación.
4. **Combos con manillas** — no vendibles en mostrador; solo comanda.
5. **Habitación en CLEANING** — bloquea nueva pieza hasta liberar.
6. **Sin pantalla barra** — dependencia de ticket impreso o memoria del bartender.
7. **Impresión manual** — lenta bajo 50+ comandas/hora.

### Técnicos / seguridad

1. **HTTPS, backups, restore** no formalizados (V1-99).
2. **Audit trail incompleto** — disputas de quién hizo qué.
3. **Sin load test documentado** — pico real sin evidencia.
4. **PIN brute-force** — sin rate limiting evidente.

### Comerciales

1. **SaaS sin billing** — no vendible como suscripción autónoma.
2. **Límites de plan** — informativos, no bloquean.

---

## 7. Prioridad — Hallazgos clasificados

### P0 — Bloquea producción

| ID | Hallazgo | Módulo |
|----|----------|--------|
| B-P0-01 | **V1-98 QA operativo no ejecutado** (0%) — sin `QA_OPERATIVO_V1_REPORT.md` | Proceso |
| B-P0-02 | **V1-99 preproducción ~20%** — sin HTTPS/backups/restore probado | Infra |
| B-P0-03 | **Kardex ausente** — si el local exige control de stock | Inventario |
| B-P0-04 | **Barra ausente** — si el local exige pantalla de preparación | Barra |
| B-P0-05 | **Impresión automática ausente** — si el local exige ticket sin intervención | Impresión |

### P1 — Resolver antes de liberar V1

| ID | Hallazgo | Módulo |
|----|----------|--------|
| B-P1-01 | Sincronizar `TenantDefaultRolePermissions` con seeder demo (fuente única) | Permisos |
| B-P1-02 | Documentar y capacitar: caja por usuario + cierre con blockers | Caja |
| B-P1-03 | Documentar: combos con manillas solo vía comanda | Ventas |
| B-P1-04 | Simulacro obligatorio: mesas → garzones → primera noche | Onboarding |
| B-P1-05 | Política impresión: navegador OK vs bloqueante | Impresión |
| B-P1-06 | Expandir audit log (apertura caja, settlements, PIN) | Seguridad |
| B-P1-07 | Validar scope liquidaciones/cierre en multicaja mismo turno | Liquidaciones |

### P2 — V1.1

| ID | Hallazgo |
|----|----------|
| B-P2-01 | SSE emitters faltantes (shift open/close, room maintenance) |
| B-P2-02 | declared_qr/card en schema de cierre |
| B-P2-03 | Load test POS-CAT + cola cobro |
| B-P2-04 | SAAS-4 enforcement límites |
| B-P2-05 | Phase D garzón (copy/move manillas) |
| B-P2-06 | Ocultar o implementar `track_inventory` |

### P3 — V2

Kardex completo, barra, CRM, print agent, PDF reportes, billing SaaS, BI, facturación electrónica.

---

## 8. Recomendación

**No declarar NIGHTPOS V1 RELEASE CANDIDATE** hasta:

1. Ejecutar **V1-98** completo (checklist §7 del mapa).
2. Completar **V1-99** (HTTPS, backups, `.env` prod, build desplegado).
3. Unificar permisos provisioner/seeder.
4. Firmar **acta de alcance** con el local: sin barra, sin kardex, impresión vía navegador.
5. Simulacro 3 h de noche completa en sucursal piloto.

**Para piloto esta semana:** usar tenant `casa-demo` o tenant wizard **después** de auditoría manual de permisos + checklist 1ª noche.

---

## 9. Tiempo estimado para cerrar V1

| Bloque | Esfuerzo | Dependencia |
|--------|----------|-------------|
| V1-98 QA operativo (todos los roles) | 2–3 días | Operación + dev |
| V1-99 preproducción | 2–4 días | Infra |
| Sync permisos provisioner | 0.5–1 día | Dev |
| Documentación operativa (caja, barra, kardex, impresión) | 1 día | Producto |
| Simulacro piloto + fixes P1 | 2–3 días | Operación |
| **Total hasta RC sin P0/P1** | **~8–12 días** | — |

*No incluye V2 (barra, kardex, print agent).*

---

## 10. Orden recomendado para cerrar V1

1. **Unificar permisos** provisioner ↔ demo seeder (evita sorpresas en primer cliente).
2. **V1-99 preproducción** — HTTPS, backup/restore, env prod, CORS.
3. **V1-98 QA operativo** — checklist por rol en entorno preprod.
4. **Acta de alcance** firmada con operación del boliche.
5. **Simulacro noche completa** — corregir solo P1 encontrados.
6. **Declarar RELEASE CANDIDATE** si 0 hallazgos P0/P1 abiertos.
7. **Primera noche real** con soporte de guardia.
8. Retrospectiva → V1.1 backlog.

---

## 11. Checklist Release Candidate

- [ ] 529+ tests backend verdes en CI
- [ ] Permisos provisioner = seeder (o matriz documentada y aceptada)
- [ ] `POST /auth/refresh` probado en entorno preprod
- [ ] Close-check consistente con liquidaciones (tests `SettlementCloseCheckConsistencyTest`)
- [ ] Settlement payment por método (CASH/QR/CARD) verificado
- [ ] Garzón `my-tables` + open guard probado
- [ ] Combo allocation solo en comanda documentado
- [ ] Reportes + CSV cierre turno generan datos coherentes
- [ ] SSE stream estable 4+ horas
- [ ] Migraciones limpias en DB vacía
- [ ] `QA_OPERATIVO_V1_REPORT.md` creado con evidencia
- [ ] Acta: sin kardex, sin barra UI, impresión navegador

---

## 12. Checklist Producción

- [ ] Todo RC +
- [ ] `APP_DEBUG=false`, `JWT_SECRET` rotado, `JWT_TTL=720`
- [ ] HTTPS obligatorio
- [ ] Backup automático diario + restore probado
- [ ] Logs centralizados / alertas mínimas
- [ ] CORS restringido a dominio frontend
- [ ] Queue/scheduler si aplica
- [ ] Plan rollback documentado
- [ ] `PREPRODUCCION_V1_REPORT.md` firmado
- [ ] Contacto soporte 24h primera semana

---

## 13. Checklist Primer Cliente

- [ ] Wizard: empresa + sucursal + admin owner
- [ ] Plan asignado (SAAS-1)
- [ ] Bootstrap / checklist 1ª noche: catálogo, precios, ambientes, mesas
- [ ] Garzones creados + **mesas asignadas**
- [ ] Cajeras PIN + capacitación caja por usuario
- [ ] Limpieza + chicas PIN
- [ ] Métodos de pago configurados
- [ ] Motivos de caja configurados
- [ ] Simulacro 2–3 h antes del primer viernes
- [ ] Política impresión acordada
- [ ] Documento “qué NO tiene V1” entregado al dueño

---

## Checklist final — Respuesta concreta

### ¿Podría instalar NightPOS hoy en un boliche real?

**NO** para operación autónoma de alto volumen.

### ¿Qué falta exactamente?

1. Ejecutar **QA operativo V1-98** (0% hecho).
2. Completar **preproducción V1-99** (HTTPS, backups, env prod).
3. **Unificar permisos** de tenants creados por wizard.
4. **Simulacro de noche completa** con todos los roles.
5. **Decisión firmada** sobre barra, kardex e impresión (si el local los exige → no es V1).
6. **Capacitación** caja por usuario, cierre con blockers, combos por comanda.
7. **Onboarding** mesas + garzones antes de abrir puertas.

### ¿Cuándo sí?

Cuando el local acepta el alcance V1 (sin barra, sin stock, impresión manual), usa datos precargados o bootstrap completo, y se completa V1-98 + V1-99 sin hallazgos P0/P1 abiertos.

---

*Documento de auditoría de producto. No implica cambios de código. Próximo paso: ejecutar V1-98 y cerrar hallazgos P0/P1 antes de declarar RELEASE CANDIDATE.*
