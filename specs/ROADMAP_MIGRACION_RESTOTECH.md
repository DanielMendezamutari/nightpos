# Roadmap Maestro de Migración RestoTech -> RiberResto POS

Este documento establece el plan ordenado para completar el 100% de las funciones legacy de RestoTech (`ControlConsumoLib.dll`), garantizando la arquitectura Local-First y el ciclo de Spec-Driven Development (SDD).

---

## 1. Estado Actual de la Migración

| Módulo | Nombre | Estado | Cobertura de Tests |
| :--- | :--- | :---: | :---: |
| **001** | Autenticación, Roles y Configuración Core | Completado | 100% |
| **002** | Salón y Mesas (Mapa Interactivo) | Completado | 100% |
| **003** | Comandas y Pedidos en Mesa | Completado | 100% |
| **004** | Facturación y Cobro con Recibo/Factura | Completado | 100% |
| **005** | Clientes y Directorio Base | Completado | 100% |
| **006** | Inventarios, Insumos, Recetas y Compras | Completado | 100% |
| **007** | Operaciones de Mesa (Mover y Juntar) | Completado | 100% |
| **008** | Separación de Cuentas (Split Bill) | Completado | 100% |
| **009** | Monitor Digital de Cocina y Barra (KDS) | Completado | 100% |
| **010** | Arqueo Ciego, Turnos de Caja y Gastos | Completado | 100% |
| **011** | Analítica y Matriz de Reportes | Completado | 100% |
| **012** | API Móvil de Garzones (Celular) | Completado | 100% |
| **013** | Impresión Térmica Directa RAW (Cobro y Precuenta) | Completado | 100% |

---

## 2. Plan para las Funcionalidades Faltantes

### Fase 1: Impresión de Comandas a Cocina y Barra Física (Módulo 014)
- **Prioridad**: Alta / Inmediata.
- **Legacy Reference**: `clsCategorias_Impresoras.cs`, `ImprimiendoComandas.printComandas`.
- **Objetivo**: Cuando el garzón o cajero da "Enviar Comanda", el sistema despacha automáticamente el ticket de cocina a la impresora física `COCINA` y las bebidas a la impresora física `BARRA`.
- **Documentación SDD**: Especificado en `specs/014-impresion-comandas-cocina-barra/`.

### Fase 2: Cuentas Corrientes y Clientes "Al Crédito" (Módulo 015)
- **Prioridad**: Media.
- **Legacy Reference**: `clsPagos.PagoAlCredito`, `ctlClientes.DevolverDeudas`, `frmCobranzas`.
- **Objetivo**: Clientes frecuentes, socios o amigos del local que consumen y dejan la cuenta al crédito ("al fiado"). Módulo de cobranzas para registrar abonos de deuda con recibo.

### Fase 3: Gift Cards y Saldo Prepago para Boliches (Módulo 016)
- **Prioridad**: Media.
- **Legacy Reference**: `clsCuentas.EsGiftCard`, `ImprimiendoComandas (sum pagos GiftCard)`.
- **Objetivo**: Venta y recarga de tarjetas o pulseras de consumo con saldo prepagado para eliminar el manejo de efectivo en barras y aumentar el ticket promedio.

### Fase 4: Descuentos y Promociones por Perfil de Cliente (Módulo 017)
- **Prioridad**: Media / Baja.
- **Legacy Reference**: `recalcularDescuento(visitaID, clienteID)`.
- **Objetivo**: Descuentos automáticos al vincular al cliente en la mesa (ej. 10% Cliente VIP, 15% cumpleañero del mes).

### Fase 5: Empaquetado "Todo en Uno" (.EXE Local Portable)
- **Prioridad**: Estratégica.
- **Objetivo**: Instalador único con Inno Setup / NSIS para Windows que instala PHP + Nginx/Caddy embebido + SQLite/MySQL local + Frontend compilado, listo para funcionar sin conexión a internet en menos de 3 minutos.
