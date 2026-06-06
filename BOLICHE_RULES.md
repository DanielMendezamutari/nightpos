# BOLICHE_RULES.md

# OBJETIVO

Definir reglas específicas para adaptar el sistema de restaurante a boliche/night club.

---

# REGLA PRINCIPAL DE PRECIOS

Un mismo producto puede tener varios precios según modalidad.

Ejemplo: cerveza.

| Modalidad | Descripción | Precio ejemplo |
| --------- | ----------- | -------------- |
| SOLO | Cliente consume solo | 30 Bs |
| CON_ACOMPANANTE | Cliente consume con acompañante | 60 Bs |

Ejemplo: botella.

| Producto | SOLO | CON_ACOMPANANTE |
| -------- | ---- | --------------- |
| Havana | 600 Bs | 1200 Bs |
| Tequila | 600 Bs | 1200 Bs |

---

# VALUE OBJECT

Crear `PriceType`:

```php
enum PriceType: string
{
    case REGULAR = 'REGULAR';
    case SOLO = 'SOLO';
    case CON_ACOMPANANTE = 'CON_ACOMPANANTE';
    case PROMO = 'PROMO';
    case VIP = 'VIP';
}
```

---

# CASO DE USO

Crear:

```text
ResolveProductPriceUseCase
```

Responsabilidad:

* recibir producto, sucursal y modalidad
* validar precio activo
* devolver precio oficial
* evitar que frontend mande precio manipulado

---

# PEDIDOS

Cuando el garzón agrega producto:

```json
{
  "product_id": 1,
  "quantity": 1,
  "price_type": "CON_ACOMPANANTE"
}
```

Backend debe guardar:

* price_type
* unit_price resuelto
* quantity
* total

---

# CAJA

Reglas:

* No se puede cobrar sin caja abierta.
* El cajero solo ve su caja o las cajas permitidas.
* El cierre debe separar efectivo, QR, tarjeta y otros.
* Todo egreso debe tener descripción.

---

# GARZONES

Reglas:

* Garzón crea comanda.
* Garzón no define precio manual.
* Garzón puede elegir modalidad permitida.
* Encargado/cajero puede corregir con permiso.

---

# COMISIONES

Preparar para:

* comisión por garzón
* comisión por producto
* comisión por venta con acompañante
* comisión fija o porcentaje

No implementar quemado; usar tabla:

```text
commission_rules
```

Campos:

* tenant_id
* branch_id
* role
* product_id nullable
* price_type nullable
* calculation_type
* amount
* is_active

---

# LIMPIEZA / INGRESOS ESPECIALES

El usuario maneja reglas como limpieza o ingresos adicionales.

Crear tipo de movimiento:

* `SALE`
* `CLEANING`
* `PENALTY`
* `TAXI_COMMISSION`
* `OTHER_INCOME`
* `EXPENSE`

No mezclar estos conceptos con venta de productos.

---

# PROMOCIONES

Promos posibles:

* 2x1
* cover gratis
* botella + acompañantes
* cortesía
* descuento por horario

Deben modelarse como reglas futuras, no como texto suelto.

---

# AUDITORÍA

Auditar:

* cambio de precio
* anulación de venta
* cancelación de comanda
* apertura/cierre de caja
* egresos
* ajustes de stock
* cambios de usuario/permiso

---

# MVP PARA VALIDAR BOLICHE

Caso mínimo:

1. Admin crea producto Cerveza.
2. Define precio SOLO 30 Bs.
3. Define precio CON_ACOMPANANTE 60 Bs.
4. Cajero abre caja.
5. Garzón abre mesa.
6. Agrega Cerveza con modalidad CON_ACOMPANANTE.
7. Backend resuelve precio 60 Bs.
8. Cajero cobra.
9. Caja registra ingreso.
10. Reporte muestra venta por modalidad.

---

# IMPRESIÓN POR DESTINO OPERATIVO

Una misma comanda puede generar varios trabajos de impresión según el tipo de producto vendido.

Ejemplo:

| Producto | Destino |
| -------- | ------- |
| Cerveza | BAR |
| Whisky | BAR |
| Pique macho | KITCHEN |
| Entrada VIP | CASHIER |
| Servicio especial | CASHIER |

Cuando el garzón registre una comanda, el sistema debe agrupar los ítems por destino e imprimir solo lo que corresponde a cada área.

Ejemplo:

```text
Comanda #150
- 2 cervezas
- 1 Havana con acompañante
- 1 pique macho
```

Debe generar:

```text
PrintJob #1 → BAR
Contenido:
- 2 cervezas
- 1 Havana con acompañante

PrintJob #2 → KITCHEN
Contenido:
- 1 pique macho
```

El garzón no debe hacer ningún paso adicional. La impresión se dispara automáticamente después de guardar la comanda.

