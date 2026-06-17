# AUDITORÍA OPERATIVA — CAJERA ALTA PRESIÓN (Frontend)

**Fecha:** 2026-06-16  
**Alcance:** Experiencia completa de la cajera en NightPOS  
**Tipo:** Auditoría operativa (no técnica)  
**Par backend:** `backend/CASHIER_HIGH_PRESSURE_OPERATION_AUDIT.md`  
**Referencia filosofía:** módulo Garzón post-rediseño (`waiter/index.vue`, shell móvil, tap-to-action)

---

## Resumen ejecutivo

Hoy la cajera opera sobre un **layout de administración de escritorio** con menú lateral completo, muchas pantallas secundarias visibles y flujos que **obligan a cambiar de página** para cobrar. La información crítica (tiempo de espera, acompañante, combo, pendientes de barra) **no aparece en la cola de cobro**. El pago mixto es funcional pero **no tiene atajos de teclado ni cobro en un toque desde la lista**.

Comparado con Garzón — que hoy tiene shell dedicado, bottom nav de 2 ítems, grid táctil y acción directa al tocar mesa — la cajera sigue pensada como **operadora de backoffice**, no como rol de primera línea en un sábado lleno.

**Veredicto:** usable en turno normal; **insuficiente bajo presión**. Una cajera nueva necesita más de 15 minutos porque debe aprender navegación admin + flujos de corrección + liquidaciones en tablas. Una cajera experimentada pierde tiempo en clics, scroll y idas/vueltas entre pantallas.

> **Fase 0 implementada 2026-06-16** — ver `CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`. Cola enriquecida, Enter/Esc, `?open=1`, ventas SSE.  
> **Fase 1 implementada 2026-06-16** — ver `CASHIER_HIGH_PRESSURE_PHASE1_REPORT.md`. Cobro inline desde cola (2 clics). Pendiente Fase 2: shell cajera.

---

## 1. Estado actual

### 1.1 Home y navegación

| Aspecto | Hoy |
|---------|-----|
| Pantalla de inicio | Consola de turno (`/nightpos/shift-console`) |
| Layout | Materialize vertical nav — **mismo que admin** |
| Shell móvil cajera | **No existe** (Garzón sí: `layout: blank` + bottom nav) |
| Menú visible | Operación (7+ ítems), Caja (3), Finanzas (Liquidaciones + Turno), Habitaciones (5), Servicios (4+) |
| Atajos globales | **Ninguno** (Enter, Esc, doble clic, swipe) |

### 1.2 Flujo operativo real hoy

```
Login → Consola de turno
  → Abrir caja (Mi caja, 3–4 clics)
  → Cobrar comandas (lista → detalle → modal pago → volver)
  → Venta directa (catálogo + carrito + pago)
  → Movimientos (Mi caja o Liquidaciones resumen)
  → Liquidaciones (Resumen → pestaña Garzones/Chicas → Pagar)
  → Cierre (Mi caja → check blockers → arqueo → imprimir)
```

### 1.3 Tiempo real

| Pantalla | SSE | Polling fallback | Banner visible |
|----------|-----|------------------|----------------|
| Consola de turno | ✅ | ✅ 30 s | ✅ |
| Cobrar comandas | ✅ | ✅ | ✅ |
| Detalle comanda | ✅ | ✅ | ✅ |
| Mi caja | ✅ | ❌ | ✅ |
| Liquidaciones | ✅ | ❌ | ❌ (resumen) |
| Venta directa | Solo caja open/close | ❌ | ❌ |
| Ventas del turno | ❌ | ❌ | ❌ |

### 1.4 Conteo de clics — flujos frecuentes (estado actual)

