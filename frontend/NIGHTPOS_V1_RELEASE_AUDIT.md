# NightPOS V1 — Auditoría de Producto (Frontend)

**Fecha:** 2026-06-17  
**Tipo:** Radiografía de producto — **no auditoría de código**  
**Pregunta central:** ¿La experiencia de usuario de NightPOS V1 soporta un boliche un viernes o sábado con máxima carga?

---

## 1. Estado general del sistema

| Dimensión | Veredicto |
|-----------|-----------|
| **¿UX lista para viernes/sábado sin soporte?** | **NO** |
| **¿UX lista para piloto controlado?** | **SÍ, con reservas** |
| **Pantallas NightPOS** | ~111 bajo `pages/nightpos` |
| **Shells móviles dedicados** | Garzón, limpieza, chica, cajera básica |
| **% desarrollo UI V1** | ~99% flujos core construidos |
| **% readiness UX producción** | ~80–85% (bloqueado por QA, placeholders, fricciones bajo presión) |

### Respuesta explícita

**¿Podría instalar NightPOS hoy en un boliche real?**

La **interfaz** del núcleo operativo **sí está construida** (no es mockup). Pero **no** se debe abrir un viernes real sin:

- QA operativo ejecutado (V1-98),
- preproducción (V1-99),
- onboarding completo del local,
- y acuerdo explícito sobre barra, inventario e impresión.

---

## 2. Matriz por módulo

| Módulo | Terminado | Parcial | No existe | Fricción UX | Prioridad |
|--------|-----------|---------|-----------|-------------|-----------|
| **SaaS UI** | Wizard setup, tenants, branches, planes | `platform/settings` placeholder | Billing UI | Expectativa falsa en menú | P2 |
| **Login** | PIN, password, contexto, recordar, sesión 12h+refresh | — | Recuperación password, 2FA | Mensaje expiración OK | P3 |
| **Logout / cuenta** | Shell cajera: Cuenta en Más + menú desktop | — | — | Recién agregado; validar en QA | P2 |
| **Garzón** | Mis mesas, comandas, POS-CAT, manillas/combo, SSE | Phase D copy manillas | Precuenta dedicada | Sin mesas = pantalla vacía | **P1** |
| **Shell cajera** | Cobrar·Piezas·Venta·Caja·Más, status bar, guards | Fase 2B cobro 1 clic | — | Corrección comanda sigue en detalle | P1 |
| **Cobro cajera** | Modal inline 2 clics + Enter | — | Cobro express card | Mejor que admin; 2B pendiente | P2 |
| **Venta directa** | Pago mixto, POS-CAT | Combos manillas bloqueados | — | Página admin dentro del shell | P2 |
| **Caja** | Resumen método, close-check, blockers con «Ir» | QR/card solo en notas UI | — | Muchos pasos en cierre | P2 |
| **Liquidaciones** | Índice SSE, pagar, generar, historial | Subpáginas sin SSE cruzado | — | 3 niveles menú admin | P2 |
| **Servicios / piezas** | Tab principal Piezas + módulo completo | — | — | Menú admin 3 niveles (mitigado shell) | P3 |
| **Habitaciones** | Dashboard, listado, limpieza, mantenimiento | — | — | CLEANING atascado confunde | P1 |
| **Barra** | Ticket comanda imprimible | — | **Pantalla barra** | Bartender sin app | **P0/P1*** |
| **Productos** | CRUD, precios, categorías, unpriced, POS-CAT | — | Duplicar producto masivo | Muchos clics en admin | P3 |
| **Reportes** | 7 tabs + CSV + integración cierre | Dashboard ventas día «—» | PDF/Excel | Admin debe saber dónde mirar | P2 |
| **Kardex / inventario** | Conciliación en reportes/cierre | — | **Todo inventario UI** | Campo track_inventory visible sin función | **P0*** |
| **Clientes** | Campo mesa en comanda | — | **Módulo CRM** | — | V2 |
| **Impresión** | 6 rutas blank + `window.print()` | — | Agente, auto-print, cola | 2+ clics + diálogo navegador | **P1*** |
| **SSE UX** | Banner, reconnect, polling fallback | Algunas pantallas admin secundarias | Barra (no hay UI) | OK en flujos críticos | P2 |
| **Permisos UI** | Nav R4 CASL, shell filtrado, guards | Placeholders sin permiso (`printers`, etc.) | — | Admin ve opciones vacías | P2 |

---

## 3. Qué está terminado (experiencia de usuario)

### Autenticación y contexto
- Login PIN rápido con selección empresa/sucursal.
- Login password + superadmin sin tenant.
- Redirect automático por rol (`resolveHomeRoute`).
- Sesión operativa 12h + refresh silencioso + logout claro.
- Shell cajera: sección **Cuenta** (cerrar sesión / cambiar cuenta).

