<!--
Sync Impact Report:
- Version change: [CONSTITUTION_VERSION] -> 1.0.0
- Ratified: 2026-09-08 | Last Amended: 2026-09-08
- Principles established:
  1. Arquitectura Hexagonal y Domain-Driven Design (DDD)
  2. Principios SOLID y Código Limpio
  3. Test-First (TDD) y Confiabilidad 100% (NO NEGOCIABLE)
  4. Cumplimiento Fiscal y Facturación Electrónica SIAT Bolivia
  5. Empaquetado Desktop con Docker y Blindaje Anti-Reverse
  6. Frontend Vue.js 3 con Plantilla Comercial y Fidelidad de Pantallas
  7. Roadmap Secuencial: Sistema Core Primero, Servidor MCP Posterior
- Added sections: Restricciones Tecnológicas y de Seguridad, Flujo de Calidad y Puertas de Aceptación, Gobernanza.
- Removed sections: N/A (Scaffold inicial completado).
- Deferred items: Ninguno.
-->

# RestoTech Constitution

## Core Principles

### I. Arquitectura Hexagonal y Domain-Driven Design (DDD)
El sistema backend DEBE estructurarse bajo Arquitectura Hexagonal (Puertos y Adaptadores) combinada con Diseño Guiado por el Dominio (DDD):
- **Capa de Dominio**: Contiene entidades puras, objetos de valor (Value Objects), eventos de dominio y contratos/puertos (interfaces de repositorios y servicios externos). NO DEBE depender de Laravel, Eloquent ni librerías de terceros.
- **Capa de Aplicación**: Contiene casos de uso (Commands, Queries, Handlers) y DTOs que orquestan el flujo de negocio.
- **Capa de Infraestructura**: Contiene implementaciones concretas (adaptadores Eloquent, cliente SOAP para SIAT, adaptadores de impresión ESC/POS, autenticación JWT).
- **Capa de Presentación / UI**: Controladores API REST, Form Requests y API Resources. Los controladores no deben contener reglas de negocio ni consultas directas a la base de datos.

### II. Principios SOLID y Código Limpio
Todo el código PHP DEBE cumplir estrictamente con los 5 principios SOLID:
- **S (Single Responsibility)**: Cada clase tiene una sola razón para cambiar (un caso de uso = un handler/interactor).
- **O (Open/Closed)**: El sistema debe ser extensible mediante interfaces y estrategias sin modificar el núcleo probado.
- **L (Liskov Substitution)**: Las implementaciones de repositorios o servicios externos deben ser completamente intercambiables sin romper el dominio.
- **I (Interface Segregation)**: Interfaces pequeñas y específicas para cada puerto.
- **D (Dependency Inversion)**: Los módulos de alto nivel (Dominio y Aplicación) NUNCA dependen de detalles de bajo nivel (Infraestructura); ambos dependen de abstracciones (Puertos).

### III. Test-First (TDD) y Confiabilidad 100% (NO NEGOCIABLE)
La calidad y exactitud matemática en un sistema POS/facturación es crítica:
- El ciclo Red-Green-Refactor es OBLIGATORIO: las pruebas se escriben primero, fallan y luego se implementa el código que las satisface.
- **Pruebas Unitarias**: Cobertura exhaustiva de entidades de dominio, cálculos de montos, impuestos, descuentos, propinas y reglas de negocio sin tocar la base de datos.
- **Pruebas de Integración**: Validación de repositorios Eloquent, generadores de XML firmado para SIAT y emisión de tokens JWT.
- Ninguna tarea se considera completa si no cuenta con sus pruebas automatizadas en verde.

### IV. Cumplimiento Fiscal y Facturación Electrónica SIAT Bolivia
El motor de facturación DEBE replicar con fidelidad absoluta las especificaciones del SIN (Servicio de Impuestos Nacionales) de Bolivia:
- Gestión completa de autenticación y códigos: CUIS (Código Único de Inicio de Sistemas) y CUFD (Código Único de Facturación Diaria).
- Generación de XML conforme a los esquemas XSD oficiales, cálculo de hash SHA-256 y firma digital XMLDSig.
- Soporte estricto de contingencia offline: almacenamiento en paquete y sincronización diferida automática al restablecerse la conexión.
- Emisión y validación de Facturas de Compra/Venta y Notas de Crédito/Débito.