| Flujo | Clics actuales | Notas |
|-------|----------------|-------|
| Abrir caja al inicio | **4** | Nav → Mi caja → Abrir caja → Confirmar |
| Cobro simple (efectivo, sin corrección) | **5–6** | Nav → Cobrar comandas → Cobrar → Todo efectivo → Confirmar → (volver implícito) |
| Cobro con corrección de ítem | **8+** | + Ver/corregir → editar → Cobrar comanda → pago |
| Cobro con caja cerrada | **7–8** | Alerta → Abrir caja → … → volver → Cobrar |
| Venta directa 1 producto efectivo | **5** | Nav → producto → Todo efectivo → Cobrar |
| Pagar liquidación garzón | **6** | Nav → Finanzas → Garzones → Pagar → Confirmar |
| Movimiento egreso típico | **5–6** | Nav → Mi caja → Egreso → motivo + monto → Registrar |
| Cierre sin bloqueos | **4** | Nav → Mi caja → Cerrar → Confirmar |
| Imprimir arqueo | **3** | Nav → Mi caja → Imprimir arqueo |

---

## 2. Pantalla por pantalla

---

### 2.1 Consola de turno (Dashboard cajera)

**Archivo:** `shift-console/index.vue`  
**Ruta:** `/nightpos/shift-console`

#### Las 10 preguntas operativas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Qué hace hoy? | Muestra KPIs (turno, caja, comandas, habitaciones, piezas vencidas, liquidaciones), accesos rápidos, resumen caja, habitaciones, alertas, servicios, tabla de comandas con 4 pestañas. |
| 2 | ¿Cuántos clics? | **0** para ver estado; **1** para ir a cobrar/venta/caja desde accesos rápidos. |
| 3 | ¿Qué necesita la cajera? | Caja abierta/cerrada, efectivo/QR/tarjeta esperado, comandas pendientes cobro, liquidaciones pendientes, alertas urgentes (piezas vencidas). |
| 4 | ¿Qué sobra? | KPIs de habitaciones en limpieza/mantenimiento, servicios del turno (manillas/shows), tabla completa de comandas duplicando «Cobrar comandas», breadcrumbs admin. |
| 5 | ¿Qué falta? | Tiempo promedio de espera, cola «clientes esperando cobro» ordenada por antigüedad, garzones activos, total BOB pendiente de cobro, indicador visual único «¿puedo cobrar ya?». |
| 6 | ¿Qué debería ser un toque? | Ir a cobrar la comanda más antigua; abrir caja si está cerrada; ver solo alertas rojas. |
| 7 | ¿Qué hace perder tiempo? | Scroll largo antes de acciones; KPIs no priorizados (misma jerarquía visual); tabla comandas obliga a leer filas pequeñas. |
| 8 | ¿Qué obliga a escribir? | Nada en esta pantalla. |
| 9 | ¿Qué debería ser automático? | Refresh (ya tiene SSE + poll); resaltar comanda más urgente; contador «X esperando > 10 min». |
| 10 | ¿Qué puede ser más rápido? | Condensar a **una franja superior de 6 chips** + 3 botones gigantes (Cobrar / Venta / Caja). |

#### Wireframe propuesto — Consola cajera (ideal)

```
┌─────────────────────────────────────────────────────────────┐
│  CAJA ABIERTA ✓    Efectivo 1.240   QR 380   Tarjeta 920   │
│  7 comandas pendientes · 3 liq. pend. · 2 piezas vencidas   │
├─────────────────────────────────────────────────────────────┤
│  [ COBRAR COMANDAS ]  [ VENTA DIRECTA ]  [ MI CAJA ]        │
├─────────────────────────────────────────────────────────────┤
│  URGENTE (>10 min)                                          │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                     │
│  │ Mesa 12  │ │ VIP 3    │ │ Barra 2  │                     │
│  │ 18 min ⏱ │ │ 12 min   │ │ 11 min   │                     │
│  │ 450 BOB  │ │ 890 BOB  │ │ 120 BOB  │                     │
│  │ [COBRAR] │ │ [COBRAR] │ │ [COBRAR] │                     │
│  └──────────┘ └──────────┘ └──────────┘                     │
└─────────────────────────────────────────────────────────────┘
```

**Clics objetivo desde login:** **1** para cobrar la más urgente (vs 5–6 hoy).

