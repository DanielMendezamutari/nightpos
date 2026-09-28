<!-- Copia sincronizada de .specify/memory/constitution.md -->
<!--
Sync Impact Report:
- Version change: 1.0.0 -> 1.1.0
- Ratified: 2026-09-08 | Amended: 2026-09-27
-->

# RestoTech / NightPOS Constitution

## Core Principles

### I. Arquitectura Hexagonal y Domain-Driven Design (DDD)
El sistema backend DEBE estructurarse bajo Arquitectura Hexagonal (Puertos y Adaptadores) combinada con Diseño Guiado por el Dominio (DDD):
- **Capa de Dominio**: Entidades puras, Value Objects, contratos y puertos de repositorio.
- **Capa de Aplicación**: Casos de uso (Commands, Queries, Handlers).
- **Capa de Infraestructura**: Adaptadores Eloquent, clientes fiscales, servicios de hardware/impresión.
- **Capa de Presentación**: Controladores API REST desacoplados.

### II. Principios SOLID y Código Limpio
Todo el código DEBE cumplir estrictamente con los 5 principios SOLID.

### III. Test-First (TDD) y Confiabilidad 100% (NO NEGOCIABLE)
Cálculos exactos de caja, inventario y cuentas respaldados por pruebas automatizadas (72 tests pasando).

### IV. Cumplimiento Fiscal y Facturación Electrónica SIAT Bolivia
Emisión de Factura Fiscal con CUF/QR y Recibo / Nota de Venta interna de consumo.

### V. Ejecución Local Autónoma (Offline-First para Boliches y Restaurantes)
El sistema DEBE vivir y operar principalmente en la **red local del restaurante/boliche**:
- **Cero Dependencia de Internet para Operar**: Si la conexión cae, el boliche sigue operando al 100% sin retrasos ni pérdidas de comandas.
- **Tiempos de Respuesta Sub-segundo (< 100 ms)** en red local.

### VI. Impresión Térmica Directa a Hardware Local (Cero Diálogos)
La impresión de comandas y tickets de caja debe ser **100% directa y automática como RestoTech**:
- Al presionar **Cobrar (F12)** o confirmar pedido, la orden fluye directamente a la impresora física (`CAJA`, cocina o barra) sin diálogos emergentes.
- Soporte de Spooler nativo Windows (`WindowsDirectPrinterService`) y modo Kiosko (`--kiosk-printing`).

### VII. Sincronización Periódica a la Nube (Cloud Sync)
- El nodo local opera autónomamente con su base de datos local.
- Un servicio de sincronización en segundo plano (`SyncEngine`) replica ventas, arqueos y cierres de caja hacia el servidor central en línea (`nightpos.ribersoft.com`) para consulta gerencial remota.
- Encolamiento local ante caídas de internet con reintento automático.

### VIII. Frontend Vue.js 3 Táctil y Paridad con RestoTech
Interfaz optimizada para pantallas táctiles, atajos de teclado rápidos (F1, F4, F8, F9, F12) y modo oscuro para ambientes nocturnos.

### IX. Modificación, Extensión y Evolución Continua
Lógica descompilada y reescrita en Laravel 12 y Vue 3 para ser 100% modificable, permitiendo crear nuevas herramientas (KDS, pagos QR, reportes BI, split bill táctil).

**Versión**: 1.1.0 | **Ratificada**: 2026-09-08 | **Enmendada**: 2026-09-27
