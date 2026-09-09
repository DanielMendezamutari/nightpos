---
name: "speckit-autopilot"
description: "Autonomous end-to-end Reverse Spec-Driven Development pipeline orchestrator. Executes all SpecKit skills sequentially for a given module from legacy reverse engineering to tested Laravel 12 + Vue.js 3 implementation."
---

# SpecKit Autopilot: Reverse Spec-Driven Development Pipeline

This skill orchestrates the complete SpecKit methodology in an autonomous execution loop for a specific module of the RestoTech migration to **NightPOS (Laravel 12 Hexagonal + Vue.js 3 Materialize Template)**.

## Pipeline Architecture

For each module given as input (e.g. `001-auth-core-config`, `002-catalogo-mesas`, etc.), the agent MUST execute the following 10 phases sequentially without skipping any SpecKit skill:

```
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 0: Reverse Reconnaissance & Assembly Decompilation (ilspycmd)    │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 1: speckit-constitution (Foundation Verification)                │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 2: speckit-specify (Feature Specification & User Stories)        │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 3: speckit-clarify (Resolving Ambiguities & Edge Cases)          │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 4: speckit-checklist (Unit Tests for Requirements Writing)       │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 5: speckit-plan (Hexagonal Architecture & DDD Design)            │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 6: speckit-analyze (Cross-Artifact Consistency Audit)            │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 7: speckit-tasks (Dependency-Ordered TDD Task Breakdown)         │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 8: speckit-implement (Autonomous TDD Implementation Loop)        │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 9: speckit-converge (Legacy Parity & Spec Convergence)           │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 10: Notification ("Ya terminé, prueba la función")               │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Detailed Execution Steps

### Phase 0: Reverse Reconnaissance & Extraction
1. Identify legacy assemblies related to the target module (`ControlConsumo.exe`, `ControlConsumoLib.dll`, `ConfigToptech.dll`, `Datos.dll`).
2. Run `ilspycmd` to decompile relevant namespaces/types into `specs/<module>/legacy_reference/` or inspect data models in `ModelRestaurant.edmx`.
3. Consult `HelpRestoTech.chm` for screen definitions, user fields, and validation behaviors.

### Phase 1: Constitution Alignment (`speckit-constitution`)
1. Read `.specify/memory/constitution.md` to ensure active adherence to:
   - Hexagonal Architecture (Domain / Application / Infrastructure / UI).
   - SOLID Principles and Clean Code.
   - Test-Driven Development (TDD mandatory, 100% test pass).
   - JWT authentication.
   - SIAT Bolivia compliance (where applicable).
   - Desktop Docker packaging & anti-reverse code protection.
   - Materialize Vue.js 3 template integration.

### Phase 2: Feature Specification (`speckit-specify`)
1. Create `specs/<module>/spec.md` following the SpecKit template.
2. Define:
   - Module scope and domain boundaries.
   - User stories with Given-When-Then acceptance criteria.
   - Pure domain business rules extracted from legacy decompilation.
   - Input/output contracts and validation constraints.

### Phase 3: Clarification & Resolution (`speckit-clarify`)
1. Detect any potential ambiguities (e.g. legacy hardware interaction vs web alternative, desktop modal vs Vue dialog).
2. Auto-resolve based on legacy business logic where clear, or ask focused questions if human intent is needed.
3. Update `specs/<module>/spec.md` with clarifications.

### Phase 4: Quality Checklist (`speckit-checklist`)
1. Generate `specs/<module>/checklists/requirements.md` ("Unit Tests for Requirements").
2. Validate:
   - Completeness (are all UI states, error states, and edge cases covered?).
   - Clarity (are financial calculations, permissions, and status transitions unambiguous?).
   - Traceability back to the legacy system.

### Phase 5: Technical Implementation Plan (`speckit-plan`)
1. Create `specs/<module>/plan.md` defining the Hexagonal architecture:
   - **Domain Layer**: Entities, Value Objects, Domain Events, Ports (Repository Interfaces).
   - **Application Layer**: Use Cases (Commands/Queries/Handlers), DTOs.
   - **Infrastructure Layer**: Eloquent Model Adapters, JWT Handlers, External Adapters.
   - **Presentation Layer**: Laravel REST API Controllers, Requests, Resources.
   - **Frontend Layer**: Vue 3 Components adapted into `materialize-vuejs-admin-template`, Pinia Stores, Axios Services.
   - **Testing Plan**: PHPUnit/Pest unit & integration test definitions.

### Phase 6: Cross-Artifact Consistency Audit (`speckit-analyze`)
1. Cross-reference `spec.md`, `checklists/requirements.md`, and `plan.md`.
2. Confirm that every user story in `spec.md` has a corresponding Use Case and UI component in `plan.md`.
3. Ensure zero violations of the Constitution.

### Phase 7: Task Breakdown (`speckit-tasks`)
1. Generate `specs/<module>/tasks.md` with strict dependency ordering:
   - **Phase 1 (Tests & Domain)**: Unit tests (failing) -> Pure Domain Entities & Value Objects.
   - **Phase 2 (Application Layer)**: Use Cases & DTOs -> Application unit tests passing.
   - **Phase 3 (Infrastructure Layer)**: Migrations, Eloquent Adapters -> Integration tests passing.
   - **Phase 4 (Presentation Layer)**: FormRequests, Controllers, JWT middleware -> API feature tests passing.
   - **Phase 5 (Frontend Vue.js 3)**: Pinia stores, API clients, Materialize template components.
   - **Phase 6 (Verification & Git Commit)**: Full test suite execution and clean Git commit.

### Phase 8: Autonomous Implementation (`speckit-implement`)
1. Execute each task in `tasks.md` step-by-step.
2. Apply code directly to `C:\xampp\htdocs\nightpos`:
   - Backend: `C:\xampp\htdocs\nightpos\backend` (Laravel 12).
   - Frontend: `C:\xampp\htdocs\nightpos\frontend` (Vue 3).
3. Run tests using `php artisan test` and verify 100% green tests.

### Phase 9: Convergence & Parity Verification (`speckit-converge`)
1. Compare new implementation against legacy `specs/000-reconocimiento/mapa-sistema.md` and decompiled C# sources.
2. Ensure no field, validation, or business flow was omitted.
3. Commit clean code to `nightpos` git repository.

### Phase 10: Completion Notification
1. Deliver the final user-facing message:
   - Summary of completed module.
   - Instructions to test in `nightpos.test` and `pnpm run dev`.
   - Clear test cases for the user to verify functionality.