---

### 2.2 Cobrar comandas

**Archivo:** `cashier/orders/index.vue`  
**Ruta:** `/nightpos/cashier/orders`

#### Las 10 preguntas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Qué hace hoy? | Lista en cards: mesa, estado, Nº, garzón, hora, ítems, total. Tabs: Pendientes / Cobradas recientes. Botones Ver/corregir y Cobrar. |
| 2 | ¿Cuántos clics? | **2** hasta modal de pago (Cobrar → Todo efectivo → Confirmar = **4** en total). |
| 3 | ¿Qué necesita? | Mesa, garzón, **tiempo esperando**, total, estado, cantidad productos, flags: acompañante/combo/piezas/pendientes barra. |
| 4 | ¿Qué sobra? | Nº comanda técnico en posición secundaria; subtítulo explicativo; botón «Ver/corregir» del mismo peso que Cobrar. |
| 5 | ¿Qué falta? | **Tiempo esperando** (solo hora absoluta); iconos/chips combo/manilla/acompañante; estado barra; pendientes de asignación; orden por antigüedad explícita; cobro inline sin ir al detalle. |
| 6 | Un toque | **Cobrar** directo desde card (modal pago); doble toque o ícono lápiz para corregir. |
| 7 | Pierde tiempo | Navegación obligatoria al detalle; dos botones apilados; cards 3 columnas en desktop dejan comandas abajo del fold. |
| 8 | Escribir | No en lista. |
| 9 | Automático | Ordenar por `waiting_minutes` desc; SSE ya recarga; badge «NUEVA» 30 s. |
| 10 | Más rápido | Modal de pago **desde la card**; swipe en móvil → Cobrar. |

#### Información visible vs necesaria

| Campo | ¿Visible hoy? | ¿Necesario sin entrar al detalle? |
|-------|---------------|-----------------------------------|
| Mesa | ✅ grande | ✅ |
| Garzón | ✅ | ✅ |
| Tiempo esperando | ❌ (solo hora) | ✅ **crítico** |
| Total | ✅ | ✅ |
| Estado | ✅ chip | ✅ |
| Cantidad productos | ✅ | ✅ |
| Acompañante | ❌ | ✅ |
| Combo/manillas | ❌ | ✅ |
| Piezas/habitación | ❌ | ⚠️ si aplica |
| Pendientes barra/asignación | ❌ | ✅ **crítico** |

#### Wireframe — Cola de cobro ideal

```
[Pendientes (7)]  [Cobradas hoy]
Caja: ABIERTA ✓

┌─────────────────────────────────────────┐
│ MESA 12          ⏱ 18 min    LISTA ✓    │
│ Juan (garzón) · 6 ítems                 │
│ 👤 Acomp.  🎫 Combo  ⚠ 1 sin manilla   │
│                              450,00 BOB │
│         [    COBRAR    ]  (tap = pago)  │
└─────────────────────────────────────────┘
     ↑ long-press / ícono ✎ = corregir
```

**Clics actuales → objetivo:** **5–6 → 2** (tap card → Todo efectivo → Enter).

---

### 2.3 Detalle de comanda (modo cajera)

**Archivo:** `orders/[id].vue` + `OrderHeader`, `OrderItemsTable`, `OrderActionsBar`

