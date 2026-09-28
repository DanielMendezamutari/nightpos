# RiberResto POS — App Móvil Garzón (Nativo Android Kotlin)

**Ecosistema**: Ribersoft  
**Módulo SDD**: `specs/015-app-movil-garzon-kotlin/`  
**Stack**: Kotlin 2.0, Jetpack Compose, Material 3, Retrofit 2, Coroutines, AndroidX DataStore  
**Compatibilidad**: Android 7.0 (API 24) a Android 15 (API 35)

---

## 1. Descripción
Esta aplicación móvil nativa reemplaza por completo el APK heredado `base.apk` (`cu.lex.android.bi`), modernizando la toma inalámbrica de pedidos para garzones y saloneros en restaurantes, bares y boliches.

### Ventajas sobre la versión legacy:
1. **100% Kotlin + Jetpack Compose**: Interfaz moderna, reactiva, fluida y con tema oscuro optimizado para ambientes nocturnos.
2. **Local-First & Cero Internet**: Opera conectándose directamente a la IP local de la PC de Caja (ej: `http://192.168.0.10:8000/api/v1/`).
3. **Impresión Térmica RAW Integrada**:
   - Al tocar **"Enviar Pedido a Cocina/Barra"**, el backend imprime automáticamente los tickets de cocina y barra con corte de papel.
   - Al tocar **"Precuenta"**, el backend emite el ticket físico en la impresora `CAJA`.
4. **Respuesta Háptica**: Cada pulsación del PIN numérico emite vibración táctil confirmatoria.

---

## 2. Flujos Implementados

| Pantalla | Descripción |
|---|---|
| `SplashScreen` | Comprobación visual de inicialización con animación de pulso. |
| `SettingsScreen` | Permite ingresar la IP y puerto de la PC de Caja con test de conexión en vivo. |
| `LoginPinScreen` | Teclado numérico táctil de 4 dígitos para autenticación rápida del mesero. |
| `TableListScreen` | Grilla de mesas con filtro en pestañas: *Todas*, *Libres* (verde) y *Ocupadas/Precuenta* (naranja/violeta). |
| `MenuScreen` | Catálogo de productos por categorías con buscador y selector de modificadores. |
| `ModifierDialog` | Diálogo para seleccionar cantidad (+/-), modificadores (*Sin Sal*, *Término Medio*, etc.) y notas de cocina. |
| `CartScreen` | Resumen de comanda de la mesa, cálculo de total en Bs., botón de despacho y consulta de cuenta activa. |

---

## 3. Instrucciones de Compilación y Generación del APK

### En Android Studio:
1. Abrir Android Studio (Ladybug / Koala o superior).
2. Seleccionar **File -> Open...** y elegir la carpeta `c:\xampp\htdocs\nightpos\mobile-garzon`.
3. Esperar que Gradle sincronice las dependencias.
4. Conectar un celular Android vía USB con depuración habilitada o usar un emulador.
5. Presionar **Run 'app' (Shift + F10)** o ir a **Build -> Build Bundle(s) / APK(s) -> Build APK(s)** para generar `app-debug.apk`.

### Vía Terminal (Línea de Comandos):
```bash
cd mobile-garzon
./gradlew assembleDebug
```
El archivo APK generado se ubicará en:
`mobile-garzon/app/build/outputs/apk/debug/app-debug.apk`

---

## 4. Configuración en el Restaurante
1. Conectar el celular del garzón a la misma red Wi-Fi del restaurante.
2. Abrir la app y pulsar el ícono de engranaje (Ajustes) en la pantalla de login.
3. Ingresar la IP local de la computadora donde corre RiberResto POS (ej: `192.168.0.10:8000`).
4. Pulsar **"Probar Conexión"** y luego **"Guardar y Continuar"**.
5. Ingresar el PIN del garzón (ej: `5678`) y comenzar a comandar.
