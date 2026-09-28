---
name: spec-driven-development
description: >-
  Metodología Spec-Driven Development (SDD) para RiberResto POS / NightPOS.
  Garantiza el ciclo estricto: 1. Constitución y Especificación Funcional (spec.md) ->
  2. Plan de Arquitectura (plan.md) -> 3. Checklist granular de Tareas (tasks.md) ->
  4. Ejecución guiada por pruebas (TDD).
---

# Spec-Driven Development (SDD) Workflow

Este workflow rige todo el desarrollo de nuevas características y migraciones desde el sistema legacy RestoTech.

## Principios Fundamentales
1. **No coding without specs**: Nunca escribir código productivo sin antes actualizar o crear `spec.md`, `plan.md` y `tasks.md`.
2. **Constitución Inquebrantable**: Todo cambio debe alinearse con la Constitución v1.1.0 (`.specify/memory/constitution.md`).
3. **Local-First & Offline Resilience**: La operación local en la PC de caja (comandas, cobros, turnos, impresión) jamás debe depender de conexión a internet externa.
4. **Fidelidad Operacional**: Comportamiento idéntico o superior al sistema RestoTech (Win32), sin diálogos del navegador y con impresión térmica RAW directa.

## Estructura de Módulos (`specs/NNN-nombre-modulo/`)
- `spec.md`: Historias de usuario en formato Gherkin (Given/When/Then), reglas de negocio y contratos de API.
- `plan.md`: Arquitectura técnica, modelos de base de datos, flujos de servicios y dependencias.
- `tasks.md`: Checklist numerado con estados `[ ]` y `[x]` para seguimiento granular.

## Comandos y Fases
- `/specify` o "especificar": Crea o actualiza `spec.md`.
- `/plan` o "planificar": Crea o actualiza `plan.md`.
- `/tasks` o "tareas": Genera el checklist en `tasks.md`.
- `/verify` o "verificar": Ejecuta la suite de pruebas unitarias/feature (`php artisan test`) antes de dar por completado.