#### Las 10 preguntas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Qué hace hoy? | Cabecera (Nº, mesa, estado, notas), tabla ítems editable en modo corrección, totales, barra fija inferior (Agregar / Cobrar / Cancelar). `charge=1` abre modal al entrar. |
| 2 | ¿Cuántos clics? | Cobro desde aquí: **2** (Cobrar comanda → confirmar pago) si ya está en la página. |
| 3 | ¿Qué necesita? | Total grande, mesa, garzón, **alertas de bloqueo** (sin chica, sin manilla), ítems problemáticos arriba. |
| 4 | ¿Qué sobra? | Notas vacías, timestamps ISO en cabecera, acciones admin (Enviar a barra oculta en corrección — bien). |
| 5 | ¿Qué falta? | Garzón en cabecera; total sticky arriba; resumen «3 bebidas, 2 combos»; filtro «solo pendientes». |
| 6 | Un toque | Cobrar desde barra fija (ya existe); Enter en modal. |
| 7 | Pierde tiempo | **Mucho scroll** en comandas largas; cabecera no muestra total; ítems cancelados mezclados; corrección = muchos diálogos. |
| 8 | Escribir | Cantidades, notas cancelación, montos pago, búsqueda producto al agregar. |
| 9 | Automático | Abrir modal si `charge=1`; bloquear cobro si falta manilla/chica (parcial — hint en barra). |
| 10 | Más rápido | Cabecera fija: mesa + total + alertas; colapsar ítems OK; sección «Requiere acción» arriba. |

#### Wireframe — Detalle ideal (zona fija)

```
┌─ FIJO ─────────────────────────────────────┐
│ ← Mesa 12 · Juan · 450,00 BOB              │
│ ⚠ Combo sin manilla (1) · Chica falta (1)  │
│ [ COBRAR ]  [ + Producto ]                 │
├────────────────────────────────────────────┤
│ REQUIERE ACCIÓN                            │
│ · Combo Premium ×2 — asignar manilla       │
│ · Ron ×1 CON_ACOMP — elegir chica          │
├────────────────────────────────────────────┤
│ (scroll) resto de ítems OK…                │
└────────────────────────────────────────────┘
```

**Clics actuales → objetivo:** corrección+cobro **8+ → 4** (menos scroll, acciones agrupadas).

---

### 2.4 Pago (comandas y venta directa)

**Componentes:** `ChargeOrderModal.vue`, `MixedPaymentForm.vue`, `direct-sale.vue`

#### Análisis pago simple / mixto / cambio / QR / tarjeta

| Aspecto | Estado | Problema operativo |
|---------|--------|-------------------|
| Pago 100% efectivo | Botón «Todo efectivo» | Bien, pero **2 clics** (botón + Confirmar) |
| Pago 100% QR | «Todo QR» | Igual |
| Pago 100% tarjeta | «Todo tarjeta» | Igual |
| Pago mixto | 3 campos numéricos | Obliga **escribir** o tab entre campos |
| Cambio | «Monto recibido» si hay efectivo | Campo extra; cajera debe calcular o escribir |
| Enter para cobrar | ❌ | Pierde velocidad en PC |
| Esc cancelar | ❌ | |
| Método por defecto | ❌ | Cada cobro empieza en cero |
| Memoria sesión | ❌ | Si 80% es QR, repite «Todo QR» siempre |
| Errores | Toast «Revise montos» | Correcto pero **reactivo**, no preventivo |
| Tiempo | Modal persistente | No se puede encadenar cobros rápidos |

#### Wireframe — Modal pago ideal

```
┌─────────────────────────────────┐
│  COBRAR — Mesa 12               │
│  TOTAL        450,00 BOB        │
├─────────────────────────────────┤
│ [EFECTIVO] [  QR  ] [TARJETA]   │  ← tabs/chips, uno activo
│  Monto: [450,00    ]            │
│  Recibido: [500]  Cambio: 50    │
├─────────────────────────────────┤
│ [ MIXTO ]                       │  ← expande 3 campos
│                                 │
│  Enter = COBRAR    Esc = cerrar │
└─────────────────────────────────┘
```

**Clics actuales → objetivo:** pago simple **2 → 1** (Enter o tap método único auto-confirma opcional).

---

### 2.5 Venta directa

**Archivo:** `cash/direct-sale.vue`

