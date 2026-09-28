# Plan de Implementación: Módulo 015 — App Móvil Garzón Nativa Kotlin (RiberResto POS)

**Módulo**: `015-app-movil-garzon-kotlin`  
**Estado**: Planificado  
**Enfoque**: Spec-Driven Development (SDD)  
**Proyecto**: `mobile-garzon/` (Android Nativo en Kotlin)

---

## 1. Arquitectura del Sistema Móvil

### 1.1 Stack Tecnológico
- **Lenguaje**: Kotlin 2.0+
- **Framework UI**: Jetpack Compose + Material Design 3 (M3)
- **Navegación**: Jetpack Navigation Compose con transiciones suaves
- **Arquitectura**: MVVM (Model-View-ViewModel) + Unidirectional Data Flow (UDF) con `StateFlow`
- **Networking**: Retrofit 2 + OkHttp 4 + Kotlinx Serialization JSON
- **Almacenamiento Local**: Jetpack DataStore Preferences (configuración de servidor local, sesión activa del garzón)
- **Asincronía**: Kotlin Coroutines + Flow
- **Branding**: RiberResto POS (Ribersoft) con tema oscuro nativo y contrastes de alta legibilidad para ambientes nocturnos.

### 1.2 Diagrama de Arquitectura de Capas
```
┌────────────────────────────────────────────────────────┐
│                    CAPA DE UI (COMPOSE)                │
│  SplashScreen  │  LoginPinScreen  │  TableListScreen    │
│  MenuOrderScreen │ ModifierDialog │  CartOrderScreen   │
│  SettingsScreen                                        │
└───────────────────────────▲────────────────────────────┘
                            │ (StateFlow / UI Events)
┌───────────────────────────┴────────────────────────────┐
│                  CAPA DE VIEWMODELS                     │
│  AuthViewModel  │  TableViewModel  │  OrderViewModel   │
│  SettingsViewModel                                     │
└───────────────────────────▲────────────────────────────┘
                            │
┌───────────────────────────┴────────────────────────────┐
│                  CAPA DE REPOSITORIO                    │
│  AuthRepository │ TableRepository │ OrderRepository    │
│  SettingsRepository                                    │
└─────────────▲──────────────────────────────▲───────────┘
              │                              │
┌─────────────┴─────────────┐  ┌─────────────┴───────────┐
│     FUENTE DE DATOS RED   │  │   ALMACENAMIENTO LOCAL  │
│  RiberRestoApiClient      │  │   DataStore Preferences │
│  (Retrofit + OkHttp)      │  │   (Server IP, PIN, User)│
└─────────────▲─────────────┘  └─────────────────────────┘
              │ (Wi-Fi Local / HTTP)
┌─────────────┴──────────────────────────────────────────┐
│      BACKEND LOCAL LARAVEL 12 (PC CAJA / SERVIDOR)     │
│      RiberResto POS API (192.168.x.x:8000)             │
│      -> Impresoras Físicas CAJA, COCINA, BARRA         │
└────────────────────────────────────────────────────────┘
```

---

## 2. Estructura de Directorios del Proyecto Android (`mobile-garzon/`)

```
mobile-garzon/
├── app/
│   ├── build.gradle.kts
│   └── src/
│       ├── main/
│       │   ├── AndroidManifest.xml
│       │   ├── java/com/ribersoft/riberresto/garzon/
│       │   │   ├── MainActivity.kt
│       │   │   ├── RiberRestoApp.kt
│       │   │   ├── data/
│       │   │   │   ├── api/
│       │   │   │   │   ├── RiberRestoApiService.kt
│       │   │   │   │   └── NetworkClient.kt
│       │   │   │   ├── local/
│       │   │   │   │   └── PreferencesManager.kt
│       │   │   │   ├── model/
│       │   │   │   │   ├── Table.kt
│       │   │   │   │   ├── Category.kt
│       │   │   │   │   ├── Product.kt
│       │   │   │   │   ├── Assistant.kt
│       │   │   │   │   ├── CartItem.kt
│       │   │   │   │   └── RequestsAndResponses.kt
│       │   │   │   └── repository/
│       │   │   │       ├── AuthRepository.kt
│       │   │   │       ├── TableRepository.kt
│       │   │   │       └── OrderRepository.kt
│       │   │   ├── ui/
│       │   │   │   ├── theme/
│       │   │   │   │   ├── Color.kt
│       │   │   │   │   ├── Theme.kt
│       │   │   │   │   └── Type.kt
│       │   │   │   ├── components/
│       │   │   │   │   ├── PinKeypad.kt
│       │   │   │   │   ├── TableCard.kt
│       │   │   │   │   ├── ProductCard.kt
│       │   │   │   │   └── ModifierDialog.kt
│       │   │   │   ├── navigation/
│       │   │   │   │   └── AppNavigation.kt
│       │   │   │   └── screens/
│       │   │   │       ├── splash/
│       │   │   │       ├── login/
│       │   │   │       ├── tables/
│       │   │   │       ├── menu/
│       │   │   │       ├── cart/
│       │   │   │       └── settings/
│       │   └── res/
│       │       ├── values/
│       │       │   ├── strings.xml
│       │       │   └── themes.xml
│       │       └── mipmap-*/
├── build.gradle.kts
├── settings.gradle.kts
└── gradle/wrapper/
```

---

## 3. Estrategia de Retrocompatibilidad y Backend Laravel

Para que la nueva app nativa y cualquier dispositivo legacy operen sin contratiempos:
1. En `backend/routes/api.php`, unificar rutas:
   - `/api/v1/movil/*` (API Moderna RESTful)
   - `/users/login/{query}` -> `MovilGarzonController@loginLegacy`
   - `/table/allTable` -> `MovilGarzonController@mesasLegacy`
   - `/products/GetProducts` -> `MovilGarzonController@menuLegacy`
   - `/Assistants/All` -> `MovilGarzonController@asistentesLegacy`
   - `/orders/SendOrder` -> `MovilGarzonController@enviarComandaLegacy`
   - `/orders/PrintOrder` -> `MovilGarzonController@imprimirPrecuentaLegacy`
   - `/orders/OrderByTable` -> `MovilGarzonController@comandaActivaMesaLegacy`
2. El controlador `MovilGarzonController` invocará directamente los servicios:
   - `ComandaPrintService`: Enrutamiento y emisión física de tickets a `COCINA` y `BARRA`.
   - `WindowsDirectPrinterService`: Emisión física de precuenta a `CAJA`.
   - `VisitaRepository`: Registro exacto de la orden en la base de datos local SQLite/MySQL.

---

## 4. Criterios de Rendimiento y UX Offline
- **Timeout HTTP**: Configurado en 3.5 segundos con reintento rápido para evitar bloqueos si el garzón camina a una zona con señal Wi-Fi débil.
- **Feedback Táctil**: Cada tecla del teclado numérico PIN emite respuesta háptica (`HapticFeedbackType.TextHandleMove`).
- **Estados Visuales**:
  - Mesas Libres: Verde Esmeralda (`#10B981`)
  - Mesas Ocupadas: Ámbar / Naranja (`#F59E0B`)
  - Mesas con Precuenta: Azul Violeta (`#6366F1`)
- **Badge Flotante de Carrito**: Contador dinámico de ítems y subtotal en bolivianos (Bs.) siempre visible.