### Garzón (móvil)
- Shell blank, bottom nav, **Mis mesas** grid táctil.
- Crear comanda desde mesa, POS-CAT (favoritos, recientes, búsqueda).
- Manillas, combos, acompañantes, enviar barra.
- Imprimir comanda (navegador).
- SSE + polling 30s en comandas.

### Cajera básica (shell)
- **Cobrar | Piezas | Venta | Caja | Más**.
- Cola cobro inline (Fase 1) — ~2 clics + Enter.
- Banner caja cerrada + abrir inline en todos los tabs.
- Status bar: caja, pendientes BOB, SSE.
- Tab Piezas = módulo room-services sin duplicar en Más.
- Tab Más: operación/catálogo/config/finanzas por permiso.
- Mi caja: resumen por método, cierre con declaración por método.

### Admin / senior
- Menú R4 completo (operación, caja, finanzas, catálogo, personal, config).
- Consola turno, fiscalización multicaja, reportes 7 tabs.
- Cierre turno con KPIs, conciliación, ticket imprimible.

### Otros roles
- Limpieza móvil: SSE, sonido pieza vencida, marcar limpia.
- Chica: ingresos, servicios asignados.

### Tiempo real
- `useOperationalEvents` compartido, reconexión, indicadores visuales.

### Impresión mínima V1
- Comanda, venta, caja, sesión, turno, precheck — `window.print()`.

---

## 4. Qué está parcialmente terminado

| Área | Estado UX | Gap |
|------|-----------|-----|
| **Cobro bajo presión** | Modal inline funcional | Fase 2B: efectivo 1 clic desde card |
| **Corrección comanda** | Pantalla detalle completa | 5–6 clics vs 2 del cobro — asimétrico |
| **Venta directa en shell** | Reutiliza página admin | Layout denso en móvil |
| **Dashboard operativo** | Enlaces rápidos | KPI «ventas del día» placeholder |
| **Liquidaciones** | Índice con SSE y alertas | Garzones/Chicas/Limpieza recargan en mount |
| **Configuración** | Motivos, pagos, mesas OK | Impresoras, Preferencias, Seguridad = placeholder |
| **SaaS** | Wizard + planes | Settings plataforma «Próximamente» |
| **Reportes** | Tabs completos | Sin PDF; export limitado |
| **Cierre caja** | Blockers con navegación | Proceso largo si muchos pendientes |
| **QA / piloto** | Checklists definidos | **No ejecutados** |

### Flujos — clics estimados (producto)

| Flujo | Clics/toques | Naturalidad |
|-------|--------------|-------------|
| Cobrar comanda (shell) | ~2 + Enter | ✅ Bueno |
| Corregir ítem comanda | ~5–6 | ⚠️ Aceptable, lento |
| Garzón: mesa → comanda en barra | ~3–5 | ✅ Bueno |
| Garzón sin mesas asignadas | 0 (bloqueado) | ❌ Sin salida |
| Venta directa mixta | ~4–8 | ⚠️ OK |
| Cierre caja con pendientes | 10+ (iterativo) | ⚠️ Correcto pero pesado |
| Imprimir comanda barra | 2+ + diálogo OS | ⚠️ Lento en volumen |
| Admin cierre de noche completo | 8–15+ | ⚠️ Esperado pero largo |

---

## 5. Qué no existe (UI V1)

- **Pantalla de barra** (preparación, estados, expedición).
- **Módulo inventario / kardex** (entradas, salidas, conteos, compras).
- **Módulo clientes** (CRM, créditos, historial).
- **Impresión automática** (cola, agente, térmica silenciosa).
- **Config impresoras funcional** (solo placeholder).
- **PDF/Excel** nativo en reportes.
- **Portal analítico** chica/garzón avanzado.
- **Facturación electrónica** UI.

---

## 6. Riesgos (experiencia y operación)

### UX bajo presión (viernes)

1. **Garzón parado** si nadie asignó mesas — mensaje «pide a la cajera» sin workaround.
2. **Impresión manual** — cuello de botella si cada comanda va a barra en papel.
3. **Sin barra en pantalla** — bartender depende de ticket o memoria.
4. **Cierre sorpresa** — blockers de piezas/liquidaciones sin entrenamiento previo.
5. **Dos cajeras** — dos «Mi caja»; la UI no unifica caja física del local.
6. **Placeholders en menú admin** — admin curioso entra a Impresoras/Preferencias vacías.
7. **Combos en mostrador** — venta directa no permite; cajera debe explicar flujo comanda.

### Información redundante / confusa