| # | Respuesta resumida |
|---|-------------------|
| Hoy | Catálogo 7/12 + carrito 5/12; favoritos; pago mixto inline; bloquea combos. |
| Clics 1 producto | **5** |
| Necesita | Favoritos grandes, total visible, cobrar sin scroll en móvil. |
| Sobra | Texto ayuda pago mixto largo. |
| Falta | Teclado numérico rápido; últimos productos vendidos; atajo Enter. |
| Un toque | Tap producto = +1 al carrito + vibración. |
| Pierde tiempo | **Móvil:** catálogo arriba, carrito abajo → scroll constante. |
| Escribir | Montos pago; selector chica CON_ACOMPANANTE. |
| Automático | Recordar último método de pago. |
| Más rápido | Layout móvil: carrito sticky footer con total + COBRAR. |

**Clics objetivo:** **5 → 3** (producto → Todo efectivo → Enter).

---

### 2.6 Mi caja (apertura, movimientos, cierre, arqueo)

**Archivo:** `cash/index.vue`

| # | Respuesta resumida |
|---|-------------------|
| Hoy | KPIs sesión, ventas por método, movimientos paginados, reconciliación productos, combos, cerrar con arqueo, imprimir. |
| Abrir caja | **4 clics**; `?open=1` en URL **no abre diálogo** (bug UX). |
| Necesita al operar | Estado, esperado por método, botones Ingreso/Egreso/Cerrar grandes. |
| Sobra en turno activo | Reconciliación productos (útil al cierre, no entre cobros). |
| Falta | Accesos «últimos movimientos» compactos; chip diferencia arqueo en vivo. |
| Un toque | Ingreso/Egreso con motivos frecuentes predefinidos. |
| Pierde tiempo | Mucho scroll para llegar a botones; KPIs duplican consola. |
| Escribir | Fondo inicial, monto contado cierre, movimientos. |
| Automático | Expected amount ya calculado — bien. |
| Cierre | Pre-check blockers — **excelente concepto**; links a acciones — bien. |

**Clics cierre objetivo:** **4 → 3** (contado pre-rellenado con esperado editable).

---

### 2.7 Movimientos de caja

**Componente:** `CashMovementDialog.vue`

#### Movimientos reales en turno (operación boliche)

| Movimiento | Frecuencia | Campos hoy | Ideal |
|------------|------------|------------|-------|
| Compra urgente (hielo, vasos) | Alta | tipo, monto, método, motivo, notas | **2 clics:** chip «Compra bar» + monto |
| Préstamo a garzón | Media | idem | Chip «Préstamo personal» |
| Propina retirada | Media | idem | Chip «Propina» |
| Corrección cambio | Baja | idem | Chip «Ajuste cambio» |
| Ingreso cambio inicial extra | Baja | idem | Desde apertura |

**Clics actuales:** **5–6** por movimiento.  
**Clics objetivo:** **2–3** (chip motivo → monto → Enter).

---

### 2.8 Liquidaciones

**Archivos:** `settlements/index.vue`, `waiters.vue`, `girls.vue`, `cleaning.vue`

| # | Respuesta resumida |
|---|-------------------|
| Hoy | Resumen 9 KPIs + generar; tablas por rol; pago método único; banner caja. |
| Clics pagar garzón | **6** |
| Necesita | Lista **solo PENDING**, monto, nombre, botón PAGAR grande. |
| Sobra | Columnas %, ventas count, generado/pagado en operación rápida. |
| Falta | Pago masivo «pagar todos garzones efectivo»; vista unificada pendientes; método por defecto. |
| Un toque | Pagar con último método usado. |
| Pierde tiempo | **Cambiar pestaña** Garzones/Chicas/Limpieza; tabla admin `VDataTable`. |
| Escribir | Notas opcionales. |
| Automático | Generate al cierre de corte (opcional config); SSE refresh — ya implementado en waiters/girls. |

#### Wireframe — Liquidaciones rápidas

```
PENDIENTES (5) — 1.240 BOB total
[Filtro: Todos | Garzones | Chicas | Limpieza]

┌──────────────────────────────────────┐
│ Juan Pérez (Garzón)      180,00 BOB  │
│ [PAGAR EFECTIVO] [QR] [TARJETA]      │
└──────────────────────────────────────┘
```

**Clics objetivo:** **6 → 2** (tap PAGAR EFECTIVO → confirmación inline).

---

