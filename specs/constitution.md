# RiberResto POS — Constitución: Edición Boliche con Compañía & Nightclubs

<!--
Branch: feature/boliche-con-compania
Version: 2.0.0 (Boliche & Nightclub Edition)
Ratified: 2026-09-08 | Amended for Nightclub with Hostesses: 2026-09-28
-->

## Preámbulo
Esta Constitución establece las reglas inviolables de arquitectura, dominio y lógica de negocio para la variante de **RiberResto POS especializada en Boliches, Discotecas y Clubes Nocturnos con Damas de Compañía (Fichas, Comisiones y Liquidaciones)**, optimizada para la operación inalámbrica de garzones mediante la App Móvil Nativa Android en conjunto con la PC de Caja local.

---

## Principios Fundamentales (Core Principles)

### I. Trazabilidad Estricta de Garzones y Asignación de Mesas
1. **Asignación de Mesas (Zonas de Servicio)**:
   - A cada garzón se le asigna un lote operativo de mesas (habitualmente entre 4 a 6 mesas por turno).
   - En la App Móvil, el garzón visualiza prioritariamente sus mesas asignadas, con opción de ver el salón completo según permisos.
2. **Autoría de Comanda Obligatoria**:
   - Ninguna orden puede crearse sin el identificador del garzón (`waiter_id` o `mesero_id`).
   - El sistema registra con precisión de segundo qué garzón tomó cada ítem de comanda, permitiendo atribuir métricas de ventas y comisiones sin disputas.

### II. Tarificación Dual y Sistema de Fichas (Cliente Normal vs. Con Chica)
1. **Doble Precio por Producto**:
   - Todo producto catalogado como bebida o trago puede tener dos listas de precios configurables:
     - `precio_normal`: Precio de consumo individual del cliente (ej: Cerveza Bs. 40).
     - `precio_chica`: Precio de consumo cuando es invitado a una chica/anfitriona (ej: Cerveza Bs. 80).
2. **Modo Ficha Instantáneo (One-Touch)**:
   - Al comandar desde la App Móvil o desde Caja, el garzón puede alternar con un toque el modo de consumo:
     - `MODALIDAD_NORMAL`: Consumo habitual de mesa.
     - `MODALIDAD_CHICA`: Aplica `precio_chica` y asocia obligatoriamente a la chica beneficiaria (`chica_id`).
3. **Parametrización Centralizada**:
   - Los precios y reglas de ficha son variables y administrables desde el panel central de configuración de productos y tarifas.

### III. Motor de Remuneración y Comisiones de Garzones
1. **Estructura Híbrida (Base + Porcentaje)**:
   - Los garzones perciben una remuneración diaria de turno compuesta por:
     $$\text{Total Garzón} = \text{Sueldo Base (ej: Bs. 80 o Bs. 100)} + (\text{Ventas Comandadas por el Garzón} \times \% \text{ Comisión})$$
2. **Parámetros Flexibles por Colaborador**:
   - Cada garzón puede tener configurado su propio sueldo base por noche y su propio porcentaje de comisión sobre ventas (ej: 3%, 5%, 7%).

### IV. Motor de Fichas y Comisiones para Damas de Compañía / Chicas
1. **Estructura de Remuneración de Chicas**:
   - Puede contar con un `sueldo_base` diario (fijo o 0).
   - Más la suma de todas las fichas de copas y botellas acumuladas durante su turno de trabajo.
2. **Fichas en Bebidas Individuales / Copas**:
   - Se liquidan por porcentaje configurable sobre el `precio_chica` (habitualmente el 50%, ej: de Bs. 80 de la cerveza, Bs. 40 para la chica y Bs. 40 para el local).
3. **Fichas en Botellas (Whisky, Fernet, Vinos, Champán)**:
   - El modelo de compensación por botellas es **híbrido y configurable por producto**:
     - *Opción A (Monto Fijo)*: La chica recibe una comisión fija preestablecida (ej: Bs. 100 por botella de Johnnie Walker Black).
     - *Opción B (Porcentaje)*: La chica recibe un porcentaje definido del valor de la botella.
4. **Fichas Compartidas (Botellas entre varias chicas)**:
   - Si una botella es consumida por varias anfitrionas en la misma mesa, el sistema debe permitir dividir la comisión de la botella de forma equitativa entre las chicas participantes.

### V. Remuneración de Personal General por Porcentaje de Venta Global
1. **Comisiones de Staff Nocturno**:
   - Puestos clave como Barman, Bartender, DJ, Seguridad o Encargado de Salón pueden tener asignado un porcentaje de bonificación sobre la **Venta Neta Total de la Noche** (ej: Barman 2%, Seguridad 1%).
2. **Cálculo Transparente en Cierre Z**:
   - Al realizar el Cierre de Caja (Cierre Z), el sistema consolida la venta global y liquida automáticamente las participaciones de todo el personal.

### VI. Liquidación Térmica Diaria y Control de Vales / Adelantos
1. **Liquidación por Turno (Cero Deudas Pendientes)**:
   - Al finalizar la noche, el sistema genera la liquidación exacta de cada garzón, de cada chica y de cada colaborador de staff:
     $$\text{Pago Neto a Entregar} = \text{Base} + \text{Comisiones de Fichas/Ventas} - \text{Vales y Consumos en Turno}$$
2. **Ticket Físico Térmico de Liquidación**:
   - Impresión directa en formato de 42 columnas (Spooler RAW `CAJA`) con el desglose firmado de copas, botellas, comisiones y neto a pagar, sirviendo de comprobante de entrega de efectivo.

### VII. Local-First Autónomo y App Móvil Offline en Red Wi-Fi
1. **Cero Dependencia de Nube para Operar**:
   - En boliches con poca cobertura celular o con cientos de clientes, la red local interna debe operar al 100% sin conexión a internet.
2. **Tiempos de Respuesta Sub-segundo (< 100ms)**:
   - La toma de comandas y el cómputo de fichas deben ser instantáneos para no entorpecer el ritmo vertiginoso del despacho de bebidas.

### VIII. Test-First (TDD) y Confiabilidad Matemática
1. **Pruebas Automatizadas de Liquidación**:
   - Todos los algoritmos de cálculo de fichas (porcentajes, montos fijos en botellas, división entre chicas y cálculo de porcentajes de garzones) deben estar certificados por pruebas automatizadas con 100% de éxito.

---

**Versión**: 2.0.0 (Boliche con Compañía Edition)  
**Rama**: `feature/boliche-con-compania`  
**Ecosistema**: Ribersoft | RiberResto POS NightClub  
