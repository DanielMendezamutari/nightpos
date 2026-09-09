# Especificación Funcional: Módulo 001 — Core, Configuración y Autenticación JWT

**Módulo**: `001-auth-core-config`  
**Estado**: Especificado (Reverse SDD de RestoTech)  
**Fuente de Verdad Legacy**: `ConfigToptech.dll` (clase `configuration`), `ControlConsumoLib.dll` (`ctlMeseros`, `TipoUsuarioID`, `dtsAccesos`, `clsConfiguraciones`), `HelpRestoTech.chm`.

---

## 1. Contexto y Objetivos de Negocio

El Módulo 001 establece los cimientos del sistema POS para restaurantes y locales nocturnos, gestionando:
1. **Empresas (Tenants) y Sucursales (Branches)**: Configuración fiscal (NIT, Razón Social, Dirección, Teléfono, Municipio), logotipo y parámetros operativos.
2. **Configuración Global del Restaurante**: Políticas de visibilidad de mesas (`SoloVeoMisMesas`, `TodosVenTodo`, `VeoMesasLibresMasMisMesas` extraídas de `ConfigToptech.dll`), cálculo de propinas/servicios y modo de operación.
3. **Usuarios, Roles y Permisos**:
   - Roles base extraídos del sistema legacy:
     - `SuperAdmin` (Administrador de plataforma / SaaS)
     - `Administrador` (Dueño / Gerente de local)
     - `Cajero` (Operador de caja, cobro y turnos)
     - `Mesero / Garzón` (Toma de pedidos, comandas y atención de mesas)
     - `Barman / Cocinero` (Despacho en barra y cocina)
     - `Almacenero` (Control de compras, kardex e inventarios)
4. **Autenticación Dual Desacoplada (JWT)**:
   - **Login con PIN Numérico (4 a 6 dígitos)**: Diseñado para terminales táctiles de caja y garzones en piso, permitiendo cambios de turno/relevo en menos de 3 segundos sin escribir contraseñas complejas.
   - **Login con Correo/Usuario y Contraseña**: Diseñado para el panel administrativo, gerencia y acceso remoto en la nube.
   - **JWT Seguro**: Tokens firmados con claims de `tenant_id`, `branch_id`, `user_id` y `role`.

---

## 2. Historias de Usuario y Criterios de Aceptación

### Historia 1: Autenticación Rápida por PIN para Cajeros y Meseros (P1)
**Como** cajero o mesero en una pantalla táctil,  
**Quiero** ingresar con mi PIN numérico de 4 dígitos seleccionando mi sucursal,  
**Para** acceder inmediatamente a mi estación de trabajo sin escribir contraseñas en teclado físico.

- **Escenario 1.1 (PIN Válido)**:
  - **Given** un usuario activo con PIN `1234` asignado a la sucursal `CENTRO`,
  - **When** ingresa el PIN `1234` desde la pantalla táctil o teclado numérico,
  - **Then** el sistema emite un token JWT válido, carga su perfil, permisos y redirige a la vista correspondiente según su rol (Cajero -> Caja, Mesero -> Mesas).
- **Escenario 1.2 (PIN Inválido o Bloqueado)**:
  - **Given** un intento con PIN incorrecto,
  - **When** se envía al backend,
  - **Then** el sistema responde con HTTP 401 Unauthorized y mensaje de error claro sin exponer información sensible.

---

### Historia 2: Autenticación por Usuario y Contraseña para Administradores (P1)
**Como** administrador o dueño de restaurante,  
**Quiero** autenticarme con mi usuario/correo y contraseña segura,  
**Para** gestionar configuraciones globales, reportes, cartas y usuarios.

- **Escenario 2.1 (Credenciales Correctas)**:
  - **Given** un usuario con rol `Administrador`,
  - **When** envía sus credenciales válidas,
  - **Then** recibe su token JWT y es redirigido al Dashboard administrativo de la plantilla Vue.
- **Escenario 2.2 (Usuario Inactivo)**:
  - **Given** un usuario dado de baja (`is_active = false`),
  - **When** intenta iniciar sesión,
  - **Then** el sistema rechaza el acceso indicando que la cuenta se encuentra inactiva.

---

### Historia 3: Parámetros y Configuración de Sucursal (P2)
**Como** administrador,  
**Quiero** consultar y actualizar la configuración operativa de mi sucursal (datos fiscales para SIAT, nombre comercial, visibilidad de mesas y moneda),  
**Para** que el POS funcione acorde a las reglas fiscales y de operación de mi negocio.

- **Escenario 3.1**:
  - **Given** una sucursal autenticada,
  - **When** el administrador consulta `GET /api/v1/settings`,
  - **Then** recibe la razón social, NIT, casa matriz/sucursal, modo de visibilidad de mesas y políticas de comisiones.

---

## 3. Modelo de Dominio Hexagonal

### Entidades y Value Objects de Dominio:
- **`Tenant`** (`TenantId`, `Name`, `Slug`, `IsActive`, `Plan`)
- **`Branch`** (`BranchId`, `TenantId`, `Code`, `Name`, `Address`, `Phone`, `Nit`, `SiatConfig`)
- **`User`** (`UserId`, `TenantId`, `BranchId`, `Name`, `Username`, `Email`, `PasswordHash`, `PinHash`, `Role`, `IsActive`)
- **`Role`** (`RoleId`, `Name`, `Permissions`)

### Puertos de Dominio (Interfaces):
- `UserRepositoryInterface`: Métodos puros `findByUsername`, `findByPin`, `findById`, `save`.
- `TenantRepositoryInterface`: Métodos `findBySlug`, `findById`.
- `TokenGeneratorInterface`: Puerto para la generación y validación de tokens JWT.
- `PasswordHasherInterface` / `PinHasherInterface`: Puerto para cifrado unidireccional.