### 2.9 Reportes rápidos / Ventas del turno

**Archivo:** `sales/index.vue`

- Tabla ventas con filtros; **sin SSE** — datos stale.
- Uso operativo: verificar último cobro, reimprimir ticket.
- **Falta:** botón «Última venta» / «Reimprimir» en consola cajera.
- **Clics objetivo:** reimpresión **3 → 1**.

---

### 2.10 Precuentas y tickets

**Rutas print:** `print/precheck/order/[id]`, `print/order/[id]`, `print/sale/[id]`

- Precuenta desde detalle comanda — no visible en cola cobro.
- Cajera a veces imprime precuenta antes de cobro: requiere entrar al detalle.
- **Ideal:** icono imprimir en card de cola; precuenta 1 tap.

---

### 2.11 Productos / Clientes esperando

- **Productos:** catálogo admin — cajera no debería necesitarlo en turno (solo venta directa picker).
- **Clientes esperando:** **no existe** como concepto UI; proxy = comandas pendientes cobro sin tiempo relativo.

---

## 3. Problemas encontrados

### 3.1 Críticos (bloquean operación rápida)

| ID | Problema | Impacto sábado noche |
|----|----------|----------------------|
| C-01 | Cobro obliga ir al **detalle** | Cola lenta; 2 cargas de página por cobro |
| C-02 | Cola cobro **sin tiempo de espera** ni flags combo/acompañante | Garzones y clientes reclaman; cajera entra a cada una «a ciegas» |
| C-03 | **Sin shell cajera** (nav admin completo) | Distrae; curva aprendizaje > 15 min |
| C-04 | **Sin atajos teclado** (Enter/Esc) | PC en caja pierde 1–2 s por cobro × cientos |
| C-05 | `?open=1` no abre diálogo caja | Fricción cuando caja cerrada en pleno rush |
| C-06 | Ventas del turno **sin tiempo real** | Duda «¿cobré?» → F5 o re-navegar |

### 3.2 Menores

| ID | Problema |
|----|----------|
| M-01 | Venta directa duplicada en menú Operación y Caja |
| M-02 | Consola duplica lista comandas vs Cobrar comandas |
| M-03 | OrderHeader no muestra garzón ni total |
| M-04 | Liquidaciones: muchas columnas en tabla |
| M-05 | Breadcrumbs y subtítulos admin en todas las pantallas |
| M-06 | Pago mixto siempre muestra 3 campos (ruido visual) |
| M-07 | Sin modo oscuro optimizado para caja (brillo nocturno) |
| M-08 | Cards cobro 3 columnas — comandas importantes abajo en 1080p |

---

## 4. Flujo ideal (filosofía Garzón aplicada a Cajera)

### Principios

1. **Una mano, un ojo** — lo crítico arriba, sin scroll.
2. **Tap = acción** — cobrar desde cola; corregir = gesto secundario.
3. **Máximo 3 ítems de navegación** — Cobrar | Venta | Caja (+ Más).
4. **Tiempo visible** — minutos esperando, no timestamps.
5. **Errores imposibles** — no dejar pulsar Cobrar si falta manilla/chica.
6. **Enter confirma, Esc cancela** — en PC.
7. **SSE + banner** — en todas las pantallas operativas.

### Flujo ideal viernes 23:00

```
Login → Shell cajera (sin sidebar admin)
  → Barra estado: Caja ✓ | 7 pendientes | 1.240 BOB en cola
  → Tap comanda → Modal pago → Enter → siguiente comanda (sin volver atrás)
  → Swipe lateral → Venta directa
  → Fin turno → Caja → Arqueo guiado (3 pasos)
```

---

## 5. Comparación con Garzón

