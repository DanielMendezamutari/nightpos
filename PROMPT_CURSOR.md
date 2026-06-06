# PROMPT_CURSOR.md

# PROMPT MAESTRO PARA CURSOR

Eres un agente de desarrollo trabajando en la migración del sistema heredado `restaurant_bolivia-1` hacia **backend, frontend**, un sistema para boliches, bares y night clubs.

Debes respetar estrictamente estos documentos:

* `README.md`
* `SYSTEM_ANALYSIS.md`
* `SAAS_ARCHITECTURE.md`
* `DATABASE_GUIDELINES.md`
* `DEVELOPMENT_RULES.md`
* `API_DOCUMENTATION.md`
* `FRONTEND_GUIDELINES.md`
* `MIGRATION_PLAN.md`
* `ROADMAP.md`
* `BOLICHE_RULES.md`

---

# CONTEXTO DEL SISTEMA HEREDADO

El sistema original es PHP monolítico para restaurante.

Tiene pantallas como:

* `productos.php`
* `pedidos.php`
* `ventas.php`
* `cajas.php`
* `arqueos.php`
* `mesas.php`
* `salas.php`
* `compras.php`
* `traspasos.php`
* `comanda_bar.php`

Tiene base de datos MySQL con 44 tablas, incluyendo:

* `usuarios`
* `sucursales`
* `salas`
* `mesas`
* `productos`
* `combos`
* `ingredientes`
* `pedidos`
* `detallepedidos`
* `ventas`
* `detalleventas`
* `cajas`
* `arqueocaja`
* `movimientoscajas`
* `kardex_productos`
* `kardex_ingredientes`
* `compras`
* `traspasos`

La lógica principal está en `class/class.php`.

---

# OBJETIVO

Crear un backend Laravel API REST con arquitectura hexagonal y un frontend Vue 3.

No debes extender el sistema PHP heredado.

El heredado solo se usa para entender reglas.

---

# ARQUITECTURA OBLIGATORIA

Usar:

```text
app/Domain
app/Application
app/Infrastructure
app/Presentation
```

El dominio no debe depender de Laravel.

Los casos de uso no deben depender de Eloquent.

Los controllers solo reciben requests y llaman casos de uso.

---

# PRIMERA TAREA RECOMENDADA

Crear fase 1:

1. Estructura hexagonal.
2. Contratos base.
3. Excepción de dominio.
4. Respuesta API uniforme.
5. Migraciones base SaaS:
   * tenants
   * plans
   * subscriptions
   * branches
   * users
   * roles
   * permissions
6. Auth JWT preparado.

No crear todavía ventas ni caja hasta cerrar Auth + Tenant + Branch.

---

# REGLA CRÍTICA PARA BOLICHE

Un producto puede tener varios precios.

Ejemplo:

* Cerveza SOLO = 30 Bs.
* Cerveza CON_ACOMPANANTE = 60 Bs.

El frontend solo envía `price_type`.

El backend debe resolver el precio desde `product_prices`.

Nunca confiar en precio enviado por frontend.

---

# CASOS DE USO CLAVE A CREAR

```text
LoginUseCase
CreateTenantUseCase
CreateBranchUseCase
CreateProductUseCase
CreateProductPriceUseCase
ResolveProductPriceUseCase
OpenCashSessionUseCase
CreateOrderUseCase
AddOrderItemUseCase
ChargeOrderUseCase
CreateDirectSaleUseCase
CloseCashSessionUseCase
RegisterStockMovementUseCase
```

---

# REGLAS DE SEGURIDAD

Validar siempre:

* tenant activo
* suscripción vigente
* usuario autenticado
* permisos
* branch activa
* caja abierta para vender
* precio activo
* stock suficiente cuando aplique

---

# NO HACER

No hagas:

* CRUD genérico sin caso de uso.
* SQL directo en controller.
* lógica de precio en Vue.
* ventas sin transacción.
* stock sin kardex.
* caja sin cierre.
* endpoints fuera de `/api/v1`.

---

# FORMATO DE TRABAJO

Por cada fase entrega:

1. Archivos creados/modificados.
2. Migraciones.
3. Endpoints.
4. Tests.
5. Pendientes.
6. Siguiente fase recomendada.

---

# PRIMER MVP

El MVP debe permitir:

