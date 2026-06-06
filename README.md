# NIGHTPOS SaaS

## Migración de sistema Restaurant Bolivia a sistema SaaS para boliches / night clubs

### Backend API + Frontend Vue + Arquitectura Hexagonal + SOLID + Multi Tenant

---

# VISIÓN DEL PROYECTO

Este proyecto **NO debe continuarse como PHP monolítico**.

El sistema heredado `restaurant_bolivia-1` sirve como **fuente de verdad para entender el negocio**: productos, ventas, pedidos/comandas, mesas, cajas, arqueos, inventario, compras, traspasos, sucursales, clientes, usuarios, créditos y reportes.

El producto final será:

| Capa | Rol final |
| ---- | --------- |
| Backend | Laravel API REST `/api/v1` con arquitectura hexagonal |
| Frontend | Vue 3 + Pinia + Axios + plantilla admin |
| Heredado PHP | Solo referencia de negocio; no debe crecer |
| Base de datos | Nueva estructura limpia, multi tenant y preparada para SaaS |

---

# OBJETIVO PRINCIPAL

Migrar el sistema actual de restaurante a un sistema SaaS adaptable a boliches, bares y casas nocturnas, manteniendo lo útil del sistema heredado pero corrigiendo su arquitectura.

El sistema nuevo debe soportar:

* Varias empresas/clientes SaaS.
* Varias sucursales por empresa.
* Mesas, salas, garzones, cajeros y encargados.
* Pedidos/comandas desde celular.
* Venta directa y venta por mesa.
* Caja por turno con arqueo.
* Inventario por productos, ingredientes y combos.
* Compras, proveedores y traspasos.
* Reportes por casa, caja, turno, vendedor y producto.
* Precios especiales para boliche: **solo / con acompañante**.
* Futuras reglas: comisiones, multas, limpieza, taxi, QR, efectivo y tarjeta.

---

# REGLA PRINCIPAL

No reconstruir todo sin analizar.

Primero se debe migrar por módulos:

1. Analizar el heredado.
2. Documentar reglas reales.
3. Crear casos de uso.
4. Crear API.
5. Probar backend.
6. Crear frontend.
7. Validar visualmente.
8. Pasar al siguiente módulo.

---

# MÓDULOS PRIORITARIOS

| Prioridad | Módulo | Motivo |
| --------- | ------ | ------ |
| 1 | Auth / usuarios / permisos | Seguridad base |
| 2 | Tenants / sucursales | SaaS y multi casa |
| 3 | Productos / precios | Base del negocio |
| 4 | Mesas / salas | Flujo boliche |
| 5 | Pedidos / comandas | Operación diaria |
| 6 | Ventas / caja / arqueo | Dinero y control |
| 7 | Inventario / kardex | Stock y pérdidas |
| 8 | Compras / proveedores | Abastecimiento |
| 9 | Reportes | Control administrativo |
| 10 | Reglas boliche | Solo, acompañante, comisiones, limpieza |

---

# ARCHIVOS DE ESTA CARPETA

* `SYSTEM_ANALYSIS.md`
* `SAAS_ARCHITECTURE.md`
* `DATABASE_GUIDELINES.md`
* `DEVELOPMENT_RULES.md`
* `API_DOCUMENTATION.md`
* `FRONTEND_GUIDELINES.md`
* `MIGRATION_PLAN.md`
* `ROADMAP.md`
* `BOLICHE_RULES.md`
* `PROMPT_CURSOR.md`

---

# IMPRESIÓN AUTOMÁTICA DE COMANDAS

El sistema nuevo debe agregar impresión automática de comandas sin quitar la funcionalidad visual actual.

Regla principal:

* El garzón comanda desde su celular.
* El backend guarda la comanda normalmente.
* La comanda sigue apareciendo en pantalla, reportes, caja e historial.
* Además, el backend crea automáticamente un trabajo de impresión (`PrintJob`).
* Un agente local instalado en una PC del boliche imprime la comanda en la impresora térmica.

El hosting online no debe intentar conectarse directamente a la impresora física, porque la impresora está en la red local del boliche. La solución correcta es usar un **Print Agent local**.

Flujo:

```text
Garzón registra comanda desde celular
        ↓
Backend API guarda la comanda
        ↓
Backend API crea PrintJob pendiente
        ↓
Print Agent local consulta la API
        ↓
Print Agent imprime en barra / cocina / caja
        ↓
Print Agent marca el trabajo como impreso
```

Esta funcionalidad debe quedar desacoplada mediante arquitectura hexagonal.


---

# ACTUALIZACIÓN FUNCIONAL: CIERRE DE CAJA Y PAGOS DIARIOS

El sistema NIGHTPOS SaaS debe contemplar que en un boliche el pago al personal puede realizarse el mismo día.

Por eso se agrega:

* Cierre de turno separado por efectivo, QR y tarjeta.
* Manillas para chicas por consumo con acompañante.
* Piezas como servicio adicional pagable a chica.
* Shows como servicio adicional pagable a chica.
* Comisión variable para garzones: 5%, 6% u otro valor configurable.
* Liquidación diaria por chica.
* Liquidación diaria por garzón.
* Registro de pagos a personal como movimiento de caja.

Ejemplo de regla:

```text
Paceña solo cliente: 40 Bs
Paceña con chica: 80 Bs
Monto para chica: 40 Bs
```

```text
Huari solo cliente: 50 Bs
Huari con chica: 100 Bs
Monto para chica: 50 Bs
```

La cajera debe ver esta información al cerrar caja para saber cuánto debe pagar a cada persona.
