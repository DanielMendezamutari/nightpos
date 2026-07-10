# Auditoría Funcional de Arquitectura de Alcance de Liquidaciones

**Estado:** Auditoría funcional completada, sin implementación
**Ámbito:** Definir el alcance funcional correcto de liquidaciones en NightPOS
**Modelos evaluados:**

- **Modelo A:** Liquidaciones siguen `Official Shift`
- **Modelo B:** Liquidaciones siguen `Cash Session`
- **Modelo C (híbrido):** Operación diaria por caja y consolidación/histórico por turno

## Resumen ejecutivo

Con la evidencia actual del sistema real (caja abierta que atraviesa cambio de turno, rotación reactiva por request y desalineación entre `official_shift_id` y `cash_session_id`), el mejor ajuste funcional para NightPOS no es A puro ni B puro.

La recomendación arquitectónica es **Modelo C (híbrido con reglas explícitas y jerarquía de verdad por contexto)**:

- **Operación de cajera y pagos pendientes:** por `Cash Session`
- **Gobierno operativo de administración/owner y reportes históricos:** por `Official Shift`
- **Cierres y control de riesgo:** validación dual obligatoria (session + shift)

Este enfoque refleja cómo opera NightPOS en la práctica y reduce los falsos negativos de visibilidad sin perder control ejecutivo de turno.

## Contexto funcional de partida

1. NightPOS rota turnos de forma reactiva cuando una request llama a `EnsureOperationalShiftUseCase`.
2. Una `cash_session` puede permanecer abierta mientras cambia el turno operativo.
3. Ya existe evidencia real de ventas y liquidaciones en la misma caja pero con distinto `official_shift_id`.
4. Por eso, un único scope rígido provoca fricción funcional para cajera o para administración, según el modelo elegido.

## Modelo A: Liquidaciones siguen Official Shift

### Ventajas

- Alinea liquidaciones con el eje ejecutivo de operación diaria por turno.
- Simplifica análisis gerencial por ventana horaria oficial (día/noche).
- Facilita control global de productividad y rentabilidad por turno.
- Mantiene consistencia con tableros históricos ya orientados por turno.

### Desventajas

- Puede ocultar pendientes operativos de la cajera cuando su caja sigue abierta tras rotación.
- Penaliza operación continua en escenarios donde caja y turno no cierran al mismo tiempo.
- Incrementa incidencias de “no veo la liquidación” en operación real de piso.

### Impacto por actor/proceso

- **Impacto en cajera:** alto riesgo de no visualizar pendientes ligados a su caja si el turno cambió.
- **Impacto en administración:** buena visibilidad macro por turno, menor visibilidad de continuidad por caja.
- **Impacto en owner:** excelente lectura ejecutiva por turno, con pérdida de trazabilidad operativa por sesión.
- **Impacto en reportes:** fuerte para reportes gerenciales por turno; débil para reportes operativos de caja activa.
- **Impacto en cierre de caja:** fricción si hay pendientes en turno distinto al de apertura de caja.
- **Impacto en cierre de turno:** consistente conceptualmente, pero exige resolver pendientes cruzados con caja abierta.
- **Impacto en garzones:** pueden quedar “invisibles” para cajera aunque tengan pendiente real en su caja.
- **Impacto en chicas:** mismo riesgo que garzones cuando la liquidación cae en turno distinto.
- **Impacto en habitaciones:** cargos/servicios pueden quedar en turno nuevo aunque la caja sea la misma.
- **Impacto en auditoría:** fuerte para auditoría temporal por turno; débil para auditoría de responsabilidad de caja.
- **Impacto en conciliación:** conciliación por turno robusta; conciliación caja-a-caja más compleja.

## Modelo B: Liquidaciones siguen Cash Session

### Ventajas

- Refleja mejor la responsabilidad operativa de la cajera sobre su caja abierta.
- Evita pérdida de visibilidad de pendientes cuando cambia el turno oficial.
- Reduce fricción de pago en operación continua y acelera resolución en mostrador.
- Mejora trazabilidad de “quién cobró/pendiente” por sesión real de trabajo.

### Desventajas

- Debilita consolidación ejecutiva estricta por turno oficial.
- Puede fragmentar la lectura gerencial si una caja atraviesa múltiples turnos.
- Exige mayor trabajo de consolidación para reportes estratégicos por turno.

### Impacto por actor/proceso

