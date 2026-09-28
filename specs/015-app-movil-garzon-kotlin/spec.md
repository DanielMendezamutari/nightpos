# Especificación Funcional: Módulo 015 — App Móvil Garzón Nativa Kotlin (RiberResto POS)

**Módulo**: `015-app-movil-garzon-kotlin`  
**Estado**: Especificado  
**Enfoque**: Spec-Driven Development (SDD) & Reverse Engineering de `base.apk`  
**Fuente de Verdad**: `base.apk` (Paquete original `cu.lex.android.bi`, RestoTech Mobile).  
**Destino de Implementación**: App Nativa Android en **Kotlin + Jetpack Compose + Material 3** (`com.ribersoft.riberresto.garzon`).

---

## 1. Declaración del Problema & Objetivos de Negocio

### Problema:
1. En restaurantes y boliches con alto flujo, los garzones necesitan tomar pedidos al pie de la mesa sin tener que desplazarse a la PC de caja.
2. El APK heredado (`base.apk`, versión `cu.lex.android.bi`) data de Android 4.x (Ice Cream Sandwich / Jelly Bean), usa librerías obsoletas (*ActionBarSherlock*, *Spring Android RestTemplate v1*), tiene la marca antigua ("RestoTech") y no es compatible con versiones modernas de Android (Android 10 a 15).
3. Es crítico modernizar la aplicación a código **Kotlin Nativo con Jetpack Compose y Material 3**, optimizado para la marca oficial **Ribersoft / RiberResto POS**, manteniendo la misma agilidad operativa que el personal ya conoce.

### Objetivos:
- **O-1 (Paridad de Flujos)**: Replicar con exactitud los 6 flujos de la APK reversada:
  1. Login rápido por PIN de 4 dígitos con teclado en pantalla.
  2. Visualización y conmutación de mesas (Libres / Ocupadas / Mi Salón).
  3. Catálogo de productos por categoría con cantidades rápidas.
  4. Selector de acompañamientos/modificadores (*Assistants*) y notas de cocina (*Comment*).
  5. Envío de comanda en tiempo real con disparo automático de tickets térmicos en Cocina/Barra.
  6. Consulta de comanda actual (*VerOrder*) y solicitud de precuenta a caja (*PrintOrder*).
- **O-2 (Local-First y Offline Wi-Fi)**: Operación directa contra la IP del servidor POS en la red Wi-Fi local (ej: `http://192.168.1.100:8000`), sin requerir internet externo.
- **O-3 (UX Moderna & Ergonómica)**: Interfaz táctil reactiva desarrollada en Jetpack Compose, con modo oscuro elegante para boliches y discotecas, respuesta háptica y animaciones fluidas.
- **O-4 (Retrocompatibilidad de API)**: Soporte completo en el backend Laravel 12 para los contratos de endpoints identificados en la APK.

---

## 2. Reversión Arquitectónica de `base.apk`

El análisis estático de los binarios DEX y recursos XML de `base.apk` arrojó la siguiente estructura:

### Actividades y Componentes Reversados:
| Componente Original (`cu.lex.android.bi`) | Propósito Reversado | Componente RiberResto POS (Kotlin) |
|---|---|---|
| `ui.activity.SplahActivity` | Pantalla de inicio y comprobación de servidor | `SplashScreen` |
| `ui.activity.SettingsActivity` (`KEY_BASE_URI_PREF`) | Configuración de IP y Puerto del servidor local | `SettingsScreen` + `ServerConfigDataStore` |
| `ui.activity.LoginActivity` + `PasswordView` | Teclado numérico táctil y validación de PIN | `LoginPinScreen` + `PinKeypad` |
| `ui.activity.ChooseTableActivity` + `TableListFragment` | Mapa/grilla de mesas con filtro Libres/Ocupadas | `TableListScreen` + `TableStateTabs` |
| `ui.activity.CategoryListActivity` + `CategoryListFragment` | Listado horizontal/vertical de categorías | `MenuScreen` (Tabs de categorías) |
| `ui.activity.ProductListActivity` + `ProductListFragment` | Grilla de productos con precio y stock | `MenuScreen` (ProductGrid) |
| `ui.activity.ProductDetailsActivity` + `ProductDetailsFragment` | Cantidad (+/-), notas y modificadores | `ProductModifierDialog` |
| `ui.activity.CartListActivity` + `CartListFragment` | Carrito de pedido, enviar comanda, precuenta | `CartOrderScreen` |