### V. Empaquetado Desktop con Docker y Blindaje Anti-Reverse
El sistema debe estar diseñado para operar tanto en local (desktop restaurante) como en la nube:
- **Ejecución Local**: Contenedores Docker pre-orquestados (Docker Compose) para desplegar el runtime de Laravel, base de datos y servidor web de forma desatendida y aislada en computadoras del restaurante.
- **Blindaje Anti-Reverse (Protección de Propiedad Intelectual)**: El código PHP de producción NO se distribuye en texto plano. DEBE aplicarse protección y ofuscación de código (compilación a bytecode / extensiones tipo PHP-Bolt / contenedor cerrado sin volumen montado de código fuente, con permisos de solo lectura) y esquema de validación de licencias para impedir que terceros puedan descompilar, duplicar o robar la solución.

### VI. Frontend Vue.js 3 con Plantilla Comercial y Fidelidad de Pantallas
La interfaz gráfica de usuario debe modernizarse sobre Vue.js 3 aprovechando la plantilla comercial adquirida por el usuario:
- Uso de Composition API (`<script setup>`), Pinia para gestión de estado global y Vue Router.
- Mantener la paridad funcional y ergonomía del punto de venta táctil original (pantallas de salón/mesas, toma de pedidos "Tartina", pantalla de caja, pantalla de comandas de cocina y bar).
- Autenticación segura y desacoplada mediante JWT en cabeceras HTTP.
- Comunicación con hardware local (impresoras térmicas de tickets y comandas) mediante micro-servicio local o WebSockets.

### VII. Roadmap Secuencial: Sistema Core Primero, Servidor MCP Posterior
El alcance del desarrollo debe mantenerse ordenado y sin dispersión:
- **Fase 1 (Migración y Estabilización Core)**: Replicar el 100% de las funcionalidades del sistema actual (catálogo, mesas, pedidos, caja, facturación SIAT, inventario, reportes) con pruebas y validación completa.
- **Fase 2 (Extensibilidad con MCP)**: ÚNICAMENTE cuando la migración esté concluida y probada al 100%, se diseñará e integrará la capa de Model Context Protocol (MCP) para que los dueños del restaurante puedan consultar métricas, alertas y reportes mediante agentes de IA.

## Restricciones Tecnológicas y de Seguridad

- **Lenguaje y Framework Backend**: PHP 8.3+ / Laravel 12.
- **Autenticación**: JSON Web Tokens (JWT) con rotación y revocación segura de tokens.
- **Base de Datos**: MySQL 8.0+ o PostgreSQL 16+. Migraciones reproducibles con seeds de datos maestros.
- **Frontend**: Vue.js 3, Vite, Pinia, Axios, integrando la plantilla comercial preexistente.
- **Hardware POS**: Soporte para impresoras térmicas ESC/POS (cocina, bar, caja) y gaveta de dinero.

## Flujo de Calidad y Puertas de Aceptación

1. **Requisitos Verificados**: Cada módulo debe pasar por `speckit-specify`, `speckit-clarify` y validación con `speckit-checklist` ("Unit Tests for Requirements") antes de planificar.
2. **Arquitectura Auditada**: El plan técnico (`speckit-plan`) debe ser validado con `speckit-analyze` para comprobar conformidad con los principios Hexagonal y SOLID.
3. **Ejecución y Convergencia**: Ninguna implementación se cierra sin ejecución exitosa de pruebas y validación con `speckit-converge`.

## Gobernanza

- Esta Constitución gobierna todas las decisiones técnicas, de arquitectura y de diseño del proyecto RestoTech.
- Ninguna tarea o cambio de código puede violar los principios aquí consagrados sin una enmienda explícita y documentada.
- Todas las herramientas del pipeline de Spec-Driven Development (`.agents/skills/speckit-*`) deben ceñirse a estos principios en cada iteración.

**Version**: 1.0.0 | **Ratified**: 2026-09-08 | **Last Amended**: 2026-09-08
