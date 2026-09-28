<!--
Sync Impact Report:
- Version change: 1.0.0 -> 1.1.0
- Ratified: 2026-09-08 | Amended: 2026-09-27
- Core Directives:
  1. Arquitectura Hexagonal y Domain-Driven Design (DDD)
  2. Principios SOLID y Código Limpio
  3. Test-First (TDD) y Confiabilidad 100% (NO NEGOCIABLE)
  4. Cumplimiento Fiscal y Facturación Electrónica SIAT Bolivia
  5. Ejecución Local Autónoma (Offline-First para Boliches y Restaurantes)
  6. Impresión Térmica Directa a Hardware Local (Cero Ventanas de Diálogo)
  7. Sincronización Periódica a la Nube (Cloud Sync para Reportes Gerenciales)
  8. Frontend Vue.js 3 Táctil y Paridad de Ergonomía con RestoTech
  9. Modificación y Evolución Continua Post-Reverse Engineering
-->

# RestoTech / NightPOS Constitution

## Core Principles

### I. Arquitectura Hexagonal y Domain-Driven Design (DDD)
El sistema backend DEBE estructurarse bajo Arquitectura Hexagonal (Puertos y Adaptadores) combinada con Diseño Guiado por el Dominio (DDD):
- **Capa de Dominio**: Contiene entidades puras, objetos de valor (Value Objects), eventos de dominio y contratos/puertos (interfaces de repositorios y servicios externos). NO depende de frameworks ni librerías de terceros.
- **Capa de Aplicación**: Contiene casos de uso (Commands, Queries, Handlers) y DTOs que orquestan el flujo de negocio.
- **Capa de Infraestructura**: Contiene implementaciones concretas (adaptadores Eloquent, cliente SOAP para SIAT, adaptadores de impresión ESC/POS y Spooler de Windows, autenticación JWT).
- **Capa de Presentación / UI**: Controladores API REST, Form Requests y API Resources.

### II. Principios SOLID y Código Limpio
Todo el código DEBE cumplir estrictamente con los 5 principios SOLID:
- **S (Single Responsibility)**: Cada clase tiene una sola razón para cambiar.
- **O (Open/Closed)**: Extensible mediante interfaces y estrategias sin modificar el núcleo probado.
- **L (Liskov Substitution)**: Las implementaciones de repositorios o servicios externos deben ser completamente intercambiables.
- **I (Interface Segregation)**: Interfaces pequeñas y específicas para cada puerto.
- **D (Dependency Inversion)**: Los módulos de alto nivel nunca dependen de detalles de bajo nivel; ambos dependen de abstracciones.

### III. Test-First (TDD) y Confiabilidad 100% (NO NEGOCIABLE)
La calidad y exactitud matemática en un sistema POS/facturación es crítica:
- El ciclo Red-Green-Refactor es obligatorio para toda nueva lógica.
- Pruebas unitarias para reglas de negocio y pruebas de integración para endpoints, bases de datos y cálculo de balances.
- Ninguna funcionalidad se considera completada sin su suite de pruebas automatizadas en verde.

### IV. Cumplimiento Fiscal y Facturación Electrónica SIAT Bolivia
El motor de facturación DEBE replicar con fidelidad absoluta las especificaciones del SIN (Servicio de Impuestos Nacionales) de Bolivia:
- Gestión completa de CUIS y CUFD.
- Cálculo de CUF y representación en ticket térmico con Ley N° 453.
- Soporte estricto de comprobantes duales: **FACTURA FISCAL** y **RECIBO / NOTA DE VENTA DE CONSUMO INTERNO**.

### V. Ejecución Local Autónoma (Offline-First para Boliches y Restaurantes)
El sistema DEBE vivir y operar principalmente en la **red local del restaurante/boliche**:
- **Cero Dependencia de Internet para Operar**: Si la conexión a internet cae o el boliche está repleto, el sistema DEBE seguir abriendo mesas, enviando comandas a cocina/barra y cobrando en caja sin la más mínima interrupción o lentitud.
- **Tiempos de Respuesta Sub-segundo (< 100 ms)**: La operación de mesa y comanda local es instantánea para garantizar máxima agilidad al personal.

### VI. Impresión Térmica Directa a Hardware Local (Cero Diálogos)
La impresión de comandas de cocina y tickets de caja debe ser **100% directa y automática como RestoTech**:
- Al presionar **Cobrar (F12)** o confirmar pedido, la orden debe fluir directamente a la impresora física (`CAJA`, cocina o barra) sin abrir ventanas emergentes, diálogos de confirmación ni requerir clics adicionales.
- Se soporta envío nativo por Spooler de Windows en la máquina local o modo Kiosko dedicado (`--kiosk-printing`).

### VII. Sincronización Periódica a la Nube (Cloud Sync)
Para permitir que los dueños y gerentes consulten sus ventas, inventario y reportes en tiempo real desde sus celulares o de forma remota:
- El nodo local almacena y procesa todo en su base de datos local.
- Un servicio de sincronización en segundo plano (`SyncEngine`) envía periódicamente o por eventos (cierre de caja, facturas emitidas, arqueo ciego) lotes de datos cifrados al servidor central en línea (`nightpos.ribersoft.com`).
- Si no hay internet, los lotes se encolan localmente y se transmiten automáticamente en cuanto se restablezca la conectividad.

### VIII. Frontend Vue.js 3 Táctil y Paridad con RestoTech
La interfaz gráfica de usuario está optimizada para terminales táctiles (touchscreen) de 10" a 24":
- Botones grandes, teclados numéricos en pantalla, contraste óptimo para ambientes oscuros de boliches/discotecas.
- Atajos de teclado rápidos (F1 Efectivo, F4 Tarjeta, F8 Factura, F9 Recibo, F12 Cobrar, Escape Cerrar).

### IX. Modificación, Extensión y Evolución Continua
El reverse-engineering del sistema legacy RestoTech (`ControlConsumoLib.dll`, `ConfigToptech.dll`) se realizó como punto de partida para **liberar la lógica propietaria**:
- El código resultante en Laravel 12 y Vue 3 es modular, limpio y 100% modificable.
- Se implementan mejoras que el sistema original no tenía: Pagos QR dinámicos con webhook bancario, Monitor KDS digital en vivo, separación de cuentas en $N$ partes con arrastre táctil y analítica gerencial moderna.

## Gobernanza

- Esta Constitución gobierna todas las decisiones técnicas y de despliegue del proyecto.
- Cualquier nueva implementación o módulo bajo Spec-Driven Development debe adherirse a los principios de **Operación Local Primero, Impresión Directa y Sincronización Cloud Asíncrona**.

**Versión**: 1.1.0 | **Ratificada**: 2026-09-08 | **Enmendada**: 2026-09-27