La comanda debe seguir visible en el sistema aunque la impresión falle. Si falla, se debe mostrar como pendiente/fallida para que caja o encargado pueda reimprimir.


---

# REGLAS NUEVAS: CIERRE DE CAJA Y LIQUIDACIÓN DIARIA

Estas reglas se agregan porque el boliche paga al personal el mismo día del turno. La cajera necesita cerrar caja sabiendo exactamente cuánto se vendió, cuánto entró por cada método de pago y cuánto debe pagarse a garzones y chicas.

## 1. Cierre de turno por método de pago

Al finalizar el turno, la cajera debe ver separado:

```text
Total vendido en efectivo
Total vendido por QR
Total vendido por tarjeta
Total general vendido
Total gastos/egresos del turno
Total a pagar a garzones
Total a pagar a chicas
Total en manillas
Total en piezas
Total en shows
Efectivo esperado en caja
Diferencia/sobrante/faltante
```

El cierre no debe mezclar todos los pagos en un solo total. Debe permitir conciliación real por método de pago.

## 2. Productos con modalidad SOLO / CON_CHICA

Un producto puede tener dos precios principales:

```text
Precio solo cliente
Precio con chica
```

Ejemplos:

| Producto | Cliente solo | Con chica | Monto chica sugerido |
| -------- | ------------ | --------- | -------------------- |
| Paceña | 40 Bs | 80 Bs | 40 Bs |
| Huari | 50 Bs | 100 Bs | 50 Bs |

Cuando el garzón registra una bebida con modalidad `CON_CHICA`, debe seleccionar obligatoriamente a la chica.

El sistema debe guardar el snapshot completo de la operación:

```text
producto_id
producto_nombre_snapshot
precio_cliente_snapshot
precio_con_chica_snapshot
modalidad
chica_id
chica_nombre_snapshot
monto_chica_snapshot
garzon_id
garzon_nombre_snapshot
porcentaje_garzon_snapshot
metodo_pago
turno_id
sucursal_id
tenant_id
```

No se debe depender del precio actual del producto ni del porcentaje actual del garzón para calcular cierres antiguos.

## 3. Manillas

La diferencia entre precio solo y precio con chica puede ser tratada como manilla o comisión de chica.

Ejemplo:

```text
Paceña con chica = 80 Bs
Precio base cliente = 40 Bs
Monto para chica = 40 Bs
```

La cajera debe poder ver por chica:

```text
Chica: María
Manillas Paceña: 3 x 40 = 120 Bs
Manillas Huari: 2 x 50 = 100 Bs
Total manillas: 220 Bs
```

## 4. Piezas

Además de manillas, una chica puede ganar por piezas.

Una pieza debe registrarse como servicio independiente o como item de venta ligado a una chica.

Ejemplo:

```text
Servicio: Pieza
Precio cliente: 150 Bs
Monto chica: 80 Bs
Monto casa: 70 Bs
```

La cajera debe ver:

```text
Chica: María
Piezas realizadas: 2
Total piezas: 160 Bs
```

## 5. Shows

Una chica también puede ganar por shows.

Ejemplo:

```text
Servicio: Show
Precio cliente: 200 Bs
Monto chica: 100 Bs
Monto casa: 100 Bs
```

Debe quedar separado de manillas y piezas.

## 6. Comisión variable de garzones

Cada garzón puede tener un porcentaje diferente.

Ejemplo:

```text
Pedro = 5%
Luis = 6%
Marco = 4%
```

El porcentaje debe ser configurable por administrador y guardarse como snapshot en cada venta.

Campos obligatorios en venta o detalle de venta:

```text
waiter_id
waiter_name_snapshot
waiter_commission_percent_snapshot
waiter_commission_amount
```

Esto evita que al cambiar el porcentaje futuro se modifiquen cierres anteriores.

## 7. Liquidación diaria de chicas

El cierre debe mostrar por chica:

```text
Nombre de chica
Total manillas
Total piezas
Total shows
Bonos si aplica
Descuentos/multas si aplica
Total a pagar
Estado: pendiente / pagado
Método de pago: efectivo / QR
```

## 8. Liquidación diaria de garzones

El cierre debe mostrar por garzón:

```text
Nombre de garzón
Total vendido asignado
Porcentaje aplicado
Comisión generada
Descuentos/multas si aplica
Total a pagar
Estado: pendiente / pagado
Método de pago: efectivo / QR
```

## 9. Regla contable del cierre

El sistema debe calcular:

```text
Efectivo esperado = ventas_efectivo + ingresos_efectivo - egresos_efectivo - pagos_personal_efectivo
```

QR y tarjeta deben mostrarse aparte porque no siempre están físicamente en caja.

## 10. Regla principal para Cursor

Cada venta debe guardar toda la información necesaria para el cierre de turno, sin recalcular con datos actuales.

Cursor debe implementar esto como reglas de dominio, no como cálculos sueltos en frontend.
