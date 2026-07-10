# Auditoría Funcional de Arquitectura de Alcance de Liquidaciones (Frontend)

**Estado:** Auditoría funcional completada, sin implementación
**Ámbito:** Definir qué alcance debe mostrar la interfaz de liquidaciones según perfil y contexto operativo
**Modelos evaluados:**

- **Modelo A:** UI orientada a `Official Shift`
- **Modelo B:** UI orientada a `Cash Session`
- **Modelo C (híbrido):** UI contextual por perfil + consolidación histórica por turno

## Resumen ejecutivo

Desde frontend, el conflicto principal no es de renderizado: es de semántica de alcance. Cuando el backend rota turno y la caja continúa abierta, una UI de alcance único produce confusión para usuarios operativos.

La recomendación funcional para interfaz NightPOS es **Modelo C híbrido**:

- cajera ve pendientes por caja,
- administración/owner ven consolidado por turno,
- histórico y gerencial permanecen por turno,
- cierres muestran validación dual (caja + turno).

Así se reduce ambigüedad sin romper el control estratégico.

## Contexto funcional de UX

1. La UI consume `context.scope` y no decide internamente la lógica de rotación de turno.
2. El usuario final sí percibe la fricción cuando “la caja sigue abierta pero el turno cambió”.
3. Si la UI no diferencia claramente contexto operativo vs contexto ejecutivo, se interpreta como inconsistencia del sistema.

## Modelo A: Liquidaciones por Official Shift

### Ventajas

- Mensaje visual simple: “todo pertenece al turno actual”.
- Coherencia con dashboards gerenciales y vistas históricas por turno.
- Menor variabilidad de filtros para administración/owner.

### Desventajas

- En caja activa, puede esconder pendientes reales para cobro inmediato.
- Genera alta tasa de confusión para cajera en cambios de turno automáticos.
- Aumenta soporte operativo por diferencias entre expectativa y vista.

### Impacto por actor/proceso

- **Impacto en cajera:** bajo entendimiento operativo cuando su caja cruza turno.
- **Impacto en administración:** visión clara de turno, pero menos foco en fricción de caja.
- **Impacto en owner:** buena lectura ejecutiva, menor detalle de continuidad operativa.
- **Impacto en reportes:** fuerte coherencia visual con reportes por turno.
- **Impacto en cierre de caja:** puede mostrar estado parcial respecto a la caja real.
- **Impacto en cierre de turno:** experiencia clara para validar por turno.
- **Impacto en garzones:** riesgo de no aparecer en pantalla de cobro de cajera.
- **Impacto en chicas:** mismo riesgo de visibilidad parcial en operación.
- **Impacto en habitaciones:** cargos de larga duración pueden verse fuera de contexto de caja.
- **Impacto en auditoría:** audit trail visual de turno robusto, sesión menos visible.
- **Impacto en conciliación:** UX favorece conciliación por turno, no por caja.

## Modelo B: Liquidaciones por Cash Session

### Ventajas

- Lo que ve cajera coincide con su responsabilidad inmediata.
- Disminuye casos “no encuentro el pendiente” durante cobro.
- Mejora velocidad operativa de pago y confirmación en mostrador.

### Desventajas

- Administración/owner pierden claridad directa por turno en la misma pantalla.
- Puede fragmentar narrativa visual gerencial si se mezcla con histórico.
- Exige más componentes de consolidación en vistas ejecutivas.

### Impacto por actor/proceso

- **Impacto en cajera:** excelente usabilidad operativa.
- **Impacto en administración:** requiere cambiar a vistas adicionales para control por turno.
- **Impacto en owner:** menor lectura inmediata de desempeño de turno.
- **Impacto en reportes:** óptimo para operación viva, débil para historia por turno.
- **Impacto en cierre de caja:** UI muy alineada con tarea de cierre de sesión.
- **Impacto en cierre de turno:** necesita resumen adicional para no perder cobertura global.
- **Impacto en garzones:** mayor probabilidad de pago oportuno visible para cajera.
- **Impacto en chicas:** mejora visibilidad de pendientes de pago en operación.
- **Impacto en habitaciones:** mejor continuidad visual de cobros en la caja activa.
- **Impacto en auditoría:** fuerte por sesión; más trabajo para lectura por turno.
- **Impacto en conciliación:** favorece conciliación de caja, no siempre la de turno.

## Modelo C: Híbrido contextual

### Definición funcional de interfaz

1. **Pantalla operativa de cajera:** pendientes y pagos por `Cash Session`.
2. **Pantallas de administración/owner:** resumen y control por `Official Shift`.
3. **Reportes históricos:** siempre por `Official Shift`.
4. **Estado de pendientes críticos:** visible por sesión y con referencia al turno.
5. **Flujos de cierre:** checklist dual (validación de sesión + validación de turno).

### Ventajas

- Reduce confusión de usuario porque cada perfil ve su contexto natural.
- Evita pérdida de control gerencial e histórico por turno.
- Mejora explicabilidad de pantalla con etiquetas de contexto correctas.
- Mantiene trazabilidad visual y operativa para soporte y auditoría.

### Desventajas

- Requiere diseño UX claro para no mezclar contextos en la misma vista.
- Necesita convenciones de copy/etiquetas consistentes.
- Incrementa el trabajo de diseño funcional y validación de flujo.

### Impacto por actor/proceso

- **Impacto en cajera:** alto y positivo; reduce incidencias de visibilidad.
- **Impacto en administración:** alto y positivo; conserva control por turno.
- **Impacto en owner:** alto y positivo; mantiene consistencia ejecutiva.
- **Impacto en reportes:** robusto; operación por caja e histórico por turno.
- **Impacto en cierre de caja:** más claro, con pendientes de sesión reales.
- **Impacto en cierre de turno:** más confiable, con validación cruzada.
- **Impacto en garzones:** mejor visibilidad y menor retraso en liquidación.
- **Impacto en chicas:** mejora continuidad y transparencia de pago.
- **Impacto en habitaciones:** mejor lectura de cobros largos y su corte ejecutivo.
- **Impacto en auditoría:** superior por combinar trazabilidad operativa y temporal.
- **Impacto en conciliación:** superior por mostrar y validar ambos ejes.

## Comparación UX A vs B vs C

| Criterio UX/Funcional | Modelo A | Modelo B | Modelo C |
|---|---|---|---|
| Claridad para cajera | Baja/Media | Alta | Alta |
| Claridad para administración | Alta | Media | Alta |
| Claridad para owner | Alta | Media | Alta |
| Coherencia histórica | Alta | Media/Baja | Alta |
| Fricción en cobro pendiente | Alta | Baja | Baja |
| Riesgo de confusión por cambio de turno | Alta | Media | Baja |
| Auditabilidad visual integral | Media | Media | Alta |

## Recomendación arquitectónica frontend para NightPOS

La recomendación es **MODELO C (híbrido)**, con principio de “contexto correcto para el perfil correcto”:

1. Operación de cobro/pago en caja: scope de sesión.
2. Supervisión y gobierno: scope de turno.
3. Histórico/gerencial: turno como eje primario.
4. Cierres: representación dual y validación explícita de ambos contextos.

## Conclusión

En frontend, A y B puros son incompletos para NightPOS. El modelo híbrido C es el único que reduce fricción operativa sin degradar control estratégico ni legibilidad histórica.