1. Login.
2. Crear sucursal.
3. Crear producto cerveza.
4. Crear precio SOLO y CON_ACOMPANANTE.
5. Abrir caja.
6. Abrir mesa.
7. Agregar cerveza con modalidad.
8. Cobrar.
9. Ver resumen de caja.

Cuando eso funcione, recién continuar con inventario avanzado y reportes.

---

# INSTRUCCIÓN ADICIONAL PARA CURSOR — IMPRESIÓN AUTOMÁTICA

Agregar módulo de impresión automática de comandas.

El sistema es online y estará en hosting. Por eso, el backend no puede conectarse directamente a la impresora física del boliche.

La solución obligatoria es:

```text
Backend API + Cola print_jobs + Print Agent local
```

Cuando un garzón registre una comanda desde su celular:

1. Validar caja abierta.
2. Guardar la comanda.
3. Guardar detalle de productos.
4. Aplicar precio según modalidad: SOLO o CON_ACOMPANANTE.
5. Actualizar inventario/kardex si corresponde.
6. Crear automáticamente uno o varios PrintJob.
7. Devolver respuesta al frontend.

No pedir al garzón ningún paso adicional para imprimir.

La comanda debe seguir apareciendo en el sistema como actualmente:

* pantalla de comandas,
* mesas,
* caja,
* reportes,
* historial,
* edición según permisos.

La impresión es una funcionalidad adicional desacoplada.

Crear bounded context:

```text
Printing
```

Crear tablas:

```text
printers
print_jobs
```

Crear endpoints:

```text
GET /api/v1/print-jobs/pending
POST /api/v1/print-jobs/{id}/printing
POST /api/v1/print-jobs/{id}/printed
POST /api/v1/print-jobs/{id}/failed
POST /api/v1/orders/{id}/reprint
```

Crear Print Agent local:

* Puede ser Node.js, Python o Electron.
* Debe correr en una PC del boliche.
* Debe conectarse a la API con token seguro.
* Debe consultar trabajos pendientes.
* Debe imprimir en la impresora local configurada.
* Debe marcar el trabajo como impreso o fallido.

Reglas técnicas:

* No meter lógica de impresora en controladores de ventas.
* No meter lógica de impresora en Vue.
* No conectar dominio con impresora física.
* Usar puertos, casos de uso y adaptadores.
* Toda consulta debe respetar `tenant_id` y `branch_id`.
* Una comanda puede generar varios PrintJob según destino: BAR, KITCHEN, CASHIER.


---

# INSTRUCCIÓN ADICIONAL PARA CURSOR: CIERRE DE CAJA Y LIQUIDACIÓN DE PERSONAL

Agregar al sistema las siguientes funcionalidades sin romper lo ya documentado:

1. Cierre de caja por turno separado por método de pago:

```text
efectivo
QR
tarjeta
```

2. Cada producto puede tener precio solo cliente y precio con chica.

Ejemplo:

```text
Paceña solo = 40 Bs
Paceña con chica = 80 Bs
Monto chica = 40 Bs

Huari solo = 50 Bs
Huari con chica = 100 Bs
Monto chica = 50 Bs
```

3. Cuando el garzón venda con modalidad `CON_CHICA`, debe seleccionar la chica. El sistema debe generar automáticamente el monto correspondiente a la chica como manilla.

4. Agregar servicios de pieza y show ligados a una chica.

5. Agregar porcentaje variable por garzón. Un garzón puede ganar 5%, otro 6%, otro 4%, etc.

6. Guardar snapshot completo al momento de vender:

```text
producto
precio
modalidad
chica asignada
monto chica
garzón
porcentaje garzón
monto comisión garzón
método de pago
turno
sucursal
tenant
```

7. Al cerrar caja, la cajera debe ver:

```text
Total efectivo
Total QR
Total tarjeta
Total ventas
Total a pagar a chicas
Total a pagar a garzones
Total manillas
Total piezas
Total shows
Efectivo esperado
Diferencia de caja
```

8. Los pagos a chicas y garzones deben registrarse como movimientos de caja y quedar auditados.

9. No implementar esta lógica en Vue. Debe estar en backend, usando casos de uso, entidades de dominio, repositorios y servicios bajo arquitectura hexagonal.

10. Crear tests obligatorios para venta con chica, venta solo, comisión variable de garzón y cierre por método de pago.
