# MANAGERIAL REPORTS PHASE 1 PROPOSAL (FRONTEND)

Fecha: 2026-07-03
Estado: propuesta UX/IA para owner y administracion
Modo: sin implementacion
Enfoque: tenant_owner, administrador, dueño
Exclusion explicita: no orientado a cajera

## 1. Objetivo de Fase 1

Diseñar una pantalla gerencial diaria que entregue lectura ejecutiva inmediata de negocio y riesgo operativo en una sola pagina.

Preguntas objetivo cubiertas:

1. venta total de sucursal
2. ingreso por efectivo/QR/tarjeta
3. pago total de liquidaciones
4. caja real y diferencia
5. top garzones por venta
6. top garzones por pago/comision/manual
7. top chicas por generacion
8. top productos
9. mejor horario de venta
10. top habitaciones/piezas
11. pendientes y alertas
12. diferencias de caja
13. neto estimado casa

## 2. Ruta propuesta

Ruta nueva propuesta:

- /nightpos/finance/reports/managerial-daily

Nombre de ruta sugerido:

- nightpos-finance-reports-managerial-daily

Permiso de pagina:

- reports.access (Fase 1)
- opcion futura: reports.managerial.access (Fase 2 hardening de acceso)

Ubicacion navegacion:

- dentro de Finanzas > Reportes
- visible para owner/administracion
- no incluir en flujo operativo de caja

## 3. Estructura visual propuesta

Layout vertical por secciones:

1. Header ejecutivo
2. Barra de filtros
3. KPI grid principal
4. Bloque de rankings
5. Bloque de rendimiento horario
6. Bloque piezas/habitaciones
7. Bloque alertas y pendientes
8. Bloque de trazabilidad financiera y formula de neto
9. Export e impresion

## 4. Tarjetas KPI propuestas

Fila KPI principal:

- Venta total sucursal
- Efectivo
- QR
- Tarjeta
- Liquidaciones pagadas
- Liquidaciones pendientes
- Efectivo esperado
- Efectivo declarado
- Diferencia de caja
- Neto estimado casa

Fila KPI secundaria:

- cantidad de ventas
- ticket promedio
- total egresos caja
- total pendientes operativos

## 5. Rankings propuestos

### 5.1 Garzones

Tab A: Top garzones por ventas

- nombre
- total vendido
- cantidad ventas
- ticket promedio

Tab B: Top garzones por compensacion

- nombre
- total compensacion pagada
- componente auto
- componente manual
- estado pendiente si aplica

### 5.2 Chicas

Top chicas por generacion:

- nombre
- ingreso generado
- settlement pagado
- participacion porcentual

### 5.3 Productos

Top productos:

- por ingreso
- por unidades
- status conciliacion (ok/mismatch) como indicador secundario

## 6. Tablas propuestas

Tabla 1: Resumen de caja por sesion

- sesion
- estado
- expected
- declared
- diferencia
- opened_at
- closed_at

Tabla 2: Top habitaciones/piezas

- habitacion
- ingresos
- usos
- duracion promedio

Tabla 3: Alertas y pendientes

- tipo (blocker/warning)
- codigo
- mensaje
- conteo

Tabla 4: Formula de neto (trazabilidad)

- gross_revenue
- liquidaciones pagadas
- egresos caja
- neto estimado

## 7. Graficos propuestos

Grafico 1: Distribucion metodos de pago

- tipo: donut
- series: efectivo, qr, tarjeta, mixto

Grafico 2: Rendimiento por hora

- tipo: bar/line
- eje X: hora o bloque horario
- eje Y: monto vendido
- destacar mejor hora

Grafico 3: Top 10 productos por ingreso

- tipo: horizontal bar
- eje X: monto
- eje Y: producto

Grafico 4: Top garzones por ventas y por compensacion

- tipo: grouped bar
- series comparativas por persona

## 8. Filtros propuestos

Filtros de Fase 1:

- fecha desde
- fecha hasta
- turno oficial (opcional)
- top N (5/10/20)
- granularidad horaria (hora exacta vs bloque)

Regla UX:

- official_shift_id anula rango fecha visualmente y mostrar badge de scope.

## 9. Exportacion propuesta

Exportacion de Fase 1:

- CSV ejecutivo (una fila por metrica clave + tablas de ranking)
- CSV detallado por seccion (rankings, hourly, rooms, alerts)

Opcional habilitable en Fase 1 si backend lo soporta:

- PDF gerencial diario

## 10. Impresion propuesta

Impresion gerencial:

- vista imprimible dedicada estilo ejecutiva
- no reutilizar ticket operativo de caja/cierre sin adaptacion
- bloques impresos:
  - KPIs clave
  - rankings top
  - mejor horario
  - alertas
  - formula neto

## 11. Reportes actuales que se reutilizan

Reutilizacion directa de datos backend:

- daily
- sales
- settlements
- rooms
- shift-closure
- product-reconciliation
- managerial de shift summary

Reutilizacion de componentes frontend:

- ProductReconciliationPanel (adaptado en bloque secundario)
- ComboBraceletSummaryPanel (solo si hay datos)

## 12. Reportes actuales que no sirven para salida gerencial final

No aptos como pantalla ejecutiva final:

- cash tab actual (orientado a operacion de sesiones)
- services tab actual (orientado a detalle operativo)
- settlements tab actual (orientado a seguimiento operativo)

Estos quedan como fuentes de datos, no como experiencia gerencial final.

## 13. Datos faltantes hoy para UX gerencial

Faltan en salida actual:

- hourly performance listo para grafico
- ranking waiter compensacion separado auto/manual
- neto estimado consolidado en endpoint de reportes
- narrativa unica de alertas gerenciales

Faltan en muestra de datos real auditada:

- shows
- manillas
- allocations
- cierres de turno persistidos

## 14. Riesgos de performance y UX

Riesgos principales:

- payload grande si se mezclan muchos detalles en un endpoint unico
- render costoso de tablas largas sin paginacion
- recargas completas por cada cambio de filtro
- dependencias de consultas backend con N+1 en fuentes actuales

Mitigacion en diseno de Fase 1:

- top N limitado por defecto
- tablas resumidas con expand opcional
- request unico con secciones agregadas
- lazy load de secciones secundarias si se requiere

## 15. Que se deja para Fase 2

Alcance diferido:

- comparativos historicos multi-periodo (tendencia)
- consolidado multi-sucursal
- objetivos/benchmark y cumplimiento
- alertas proactivas configurables
- analitica avanzada de margen/costo
- drill-down transaccional profundo desde cada KPI

## 16. Resumen de propuesta frontend Fase 1

- nueva pantalla gerencial diaria para owner/administracion
- enfoque en lectura ejecutiva y toma de decision
- KPIs + rankings + horario + habitaciones + alertas + neto
- export e impresion orientados a gestion
- sin cambios en flujo de caja operativo
- sin cambios en modulo de liquidaciones operativas
