# FRONTEND_GUIDELINES.md

# OBJETIVO

Definir las reglas oficiales para desarrollar el frontend del sistema CONSTRUPLUS SaaS.

El frontend debe construirse sobre Vue 3 y la plantilla Materialize Admin Template, respetando la funcionalidad real de restaurant_bolivia-1

---

# STACK

* Vue 3
* Composition API
* Pinia
* Vue Router
* Axios
* Plantilla admin responsiva
* DataTables o tablas propias
* Componentes reutilizables

---

# REGLA PRINCIPAL

El frontend NO debe desarrollarse antes de validar restaurant_bolivia-1.

Antes de crear cualquier pantalla se debe:

1. Analizar el módulo en backend.
2. Revisar endpoints.
3. Revisar validaciones.
4. Revisar permisos.
5. Revisar reglas de negocio.
6. Revisar tests.
7. Diseñar la interfaz.
8. Implementar el frontend.

El frontend consume `/backend/v1`.

---

# PANTALLAS PRIORITARIAS

## 1. Login

* Usuario/contraseña.
* PIN rápido para caja/garzón.
* Guardar token seguro.

## 2. Dashboard

* Ventas del día.
* Caja abierta.
* Productos más vendidos.
* Alertas de stock.
* Suscripción venciendo.

## 3. Mesas / Ambientes

Vista visual:

* Disponible.
* Ocupada.
* Reservada.
* Cuenta pendiente.

## 4. Comanda móvil

Pantalla para garzón:

* Elegir mesa.
* Buscar producto.
* Elegir modalidad: SOLO / CON ACOMPAÑANTE.
* Agregar notas.
* Enviar a barra.

## 5. Caja

Pantalla para cajero:

* Abrir caja.
* Cobrar mesa.
* Venta directa.
* Registrar ingreso/egreso.
* Cerrar caja.

## 6. Productos

* Bebidas.
* Servicios.
* Combos.
* Promociones.
* Precios por modalidad.

## 7. Inventario

* Stock actual.
* Kardex.
* Compras.
* Traspasos.

## 8. Reportes

* Por fecha.
* Por caja.
* Por garzón.
* Por casa.
* Por producto.

---

# COMPONENTES REUTILIZABLES

```text
PriceTypeSelector.vue
ProductSearchModal.vue
OrderTableCard.vue
CashSessionStatus.vue
PaymentMethodsForm.vue
BranchSelector.vue
TenantSubscriptionAlert.vue
StockBadge.vue
```

---

# REGLA DE PRECIOS

El frontend puede mostrar selector:

* Solo
* Con acompañante

Pero nunca debe calcular el precio final como fuente de verdad.

El backend debe devolver:

* precio unitario
* subtotal
* total
* descuentos

---

# PINIA STORES

```text
useAuthStore
useTenantStore
useBranchStore
useProductStore
useOrderStore
useCashStore
useInventoryStore
useReportStore
```

---

# RUTAS SUGERIDAS

```text
/login
/dashboard
/mesas
/comandas
/caja
/productos
/productos/:id/precios
/inventario
/compras
/traspasos
/reportes
/configuracion/usuarios
/configuracion/sucursales
```

---

# EXPERIENCIA MÓVIL

Prioridad alta para:

* garzones desde celular
* encargados revisando ventas
* caja desde tablet/laptop

Botones grandes, búsqueda rápida y pocas pantallas.

---

# PROHIBIDO

* Duplicar reglas del backend.
* Quemar precios.
* Usar datos falsos cuando API ya existe.
* Crear pantallas sin permisos.
* Permitir cobrar sin caja abierta.
No crear pantallas basadas en suposiciones.

No inventar procesos de negocio.

No crear CRUDs genéricos si el backend tiene reglas específicas.

No duplicar componentes existentes.

No eliminar componentes de la plantilla Materialize sin justificación.

---

# MATERIALIZE TEMPLATE

La plantilla Materialize será la base visual del sistema.

Cursor debe reutilizar:

* Layout principal
* Sidebar
* Navbar
* Cards
* Forms
* Tables
* Modals
* Charts
* Components
* Helpers

---

# COMPONENTES NO UTILIZADOS

La plantilla puede traer muchas funcionalidades que no serán utilizadas inicialmente.

Reglas:

* No eliminar.
* No romper.
* No modificar innecesariamente.
* Ocultar mientras no sean necesarias.
* Mantener disponibles para futuras versiones.


---

# PANTALLAS NUEVAS PARA CAJERA Y CIERRE

## Cierre de turno

Pantalla para cajera con tarjetas separadas:

```text
Efectivo
QR
Tarjeta
Total vendido
Gastos
Pagos a chicas
Pagos a garzones
Efectivo esperado
Efectivo contado
Diferencia
```

Debe permitir imprimir o exportar el cierre.

## Liquidación de chicas