### DTOs Reversados:
- **`LoginResponse`**: `{ loginSuccessful: boolean, existFreeTables: boolean }`
- **`Table`**: `{ id: long, name: string }`
- **`Category`**: `{ mId: long, mName: string }`
- **`Product`**: `{ mId: long, mName: string }`
- **`Assistant`**: `{ mId: long, mName: string, mFullName: string }`
- **`CartItem`**: `{ mId: long, mProduct: Product, mCount: int, mComment: string, mAssistants: string }`
- **`Order`**: `{ productId: long, productName: string, count: int }`
- **`OrderResquest`**: `{ tableId: long, waiterId: string, cartItemList: List<CartItem> }`
- **`PrintRequest`**: `{ tableId: long, waiterId: string }`
- **`VerRequest`**: `{ tableId: long, waiterId: string }`

---

## 3. Historias de Usuario & Criterios de Aceptación (Gherkin)

### Historia 1: Inicio de Sesión Rápido por PIN (P1)
**Como** garzón del restaurante,  
**Quiero** ingresar con mi PIN numérico de 4 dígitos en el teclado táctil,  
**Para** autenticarme en menos de 2 segundos sin escribir correos ni contraseñas complejas.

- **Escenario 1.1: PIN correcto**
  - **Given** el garzón tiene el PIN asignado `1234` y el servidor POS está disponible en la red local,
  - **When** presiona secuencialmente `1`, `2`, `3`, `4`,
  - **Then** la app envía `GET /users/login/1234` (o `POST /api/v1/movil/login`),
  - **And** recibe `loginSuccessful: true`,
  - **And** navega inmediatamente a la pantalla de Mesas con feedback háptico.

- **Escenario 1.2: PIN incorrecto**
  - **Given** el garzón ingresa un PIN erróneo `9999`,
  - **When** se completa el 4to dígito,
  - **Then** la app muestra una alerta roja "PIN Incorrecto", limpia los dígitos y emite vibración de error.

---

### Historia 2: Exploración y Apertura de Mesas (P1)
**Como** garzón,  
**Quiero** visualizar las mesas clasificadas en Libres y Ocupadas,  
**Para** saber dónde sentar nuevos comensales o agregar ítems a una mesa abierta.

- **Escenario 2.1: Filtrado de mesas**
  - **Given** que existen 10 mesas en el restaurante (4 ocupadas y 6 libres),
  - **When** el garzón selecciona la pestaña "Libres",
  - **Then** la grilla muestra únicamente las 6 mesas disponibles en color verde/azul.
  - **When** selecciona "Ocupadas",
  - **Then** se muestran las 4 mesas ocupadas en color naranja/ámbar con indicador de tiempo.

- **Escenario 2.2: Apertura de mesa libre**
  - **Given** la Mesa 5 está libre,
  - **When** el garzón toca la Mesa 5,
  - **Then** se inicializa un nuevo pedido y se abre la pantalla del Menú para comandar.

---

### Historia 3: Selección de Productos, Modificadores y Notas de Cocina (P1)
**Como** garzón,  
**Quiero** seleccionar productos, ajustar cantidades y marcar notas especiales (ej: "Sin sal", "Término medio"),  
**Para** que la cocina prepare el pedido tal como lo pidió el cliente.

- **Escenario 3.1: Configuración de producto en comanda**
  - **Given** el garzón selecciona "Pique Macho",
  - **When** se abre el diálogo de detalle y selecciona el modificador "Picante Extra" y escribe "Sin cebolla",
  - **And** presiona "Agregar (2)",
  - **Then** el carrito registra `Pique Macho x2` con asistentes `[Picante Extra]` y comentario `Sin cebolla`.

---

### Historia 4: Despacho de Comanda a Cocina y Barra (P1)
**Como** garzón,  
**Quiero** presionar "Enviar Pedido" desde el carrito,  
**Para** que las impresoras de Cocina y Barra impriman los tickets de producción al instante.

- **Escenario 4.1: Envío exitoso de comanda**
  - **Given** el carrito contiene 1 comida y 2 bebidas para la Mesa 3,
  - **When** el garzón presiona "Enviar Pedido",
  - **Then** la app envía el payload `OrderResquest` al servidor local,
  - **And** el backend imprime el ticket térmico en Cocina y en Barra vía Spooler RAW Win32,
  - **And** la mesa cambia de estado a Ocupada en la app móvil y en la pantalla táctil de caja,
  - **And** el carrito se vacía regresando a la lista de mesas con mensaje "Comanda enviada con éxito".

---

### Historia 5: Solicitud de Precuenta desde el Móvil (P2)
**Como** garzón al finalizar el servicio en una mesa,  
**Quiero** presionar el botón "Imprimir Precuenta",  
**Para** que la impresora física de Caja imprima el detalle de la cuenta para entregárselo al cliente.

