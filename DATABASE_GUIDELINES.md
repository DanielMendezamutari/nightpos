# DATABASE_GUIDELINES.md

# OBJETIVO

Definir el modelo de base de datos limpio para migrar desde las 44 tablas heredadas hacia una estructura SaaS ordenada.

---

# REGLA PRINCIPAL

Todas las tablas de negocio deben tener:

* `tenant_id`
* `branch_id` cuando aplique
* timestamps
* soft deletes cuando aplique

---

# TABLAS SaaS BASE

```text
tenants
plans
plan_modules
subscriptions
branches
users
roles
permissions
role_permissions
user_branches
```

---

# TABLAS OPERATIVAS PROPUESTAS

## Ambientes

```text
rooms
room_tables
```

Reemplazan:

* `salas`
* `mesas`

---

## Productos

```text
categories
units
products
product_prices
product_recipes
combos
combo_items
```

Reemplazan:

* `categorias`
* `medidas`
* `productos`
* `ingredientes`
* `productosxingredientes`
* `combos`
* `combosxproductos`

---

# PRODUCT_PRICES

Tabla clave para boliche.

```text
id
tenant_id
branch_id
product_id
price_type
amount
currency
is_active
starts_at
ends_at
```

Valores de `price_type`:

* `REGULAR`
* `SOLO`
* `CON_ACOMPANANTE`
* `PROMO`
* `VIP`

Ejemplo:

| Producto | SOLO | CON_ACOMPANANTE |
| -------- | ---- | --------------- |
| Cerveza | 30 Bs | 60 Bs |
| Havana | 600 Bs | 1200 Bs |
| Tequila | 600 Bs | 1200 Bs |

---

# PEDIDOS / COMANDAS

```text
orders
order_items
order_item_notes
order_status_history
```

Reemplazan:

* `pedidos`
* `detallepedidos`
* `notificaciones`

Campos recomendados en `orders`:

* id
* tenant_id
* branch_id
* table_id
* waiter_id
* customer_id nullable
* order_number
* status
* opened_at
* closed_at

Campos recomendados en `order_items`:

* id
* order_id
* product_id
* quantity
* price_type
* unit_price
* total
* dispatch_area
* status
* notes

---

# VENTAS

```text
sales
sale_items
payments
```

Reemplazan:

* `ventas`
* `detalleventas`

Campos clave:

* tenant_id
* branch_id
* cash_session_id
* order_id nullable
* customer_id nullable
* seller_id
* total
* discount
* tax
* status
* sale_type

---

# CAJA

```text
cash_registers
cash_sessions
cash_movements
cash_closures
```

Reemplazan:

* `cajas`
* `arqueocaja`
* `movimientoscajas`

Reglas:

* No se puede vender si no hay caja abierta.
* Cada cajero abre su turno.
* Todo ingreso/egreso debe registrarse.
* El cierre debe cuadrar métodos de pago.

---

# INVENTARIO

```text
stock_items
stock_movements
stock_transfer
stock_transfer_items
purchase_orders
purchase_items
suppliers
```

Reemplazan:

* `kardex_productos`
* `kardex_ingredientes`
* `kardex_combos`
* `traspasos`
* `detalletraspasos`
* `compras`
* `detallecompras`
* `proveedores`

---

# CAMPOS QUE NO SE DEBEN QUEMAR

No quemar en código:

* precios
* porcentajes de comisión
* métodos de pago
* nombres de casas
* horarios de turno
* permisos
* nombres de roles
* reglas de stock
* reglas de acompañante

Todo debe vivir en base de datos o configuración.

---

# MIGRACIÓN DE DATOS

No migrar todo de golpe.

Orden recomendado:

1. tenants / branches
2. users
3. categories / units
4. products
5. product_prices
6. rooms / tables
7. cash registers
8. inventory initial stock
9. sales historical summary, si hace falta

---

# NOTA IMPORTANTE

El dump heredado tiene nombres como `codproducto`, `codsucursal`, `codventa`. En el sistema nuevo se deben usar IDs limpios y guardar los códigos heredados en campos `legacy_code` si se necesita trazabilidad.

---

# TABLAS PARA IMPRESIÓN AUTOMÁTICA

## printers

Tabla para registrar impresoras configuradas por empresa y sucursal.

```sql
CREATE TABLE printers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    destination VARCHAR(50) NOT NULL,
    connection_type VARCHAR(50) NULL,
    local_printer_name VARCHAR(150) NULL,
    ip_address VARCHAR(50) NULL,
    port INT NULL,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

Valores sugeridos para `destination`:

```text
BAR
KITCHEN
CASHIER
RECEPTION
GENERAL
```

## print_jobs

Tabla para la cola de impresión automática.

```sql
CREATE TABLE print_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    printer_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    sale_id BIGINT UNSIGNED NULL,
    document_type VARCHAR(50) NOT NULL,
    destination VARCHAR(50) NOT NULL,
    payload JSON NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'PENDING',
    copies INT NOT NULL DEFAULT 1,
    attempts INT NOT NULL DEFAULT 0,
    error_message TEXT NULL,
    printed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