| Dimensión | Garzón (post-rediseño) | Cajera (hoy) |
|-----------|------------------------|--------------|
| Layout | `blank` + bottom nav 2 tabs | Admin vertical nav |
| Acción principal | Tap mesa → comanda | Tap Cobrar → **otra página** → modal |
| Resumen | Chips libres/ocupadas | KPIs tipo dashboard admin |
| Móvil | `@styles/waiter-mobile`, targets grandes | Responsive genérico |
| SSE | Mesas/comandas | Cola cobro ✅; ventas ❌ |
| Curva 15 min | ✅ alcanzable | ❌ requiere mapa mental admin |
| Foco | Solo mesas asignadas | 20+ rutas visibles |

**Meta:** cajera debe sentir la misma **densidad operativa** que garzón: pantalla = herramienta, no panel de control.

---

## 6. Atajos — oportunidades

| Atajo | Dónde | Beneficio |
|-------|-------|-----------|
| **Enter** | Modal pago, movimiento, abrir caja | −1 clic por acción |
| **Esc** | Cerrar modales | Flujo continuo |
| **Doble clic card** | Cola cobro → cobrar efectivo | −2 clics |
| **F2 / Ctrl+K** | Buscar mesa en cola | Rush con 20 mesas |
| **Swipe →** | Cola → cobrar (móvil) | Una mano |
| **Chips método** | Pago y liquidaciones | Sin dropdown |
| **Long press** | Card → corregir | Separar cobro/corrección |
| **Acción masiva** | «Cobrar efectivo» en comanda seleccionada | Bajo prioridad |

---

## 7. Errores — prevención por diseño (no mensajes)

| Error humano | Prevención UX |
|--------------|---------------|
| Cobrar comanda sin manillas | Card roja «No cobrable»; botón Cobrar disabled + chip |
| Cobrar sin chica en CON_ACOMPANANTE | Igual |
| Cobrar con caja cerrada | Banner global persistente; modal abrir caja inline |
| Método pago equivocado | Chips grandes; último método resaltado |
| Excedente/faltante en mixto | Barra progreso visual hacia total |
| Cerrar caja con pendientes | Blockers con links — **mantener y mejorar** |
| Liquidar sin caja | Banner settlements — ya existe |
| Cobrar comanda ajena / ya cobrada | Optimistic lock + card desaparece con SSE |

---

## 8. Tiempo real — qué debe actualizarse solo

| Dato | Auto hoy | Debe ser auto |
|------|----------|---------------|
| Cola cobro | ✅ SSE | ✅ |
| Totales caja | ✅ SSE | ✅ |
| Detalle comanda abierta | ✅ SSE | ✅ |
| Consola turno | ✅ SSE + poll | ✅ |
| Liquidaciones | ✅ SSE | ✅ |
| Ventas del turno | ❌ | ✅ |
| Posición en cola / tiempo espera | ❌ | ✅ (timer local + sync) |
| Cambio caja cerrada otro terminal | Parcial | ✅ banner global |

---

## 9. Diseño visual

| Aspecto | Hoy | Recomendación |
|---------|-----|---------------|
| Jerarquía | Todo `text-body-2` similar | Mesa + total `text-h4`; resto secundario |
| Colores | Chips Vuetify estándar | Verde=cobrar, Amarillo=espera, Rojo=bloqueo |
| Botón Cobrar | `size="x-large"` en card | OK — **único CTA primario** |
| Contraste | Tema claro admin | Modo caja oscuro alto contraste |
| Espaciado | Cards aireadas | Más densidad en cola (más comandas visibles) |
| Área táctil | 48px+ en botones principales | OK en Cobrar; mejorable en tablas liquidaciones |
| PC vs móvil | Mismo layout | Shell responsive; bottom nav en móvil |

---

## 10. Rendimiento percibido

| Pantalla | Problema | Efecto operativo |
|----------|----------|------------------|
| Cobrar → detalle | 2 requests + render tabla completa | 1–3 s pausa entre cobros |
| Consola turno | Muchos componentes + tabla tabs | Carga inicial pesada |
| Mi caja | Reconciliación + movimientos + KPIs | Scroll infinito |
| Liquidaciones | 3 páginas separadas | Cambio contexto mental |
| Direct sale móvil | Catálogo grande | Scroll fatigue |

