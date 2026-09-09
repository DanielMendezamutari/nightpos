# Checklist de Calidad de Requisitos — Módulo 001 (Core, Configuración y Auth JWT)

> **Propósito**: "Unit Tests para los Requisitos en lenguaje natural" — Valida exhaustivamente que la especificación sea completa, inequívoca, trazable y lista para la arquitectura hexagonal.

---

## 1. Completitud de Requisitos (Completeness)
- [ ] ¿Están definidos todos los flujos de autenticación requeridos (PIN táctil para POS y Usuario/Password para Admin)?
- [ ] ¿Se especifican las reglas para el hash seguro del PIN (evitar almacenamiento en texto plano)?
- [ ] ¿Se contempla la expiración, renovación y revocación del token JWT?
- [ ] ¿Está documentado el comportamiento cuando un usuario pertenece a múltiples sucursales?
- [ ] ¿Se definen los códigos de error HTTP exactos (401, 403, 404, 422) para cada caso fallido?

## 2. Claridad e Inequivocidad (Clarity)
- [ ] ¿La longitud del PIN está estrictamente acotada (4 a 6 dígitos numéricos)?
- [ ] ¿Los roles y su jerarquía de permisos están explícitamente enumerados sin ambigüedades?
- [ ] ¿Se distingue con precisión la separación entre datos de Tenant (Empresa) y Branch (Sucursal física)?

## 3. Coherencia y Restricciones de la Constitución (Constitution Alignment)
- [ ] ¿La lógica de autenticación está desacoplada de la base de datos mediante Puertos e Interfaces (Hexagonal)?
- [ ] ¿El modelo de dominio `User` y `Tenant` carece de dependencias directas con el framework Laravel / Eloquent?
- [ ] ¿Los endpoints de API devuelven DTOs/Resources formateados para consumo limpio en la plantilla Vue 3 Materialize?

## 4. Trazabilidad con el Sistema Legacy RestoTech
- [ ] ¿Los roles base coinciden con los tipos de usuario extraídos de `ctlMeseros.TipoUsuarioID`?
- [ ] ¿Se preservan los modos de visibilidad de mesas extraídos de `ConfigToptech.dll` (`SoloVeoMisMesas`, `TodosVenTodo`)?
- [ ] ¿Los campos fiscales de la sucursal (NIT, razón social, dirección) son compatibles con los requeridos para la facturación SIAT Bolivia?
