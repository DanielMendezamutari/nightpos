# Reglas de Proyecto: RiberResto POS (Ecosistema Ribersoft)

Este proyecto sigue la metodología **Spec-Driven Development (SDD)** de forma estricta e inquebrantable.

## 1. Constitución y Proceso SDD Obligatorio
Antes de generar o modificar código funcional:
1. Revisar la Constitución en `.specify/memory/constitution.md`.
2. Crear o actualizar la carpeta de especificación del módulo correspondiente en `specs/NNN-nombre-modulo/`:
   - `spec.md` (Historias de usuario en formato Gherkin y contratos de API).
   - `plan.md` (Diseño arquitectónico, modelos y flujos técnicos).
   - `tasks.md` (Checklist de tareas numeradas T-1, T-2...).
3. Implementar con pruebas automatizadas (`php artisan test`).
4. Actualizar el estado de las tareas en `tasks.md`.

## 2. Principios de Arquitectura
- **Local-First (Offline-First)**: La PC de caja debe operar al 100% de forma autónoma sin depender de internet.
- **Impresión Térmica RAW Directa**: Las impresoras físicas de Windows (`CAJA`, `COCINA`, `BARRA`) se comunican vía Spooler RAW Win32 (`winspool.drv`), con corte automático de papel y sin diálogos emergentes del navegador.
- **Marca Oficial**: Toda referencia debe usar la marca **Ribersoft** y el nombre de producto **RiberResto POS**.