**Meta:** cobro encadenado < 1 s entre confirmación y siguiente card.

---

## 11. Roadmap por fases

### Fase 0 — Quick wins (1–2 días)

- Enter/Esc en modales pago y cierre.
- `?open=1` abre diálogo en Mi caja.
- SSE + banner en ventas del turno.
- Ordenar cola por antigüedad; mostrar «hace X min».
- Deshabilitar Cobrar en card si flags backend lo indican.

**Clics ahorrados:** ~15–20% en turno típico.

### Fase 1 — Cola de cobro alta presión (1 semana)

- Modal pago **desde card** (sin navegar a detalle).
- Chips combo/acompañante/pendiente en card.
- Cabecera fija en detalle (mesa + total + alertas).
- Shell cajera: ocultar nav admin; 3 bottom tabs móvil / barra superior PC.

**Clics:** cobro simple **5–6 → 2**.

### Fase 2 — Pago y venta directa (1 semana)

- Pago por chips método único + mixto colapsable.
- Memoria último método de pago.
- Venta directa: footer sticky total + cobrar.
- Reimprimir última venta desde consola.

**Clics:** venta directa **5 → 3**.

### Fase 3 — Liquidaciones y movimientos (1 semana)

- Vista unificada pendientes con filtros chips.
- Pagar en 1–2 clics (método como chip, no select).
- Movimientos: motivos frecuentes como chips.

**Clics:** liquidación **6 → 2**.

### Fase 4 — Consola y cierre (1 semana)

- Consola condensada «3 segundos».
- Cierre guiado 3 pasos; reconciliación solo al cierre.
- Precuenta desde cola.

### Fase 5 — Pulido

- Modo oscuro caja.
- Atajos F2 búsqueda.
- Sonido opcional nueva comanda (config).

---

## 12. Qué implementar primero

**Orden recomendado:**

1. **Cobro desde cola sin entrar al detalle** — mayor ROI en sábado.
2. **Tiempo de espera + flags en card** — reduce entradas innecesarias al detalle.
3. **Enter para confirmar pago** — gratis en PC.
4. **Shell cajera simplificado** — reduce errores de navegación.
5. **Liquidaciones vista única pendientes** — fin de turno menos caótico.

---

## 13. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Cobro rápido sin revisar comanda | Modo «corregir» explícito; bloqueos manilla/chica |
| Dos cajeras cobran misma comanda | SSE + disabled optimista; backend idempotencia |
| Ocultar nav admin pierde acceso habitaciones | Tab «Más» con rutas secundarias |
| Memoria método pago wrong | Mostrar método activo grande antes de Enter |
| Scope regresión liquidaciones | No tocar core settlements; solo UI |

---

## 14. Beneficio operativo esperado

| Métrica | Antes | Después (Fase 1–2) |
|---------|-------|---------------------|
| Clics cobro simple | 5–6 | 2 |
| Tiempo cobro encadenado | ~8–12 s | ~3–5 s |
| Entradas al detalle / hora | Alta | −60% |
| Curva aprendizaje cajera nueva | ~30–45 min | **< 15 min** |
| Reclamos «no vi la comanda» | SSE frágil | SSE + poll + banner |
| Estrés percibido | «El sistema me frena» | «Sigo al cliente» |

---

## 15. Matriz clics — resumen global

| Flujo | Actual | Objetivo | Fase |
|-------|--------|----------|------|
| Abrir caja | 4 | 2 | 0–1 |
| Cobro efectivo simple | 5–6 | 2 | 1 |
| Cobro + corrección | 8+ | 4 | 1–2 |
| Venta directa 1 ítem | 5 | 3 | 2 |
| Movimiento egreso | 5–6 | 2–3 | 3 |
| Pagar liquidación | 6 | 2 | 3 |
| Cierre caja | 4 | 3 | 4 |
| Precuenta | 4+ | 1 | 4 |

---

*Auditoría operativa frontend — sin cambios de código. Próximo paso: revisión conjunta y priorización de Fase 0.*