Tabla por chica:

```text
Chica
Manillas
Piezas
Shows
Bonos
Descuentos
Total a pagar
Estado
Botón pagar
```

Al abrir una chica, mostrar detalle:

```text
Producto / servicio
Cantidad
Monto chica
Garzón
Hora
Método de pago
```

## Liquidación de garzones

Tabla por garzón:

```text
Garzón
Total vendido
Porcentaje
Comisión
Descuentos
Total a pagar
Estado
Botón pagar
```

## Comanda desde celular

Cuando el garzón seleccione un producto, el frontend debe permitir:

```text
Modalidad: Solo / Con chica
Chica: selector obligatorio si es Con chica
```

Ejemplo:

```text
Paceña
Solo: 40 Bs
Con chica: 80 Bs
Chica: María
```

El garzón no debe calcular nada manualmente. El backend devuelve precios y montos configurados.

---

# ESTRUCTURA RECOMENDADA

src/

```
modules/

    auth/
    dashboard/
    users/
    roles/
    clients/
    projects/
    construction-sites/
    contracts/
    budgets/
    inventory/
    purchases/
    suppliers/
    employees/
    reports/
    subscriptions/

shared/

    components/
    layouts/
    services/
    composables/
    helpers/
    constants/

router/

stores/

assets/
```

---

# MÓDULOS

Cada módulo debe tener su propia estructura.

Ejemplo:

modules/clients/

```
pages/
components/
services/
stores/
routes.js
```

---

# PAGES

Contienen pantallas completas.

Ejemplo:

* ClientListPage.vue
* ClientCreatePage.vue
* ClientEditPage.vue
* ClientShowPage.vue

---

# COMPONENTS

Contienen partes reutilizables.

Ejemplo:

* ClientForm.vue
* ClientTable.vue
* ClientFilters.vue

---

# SERVICES

Contienen llamadas API.

Ejemplo:

clientService.js

---

# STORES

Contienen estado de módulo usando Pinia.

Ejemplo:

clientStore.js

---

# ROUTES

Cada módulo puede tener sus rutas propias.

---

# AXIOS

Debe existir una instancia central.

Responsabilidades:

* Base URL.
* Token JWT.
* Interceptores.
* Manejo de errores.
* Redirección por token vencido.

---

# INTERCEPTOR JWT

Si la API devuelve:

* 401
* 403
* Token Expired
* Token Invalid

Entonces el frontend debe:

1. Limpiar LocalStorage.
2. Limpiar SessionStorage.
3. Limpiar stores de Pinia.
4. Limpiar usuario autenticado.
5. Redirigir al Login.

---

# PINIA

Pinia debe manejar:

* Usuario autenticado.
* Token JWT.
* Tenant actual.
* Permisos.
* Módulos habilitados.
* Configuración del sistema.

---

# VUE ROUTER

El router debe validar:

* Autenticación.
* Permisos.
* Tenant activo.
* Suscripción activa.

---

# RUTAS PROTEGIDAS

Una ruta protegida no debe abrirse si:

* No existe token.
* El token expiró.
* El usuario no tiene permiso.
* La suscripción está vencida.
* El módulo no está habilitado.

---

# LOGIN

El login debe soportar dos métodos.

## Usuario y contraseña

Formulario con:

* Usuario o email.
* Contraseña.

---

## Código PIN

Formulario con:

* PIN de 4 dígitos.

---

# MENÚ DINÁMICO

El menú debe generarse según:

* Rol.
* Permisos.
* Módulos habilitados.
* Plan contratado.
* Estado de suscripción.

---

# BOTONES Y ACCIONES

Los botones deben mostrarse según permisos.

Ejemplo:

* Crear.
* Editar.
* Eliminar.
* Exportar.
* Ver detalle.

Si el usuario no tiene permiso, el botón no debe mostrarse.

---

# DATATABLES

Los listados principales deben utilizar DataTables o tablas avanzadas.

Debe soportar:

* Paginación.
* Búsqueda.
* Ordenamiento.
* Filtros.
* Acciones por fila.
* Exportación cuando corresponda.

---

# SERVER SIDE PAGINATION

Para tablas grandes usar paginación desde backend.

No cargar miles de registros en frontend.

---

# FORMULARIOS

Los formularios deben usar componentes adecuados de Materialize.

Usar:

* Inputs.
* Selects.
* Date pickers.
* File upload.
* Switches.
* Checkboxes.
* Modals.

---

# VALIDACIONES

El frontend debe mostrar errores recibidos del backend.

No ocultar errores de validación.

Formato esperado:

{
"success": false,
"message": "Validation Error",
"errors": {}
}

---

# DASHBOARD

El Dashboard debe usar:

* Cards.
* KPIs.
* Charts.
* Indicadores.
* Resúmenes.

No mostrar tarjetas vacías.
