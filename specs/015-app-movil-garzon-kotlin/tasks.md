# Checklist de Tareas: Módulo 015 — App Móvil Garzón Nativa Kotlin (RiberResto POS)

**Módulo**: `015-app-movil-garzon-kotlin`  
**Estado**: Completado  
**Enfoque**: Spec-Driven Development (SDD)

---

## Tareas de Especificación y Reversión
- [x] **T-1**: Descompilar e inspeccionar `base.apk` para extraer clases, actividades, DTOs y contratos HTTP originales.
- [x] **T-2**: Crear especificación funcional `spec.md` con historias de usuario Gherkin y contratos de endpoints.
- [x] **T-3**: Diseñar el plan arquitectónico `plan.md` con Jetpack Compose, Material 3, Clean MVVM y soporte offline Wi-Fi.

---

## Tareas de Backend y Retrocompatibilidad (Laravel 12)
- [x] **T-4**: Actualizar `MovilGarzonController.php` y rutas en `backend/routes/api.php` para responder a los contratos de endpoints (tanto RESTful `/api/v1/movil/*` como alias reversados `/users/login/*`, `/table/allTable`, `/orders/*`, `/Assistants/All`).
- [x] **T-5**: Integrar el despacho de comandas móviles con `ComandaPrintService` (impresión RAW directa a Cocina y Barra) e impresión de precuenta a Caja.
- [x] **T-6**: Crear y ejecutar pruebas automatizadas en backend con `php artisan test` para validar el 100% de los endpoints móviles (75 tests pasando, 567 aserciones).

---

## Tareas de la App Móvil Android Nativa (Kotlin + Compose)
- [x] **T-7**: Andamiaje del proyecto Android Nativo en `mobile-garzon/` con Gradle Kotlin DSL (`build.gradle.kts`), configuración de Compose, Material 3, y permisos de red (`AndroidManifest.xml`).
- [x] **T-8**: Implementar tema visual corporativo Ribersoft (`Theme.kt`, `Color.kt`, `Type.kt`) con Dark Mode elegante para boliches y restaurantes.
- [x] **T-9**: Implementar modelos de datos y DTOs Kotlin (`Table`, `Category`, `Product`, `Assistant`, `CartItem`, etc.).
- [x] **T-10**: Implementar capa de red Retrofit + OkHttp (`RiberRestoApiService`, `NetworkClient`) y gestión de IP del servidor con DataStore (`PreferencesManager`).
- [x] **T-11**: Implementar pantalla de Ajustes de Servidor (`SettingsScreen`) para configurar la IP del servidor local con botón de "Probar Conexión".
- [x] **T-12**: Implementar pantalla de Splash e inicio con verificación de servidor (`SplashScreen`).
- [x] **T-13**: Implementar pantalla de Login por PIN (`LoginPinScreen`) con teclado táctil 0-9 animado (`PinKeypad`) y vibración.
- [x] **T-14**: Implementar pantalla de Selección de Mesas (`TableListScreen`) con tabs Libres/Ocupadas/Todas y tarjeta de estado visual.
- [x] **T-15**: Implementar pantalla de Catálogo y Menú (`MenuScreen`) con selector de categorías, buscador y tarjetas de productos.
- [x] **T-16**: Implementar diálogo de Modificadores y Comentarios de Cocina (`ModifierDialog`) para selección de asistentes y notas especiales.
- [x] **T-17**: Implementar pantalla de Carrito y Comanda (`CartScreen`) con resumen, cálculo de subtotal, botón "Enviar a Cocina/Barra" y botón "Pedir Precuenta".
- [x] **T-18**: Configurar navegación centralizada (`AppNavigation.kt`) y punto de entrada `MainActivity.kt`.

---

## Tareas de Verificación y Cierre
- [x] **T-19**: Verificar compilación y consistencia del código Kotlin en `mobile-garzon/`.
- [x] **T-20**: Probar flujo completo de punta a punta (Login PIN -> Mesa -> Pedido -> Impresión Cocina/Barra/Precuenta).
- [x] **T-21**: Actualizar documentación final del módulo y marcar tareas completadas.