Estados permitidos para `status`:

```text
PENDING
PRINTING
PRINTED
FAILED
CANCELLED
```

Tipos sugeridos para `document_type`:

```text
ORDER_TICKET
SALE_RECEIPT
CASH_CLOSING
REPRINT_ORDER
```

Regla multi tenant:

Toda consulta del Print Agent debe filtrar siempre por `tenant_id` y `branch_id`.


---

# TABLAS NUEVAS: CIERRE, COMISIONES, MANILLAS, PIEZAS Y SHOWS

## staff_profiles

Perfil operativo del personal.

```text
id
tenant_id
branch_id
user_id
staff_type -- WAITER, GIRL, CASHIER, MANAGER
commission_percent -- para garzones
active
created_at
updated_at
```

## shift_closures

Cierre de turno realizado por cajera.

```text
id
tenant_id
branch_id
cash_register_id
shift_id
cashier_id
opened_at
closed_at
opening_cash_amount
sales_cash_total
sales_qr_total
sales_card_total
sales_total
income_cash_total
expense_cash_total
waiter_commissions_total
girl_settlements_total
expected_cash_amount
counted_cash_amount
cash_difference
status -- OPEN, CLOSED, APPROVED, CANCELLED
notes
created_at
updated_at
```

## cash_movements

Movimientos de caja.

```text
id
tenant_id
branch_id
shift_id
cashier_id
type -- INCOME, EXPENSE, STAFF_PAYMENT, ADJUSTMENT
payment_method -- CASH, QR, CARD
amount
concept
reference_type
reference_id
created_at
updated_at
```

## sale_payment_breakdowns

Pagos separados por método.

```text
id
tenant_id
branch_id
sale_id
shift_id
payment_method -- CASH, QR, CARD
amount
created_at
updated_at
```

## sale_item_staff_snapshots

Snapshot por detalle de venta para liquidaciones.

```text
id
tenant_id
branch_id
sale_id
sale_item_id
product_id
product_name_snapshot
price_type -- SOLO, CON_CHICA, PROMO, VIP
base_price_snapshot
charged_price_snapshot
girl_id NULL
girl_name_snapshot NULL
girl_amount_snapshot
waiter_id
waiter_name_snapshot
waiter_commission_percent_snapshot
waiter_commission_amount
created_at
updated_at
```

## girl_settlements

Liquidación diaria de chicas.

```text
id
tenant_id
branch_id
shift_id
girl_id
girl_name_snapshot
bracelet_total
room_total
show_total
bonus_total
discount_total
total_to_pay
paid_amount
payment_method -- CASH, QR
status -- PENDING, PAID, CANCELLED
paid_at
created_at
updated_at
```

## girl_settlement_items

Detalle de lo que se paga a una chica.

```text
id
tenant_id
branch_id
girl_settlement_id
source_type -- BRACELET, ROOM, SHOW, BONUS, DISCOUNT
source_id
quantity
description
amount
created_at
updated_at
```

## waiter_settlements

Liquidación diaria de garzones.

```text
id
tenant_id
branch_id
shift_id
waiter_id
waiter_name_snapshot
sales_total
commission_percent_snapshot
commission_total
discount_total
total_to_pay
paid_amount
payment_method -- CASH, QR
status -- PENDING, PAID, CANCELLED
paid_at
created_at
updated_at
```

## room_services

Servicios de pieza.

```text
id
tenant_id
branch_id
sale_id NULL
shift_id
girl_id
girl_name_snapshot
waiter_id NULL
price_customer
amount_girl
amount_house
payment_method
status -- REGISTERED, PAID, CANCELLED
created_at
updated_at
```

## show_services

Servicios de show.

```text
id
tenant_id
branch_id
sale_id NULL
shift_id
girl_id
girl_name_snapshot
waiter_id NULL
price_customer
amount_girl
amount_house
payment_method
status -- REGISTERED, PAID, CANCELLED
created_at
updated_at
```

## product_price_rules

Reglas de precio por producto y modalidad.

```text
id
tenant_id
branch_id
product_id
price_type -- SOLO, CON_CHICA, PROMO, VIP
customer_price
girl_amount
house_amount
active
created_at
updated_at
```

# REGLA TÉCNICA

Nunca recalcular comisiones antiguas usando porcentajes actuales. Toda venta debe guardar snapshot de precio, chica, garzón, porcentaje y monto calculado.
