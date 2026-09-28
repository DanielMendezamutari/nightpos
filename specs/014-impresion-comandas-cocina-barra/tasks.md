# Checklist de Tareas: Módulo 014 — Impresión Directa de Comandas a Cocina y Barra

- [ ] **T-1 (Base de Datos - Mapeo de Estaciones e Impresoras)**: Agregar campos `estacion` e `impresora_nombre` en `categorias_productos` y flag `impreso_comanda` en `visitas_detalles`.
- [ ] **T-2 (Backend - Formato ESC/POS de Comanda)**: Implementar `formatComanda()` en `TicketFormatterService` con títulos de mesa, garzón, hora, cantidades e indicaciones/notas de cocina en negrita.
- [ ] **T-3 (Backend - Despachador de Impresión)**: Crear `ComandaPrintDispatcherService` que agrupe los ítems nuevos de la visita por impresora de destino y despache en paralelo o secuencial a Windows Spooler.
- [ ] **T-4 (Backend - Hook en Envío de Comandas)**: Integrar el despachador en `ComandaController::enviarComanda` y en la API móvil de garzones.
- [ ] **T-5 (Backend - Tests Automatizados)**: Crear `ComandaImpresionApiTest` con mocks de impresión por estación y validación de marcado `impreso_comanda = true`.
- [ ] **T-6 (Frontend - Configuración y Feedback)**: Panel de configuración de impresoras por categoría y feedback visual en la comanda.