- **Impacto en cajera:** máxima claridad y control de pendientes de su sesión.
- **Impacto en administración:** mejora control operativo de piso, pero requiere reconstrucción por turno.
- **Impacto en owner:** menor legibilidad directa de KPIs por turno si no hay consolidación adicional.
- **Impacto en reportes:** excelente para reportes operativos de caja; incompleto para analítica por turno.
- **Impacto en cierre de caja:** cierre más natural, porque todo lo pendiente de la sesión está visible.
- **Impacto en cierre de turno:** puede cerrar turno con pendientes aún abiertos en cajas que continúan.
- **Impacto en garzones:** mejor visibilidad de pendientes para cobro dentro de la misma caja.
- **Impacto en chicas:** mejora continuidad de pagos y control de pendientes por caja.
- **Impacto en habitaciones:** mejor seguimiento operativo si los cargos se cobran en caja activa.
- **Impacto en auditoría:** muy fuerte para auditoría de responsabilidad por caja; más débil en corte ejecutivo por turno.
- **Impacto en conciliación:** conciliación de caja simplificada; conciliación global por turno más costosa.

## Modelo C: Híbrido (Caja para operación, Turno para gobierno)

### Definición funcional propuesta

1. **Cajera trabaja por `Cash Session`** para pendientes y pagos operativos.
2. **Administración y owner trabajan por `Official Shift`** para control de turno.
3. **Reportes históricos y gerenciales** se consolidan por `Official Shift`.
4. **Pagos pendientes en operación** se muestran por `Cash Session`.
5. **Cierre de caja y cierre de turno** validan ambos contextos antes de permitir cierre definitivo.

### Ventajas

- Elimina el falso conflicto entre operación de mostrador y control ejecutivo.
- Reduce incidencias de visibilidad para cajera sin sacrificar gobernanza por turno.
- Conserva comparabilidad histórica por turno para owner/administración.
- Mejora trazabilidad para auditoría al disponer de doble eje (`cash_session_id` + `official_shift_id`).

### Desventajas

- Requiere reglas de negocio explícitas para evitar ambigüedades de UX y de control.
- Aumenta complejidad conceptual si no se documenta la jerarquía de decisiones.
- Exige disciplina de conciliación dual en cierres.

### Impacto por actor/proceso

- **Impacto en cajera:** alto, positivo; trabaja con su realidad operativa de caja.
- **Impacto en administración:** alto, positivo; mantiene control de turno y visibilidad operativa.
- **Impacto en owner:** alto, positivo; conserva KPIs por turno y lectura confiable del negocio.
- **Impacto en reportes:** robusto; operativo por caja y estratégico por turno sin perder histórico.
- **Impacto en cierre de caja:** más seguro; valida pendientes de sesión y consistencia contra turno.
- **Impacto en cierre de turno:** más seguro; valida estado agregado de sesiones relacionadas.
- **Impacto en garzones:** menos disputas por pagos no visibles; mejor continuidad en cobro.
- **Impacto en chicas:** misma mejora operativa de visibilidad y pago oportuno.
- **Impacto en habitaciones:** mejor trazabilidad de consumos largos con corte ejecutivo correcto.
- **Impacto en auditoría:** el más fuerte de los tres modelos por permitir doble trazabilidad.
- **Impacto en conciliación:** mejor balance; conciliación operativa y ejecutiva convergen con reglas duales.

## Comparación directa A vs B vs C

| Criterio | Modelo A (Shift) | Modelo B (Session) | Modelo C (Híbrido) |
|---|---|---|---|
| Operación cajera | Media/Baja | Alta | Alta |
| Control administración | Alta | Media | Alta |
| Lectura owner | Alta | Media | Alta |
| Reporte histórico | Alta | Media/Baja | Alta |
| Cierre de caja | Media/Baja | Alta | Alta |
| Cierre de turno | Alta | Media | Alta |
| Auditoría integral | Media | Media | Alta |
| Conciliación integral | Media | Media | Alta |

## Recomendación arquitectónica para NightPOS

La recomendación es **MODELO C (híbrido)** como estándar funcional de NightPOS.

### Razones de recomendación

1. NightPOS ya opera con desacople real entre caja y turno; forzar A o B puros generará nuevos costos funcionales.
2. El problema principal observado no es técnico aislado, sino de alineación de alcance por perfil y contexto.
3. C permite resolver la operación diaria de cajera sin comprometer gobierno, analítica ni auditoría de owner/administración.
4. C mejora conciliación porque obliga a validar tanto el contexto de sesión como el de turno.

## Regla arquitectónica final sugerida (sin implementar)

1. **Scope operativo de pagos pendientes:** `cash_session_id`.
2. **Scope de control y cierre de turno:** `official_shift_id`.
3. **Scope de reportes históricos y KPI:** `official_shift_id` consolidado.
4. **Scope de cierre de caja:** validación cruzada sesión-turno.
5. **Scope de auditoría:** siempre doble llave (`cash_session_id`, `official_shift_id`) con trazabilidad de usuario/tiempo.

## Conclusión

Para NightPOS, el modelo más sólido es el **híbrido (Modelo C)**. Resuelve la realidad operativa de caja sin perder control ejecutivo por turno, reduce conflictos de visibilidad de liquidaciones y fortalece auditoría y conciliación.