- **Escenario 5.1: Emisión de precuenta**
  - **Given** la Mesa 3 está ocupada con consumo activo,
  - **When** el garzón ingresa al detalle de la Mesa 3 y toca "Precuenta",
  - **Then** la app envía `PrintRequest` con el `tableId: 3`,
  - **And** la impresora física `CAJA` imprime el ticket de precuenta con formato térmico de 42 columnas y corte automático.

---

### Historia 6: Conexión de Red Local (Local IP Settings) (P1)
**Como** administrador o garzón,  
**Quiero** ingresar la dirección IP de la PC de Caja (ej: `192.168.0.10:8000`),  
**Para** que la app se comunique por Wi-Fi directo con la base de datos local sin depender de internet.

- **Escenario 6.1: Configuración inicial de IP**
  - **Given** la app recién instalada,
  - **When** el usuario abre la pantalla de Ajustes e ingresa `192.168.0.10:8000` y pulsa "Probar Conexión",
  - **Then** la app realiza un ping HTTP a `/api/v1/movil/ping` (o `/table/allTable`),
  - **And** si responde satisfactoriamente, almacena la URL en DataStore y permite iniciar sesión.

---

## 4. Contratos de API (Compatibilidad RestoTech + Laravel POS)

El backend de RiberResto POS expondrá tanto las rutas modernas (`/api/v1/movil/*`) como los alias heredados de `base.apk` para garantizar compatibilidad universal:

### A. Autenticación Garzón
- **Método**: `GET /users/login/{query}` y `POST /api/v1/movil/login`
- **Request**: `query` = PIN (ej: `1234`) o `{ "pin": "1234" }`
- **Response 200 OK**:
```json
{
  "loginSuccessful": true,
  "existFreeTables": true,
  "waiterId": "2",
  "waiterName": "Carlos Garzón",
  "token": "sanctum_or_jwt_token_here"
}
```

### B. Listado de Mesas
- **Método**: `POST /table/allTable` y `GET /api/v1/movil/mesas`
- **Response 200 OK**:
```json
[
  { "id": 1, "name": "Mesa 1", "estado": "libre", "salon": "Planta Baja" },
  { "id": 2, "name": "Mesa 2", "estado": "ocupada", "salon": "Planta Baja" }
]
```

### C. Catálogo de Menú y Modificadores
- **Método**: `POST /products/GetProducts` y `GET /api/v1/movil/menu`
- **Response 200 OK**:
```json
{
  "categories": [
    { "id": 1, "name": "Platos Fuertes" },
    { "id": 2, "name": "Bebidas & Tragos" }
  ],
  "products": [
    { "id": 101, "categoryId": 1, "name": "Pique Macho", "price": 65.0, "hasAssistants": true },
    { "id": 102, "categoryId": 2, "name": "Fernet Branca", "price": 35.0, "hasAssistants": false }
  ]
}
```

- **Método**: `GET /Assistants/All` y `GET /api/v1/movil/asistentes`
- **Response 200 OK**:
```json
[
  { "id": 1, "name": "Sin Cebolla", "fullName": "Sin Cebolla" },
  { "id": 2, "name": "Con Hielo", "fullName": "Con Bastante Hielo" },
  { "id": 3, "name": "Término Medio", "fullName": "Carne Término Medio" }
]
```

### D. Enviar Comanda a Cocina/Barra
- **Método**: `POST /orders/SendOrder` y `POST /api/v1/movil/enviar-comanda`
- **Request Body (`OrderResquest`)**:
```json
{
  "tableId": 2,
  "waiterId": "2",
  "cartItemList": [
    {
      "productId": 101,
      "productName": "Pique Macho",
      "count": 1,
      "comment": "Bien cocido",
      "assistants": "Sin Cebolla"
    },
    {
      "productId": 102,
      "productName": "Fernet Branca",
      "count": 2,
      "comment": "Vaso largo",
      "assistants": "Con Hielo"
    }
  ]
}
```
- **Response 200 OK**:
```json
{
  "success": true,
  "message": "Comanda despachada correctamente",
  "orderId": 55,
  "printedKitchen": true,
  "printedBar": true
}
```

### E. Imprimir Precuenta
- **Método**: `POST /orders/PrintOrder` y `POST /api/v1/movil/imprimir-precuenta/{mesaId}`
- **Request Body (`PrintRequest`)**:
```json
{
  "tableId": 2,
  "waiterId": "2"
}
```
- **Response 200 OK**:
```json
{
  "success": true,
  "message": "Precuenta enviada a impresora CAJA",
  "mesa": "Mesa 2",
  "total": 135.00
}
```