- **Piezas** aparecía en Más y ahora en tab principal — ✅ corregido.
- **Venta directa** duplicada en Operación y Caja (admin); shell solo en tab Venta.
- **Dashboard** vs **Reportes** vs **Consola** — tres lugares para KPIs similares.
- **`track_inventory`** en formulario producto sin efecto visible — expectativa falsa.

### Pantallas que pueden sentirse lentas

- Catálogo admin con muchos productos (mitigado por POS-CAT en operación).
- Cierre turno con conciliación + múltiples secciones.
- Venta directa con muchos ítems en móvil.
- Apertura ticket impresión (nueva pestaña + render).

*Sin mediciones de performance en producción — riesgo percibido, no benchmark.*

---

## 7. Prioridad — Hallazgos clasificados

### P0 — Bloquea producción (UX/producto)

| ID | Hallazgo |
|----|----------|
| F-P0-01 | **V1-98 no ejecutado** — flujos por rol sin evidencia en piso |
| F-P0-02 | **V1-99 no completado** — frontend no desplegado formalmente en prod |
| F-P0-03 | **Sin UI barra** — si operación depende de pantalla cocina |
| F-P0-04 | **Sin UI inventario** — si operación depende de stock |
| F-P0-05 | **Impresión solo navegador** — si local exige automático |

### P1 — Antes de liberar V1

| ID | Hallazgo |
|----|----------|
| F-P1-01 | Garzón bloqueado sin mesas — proceso onboarding obligatorio |
| F-P1-02 | Capacitación cierre caja/turno (blockers, métodos, liquidaciones) |
| F-P1-03 | Ocultar placeholders (Impresoras, Preferencias, Seguridad, SaaS settings) o implementar mínimo |
| F-P1-04 | Dashboard operativo: KPI ventas del día real o quitar card |
| F-P1-05 | Documentar en UI/help: combos solo por comanda |
| F-P1-06 | Validar shell cajera en celular real (5 tabs + bottom nav) |
| F-P1-07 | Política impresión acordada con operación |

### P2 — V1.1

| ID | Hallazgo |
|----|----------|
| F-P2-01 | Fase 2B: cobro efectivo 1 clic desde card |
| F-P2-02 | SSE en subpáginas liquidaciones (waiters/girls/cleaning) |
| F-P2-03 | Corrección comanda en modal (como cobro) |
| F-P2-04 | Export PDF reportes |
| F-P2-05 | Venta directa layout compacto en shell móvil |
| F-P2-06 | Ocultar `track_inventory` hasta V2 |

### P3 — V2

Barra UI, kardex, CRM, print agent, BI dashboards, facturación.

---

## 8. Recomendación

**No abrir viernes real** hasta V1-98 + V1-99.

**Sí permitir piloto** si:

- Se usa checklist 1ª noche + asignación mesas.
- Se capacita en shell cajera (orden tabs: Cobrar → Piezas → …).
- Se acepta impresión manual y ausencia de barra/inventario.
- Hay soporte de guardia primera noche.

**Simplificaciones inmediatas (sin código en esta fase):**

- Operación admin: usar **Consola turno** + **Reportes** como fuente de verdad (no dashboard).
- Cajera básica: vivir en **shell**; no enseñar menú R4.
- Garzones: verificar **Mis mesas** antes de abrir puertas.
- Dueño: firmar alcance V1 por escrito.

---

## 9. Tiempo estimado para cerrar UX V1

| Bloque | Esfuerzo |
|--------|----------|
| V1-98 QA UX (todos roles, celular real) | 2–3 días |
| Fixes P1 UX (placeholders, dashboard, docs in-app) | 1–2 días |
| Fase 2B cobro 1 clic (opcional recomendado alto volumen) | 1–2 días |
| Simulacro + ajustes | 1–2 días |
| **Total UX hasta RC** | **~5–9 días** (paralelo a backend/infra) |

---

## 10. Orden recomendado para cerrar V1 (frontend)

1. Ejecutar checklist §7 mapa en **dispositivos reales** (cajera Android, garzón iPhone).
2. Ocultar o completar **placeholders** que generan confusión.
3. Completar **dashboard** o redirigir a reportes.
4. **Fase 2B** si local es alto volumen en cobro.
5. Documentación in-app / capacitación 30–60 min por rol.
6. Build prod + smoke test rutas críticas shell.
7. Declarar RC cuando 0 P0/P1 UX abiertos.

---

## 11. Checklist Release Candidate (frontend)

- [ ] Login PIN todos los roles → home correcto
- [ ] Shell cajera: 5 tabs visibles según permiso
- [ ] Cobro inline ≤3 interacciones en comanda simple
- [ ] Tab Piezas abre room-services con shell
- [ ] Más sin duplicado Piezas + sección Cuenta funcional
- [ ] Garzón Mis mesas → comanda → barra en celular
- [ ] POS-CAT 20+ productos sin scroll infinito
- [ ] SSE visible en cajera/garzón/limpieza (sin F5 10 min)
- [ ] Venta directa pago mixto
- [ ] Cierre caja: resumen método + blockers + «Ir»
- [ ] Liquidaciones pagar desde UI
- [ ] Ticket comanda imprimible desde navegador
- [ ] Reportes: resumen diario + caja del turno
- [ ] `npm run build` sin errores
- [ ] Sin placeholders visibles en menú operativo diario

---

## 12. Checklist Producción (frontend)

- [ ] Todo RC +
- [ ] `VITE_API_BASE_URL` apunta a API prod HTTPS
- [ ] Build desplegado en servidor web (no solo dev)
- [ ] PWA/cache headers si aplica
- [ ] Login probado en red móvil del local (WiFi + datos)
- [ ] Impresión probada con impresora real del cliente (si aplica)
- [ ] Sesión 12h validada en turno simulado
- [ ] Rollback: versión anterior frontend disponible

---

## 13. Checklist Primer Cliente (frontend)

- [ ] Wizard completado desde superadmin UI
- [ ] Admin entra y completa checklist 1ª noche UI
- [ ] Productos con precio (sin alerta unpriced bloqueante)
- [ ] Ambientes + mesas creados en settings
- [ ] Asignar mesas a garzones (`staff/waiter-assignments`)
- [ ] Usuarios PIN creados y probados (login cada uno)
- [ ] Cajera ve shell; admin ve menú completo
- [ ] Simulacro UI 2h con personal del local
- [ ] Entrega documento «alcance V1» + «qué no está»
- [ ] Contacto soporte visible primera noche

---

## Auditoría por módulo — Detalle

### SaaS — ¿Listo para venderse?

**NO** como producto self-service completo. UI de wizard y planes existe; falta billing, enforcement, onboarding sin superadmin, y settings plataforma.

### Garzón — re-auditoría

| Ítem | Estado |
|------|--------|
| Mesas | ✅ Mis mesas, tap-to-open |
| Comandas | ✅ Completo |
| Combos/manillas | ✅ En comanda |
| Acompañantes | ✅ CON_ACOMPANANTE |
| Precuenta | ⚠️ Solo imprimible, no flujo precuenta formal |
| UX tiempo | ✅ 3–5 toques a barra |
| Phase D copy manillas | ❌ Pendiente |

### Cajera — experiencia completa

| Ítem | Estado |
|------|--------|
| Shell | ✅ 5 tabs |
| Cobro | ✅ Fase 1; 2B pendiente |
| Venta | ✅ |
| Caja | ✅ Con métodos y blockers |
| Liquidaciones | ✅ Accesible shell + Más |
| Piezas | ✅ Tab principal |
| Cierre | ✅ Con declaración por método |
| Logout | ✅ Cuenta en Más + desktop |

### Permisos UI — respuestas

| Pregunta | Respuesta |
|----------|-----------|
| ¿Permisos demasiado amplios? | Cajera provisioner tiene `shifts.close` (más que demo); revisar |
| ¿Innecesarios? | Placeholders visibles sin función |
| ¿Pantallas sin permiso? | Guards redirigen; algunos settings `permission: null` |
| ¿Acciones sin backend? | No detectado en flujos core; guards alineados en rutas principales |

---

## Checklist final

### ¿Podría instalar NightPOS hoy en un boliche real?

**NO** para viernes autónomo de máxima carga.

### ¿Qué falta exactamente? (lista concreta)

1. **Ejecutar V1-98** — checklist cajera/garzón/limpieza/chica/admin/superadmin en dispositivos reales.
2. **Desplegar frontend en preprod/prod** (V1-99) con HTTPS.
3. **Onboarding UI:** mesas, garzones asignados, catálogo con precios.
4. **Capacitación 30–60 min** shell cajera + cierre con blockers.
5. **Decisión impresión** — si no aceptan navegador, V1 no cumple.
6. **Aceptar ausencia** de barra, kardex, CRM — o posponer go-live.
7. **Fase 2B** (recomendado) si volumen de cobro es muy alto.
8. **Ocultar placeholders** que confunden al admin en primera semana.
9. **Simulacro 3 h** noche completa en UI antes del primer viernes.
10. **Soporte de guardia** primera noche real.

### ¿Cuándo declarar RELEASE CANDIDATE?

Cuando V1-98 y V1-99 estén completos, simulacro sin hallazgos **P0/P1**, y el local firmó alcance V1.

---

*Auditoría de producto frontend. Sin cambios de código. Complementa `backend/NIGHTPOS_V1_RELEASE_AUDIT.md`.*
