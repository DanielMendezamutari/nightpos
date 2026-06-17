/**
 * Árbol de navegación NightPOS — Orden por prioridad operativa real.
 * Visibilidad: CASL (action/subject) + useNightPosNavItems (contexto superadmin/operacional).
 *
 * Orden:
 *   1. Operación  — acciones del día: consola, cobrar, VENTA DIRECTA, comandas, servicios, habitaciones
 *   2. Caja       — mi caja, ventas, fiscalización (acceso secundario a venta directa)
 *   3. Finanzas   — liquidaciones, cierre de turno, turnos (admin)
 *   4. Catálogo   — productos, categorías, precios
 *   5. Personal   — usuarios y staff
 *   6. Configuración
 *   7. Plataforma SaaS (solo superadmin)
 */
export default [

  // ─────────────────────────────────────────
  // 1. OPERACIÓN — acciones del día
  // ─────────────────────────────────────────
  { heading: 'Operación', requiresOperationalContext: true },
  {
    title: 'Operación',
    icon: { icon: 'ri-dashboard-3-line' },
    requiresOperationalContext: true,
    children: [
      { title: 'Dashboard operativo', to: 'nightpos-dashboard' },
      { title: 'Consola de turno', to: 'nightpos-shift-console', action: 'access', subject: 'shift_console.access' },
      { title: 'Cobrar comandas', to: 'nightpos-cashier-orders', action: 'access', subject: 'sales.charge' },
      // Venta directa: primario en Operación (uso diario)
      { title: 'Venta directa', to: 'nightpos-cash-direct-sale', action: 'access', subject: 'sales.direct_create' },
      { title: 'Comandas activas', to: 'nightpos-orders', action: 'access', subject: 'orders.access' },
      {
        title: 'Servicios',
        children: [
          { title: 'Manillas', to: 'nightpos-services-bracelets', action: 'access', subject: 'bracelets.access' },
          { title: 'Piezas', to: 'nightpos-services-room-services', action: 'access', subject: 'room_services.access' },
          { title: 'Shows', to: 'nightpos-services-shows', action: 'access', subject: 'shows.access' },
          { title: 'Control piezas', to: 'nightpos-services-room-control', action: 'access', subject: 'room_services.cleaning_view' },
        ],
      },
      {
        title: 'Habitaciones',
        children: [
          { title: 'Dashboard', to: 'nightpos-rooms-dashboard', action: 'access', subject: 'rooms.access' },
          { title: 'Listado', to: 'nightpos-rooms-list', action: 'access', subject: 'rooms.access' },
          { title: 'Disponibles', to: 'nightpos-rooms-available', action: 'access', subject: 'rooms.access' },
          { title: 'Limpieza', to: 'nightpos-rooms-cleaning', action: 'access', subject: 'rooms.access' },
          { title: 'Mantenimiento', to: 'nightpos-rooms-maintenance', action: 'access', subject: 'rooms.access' },
        ],
      },
    ],
  },

  // ─────────────────────────────────────────
  // 2. CAJA — apertura, movimientos, consultas
  // Venta directa: acceso secundario desde Mi caja y consola
  // ─────────────────────────────────────────
  { heading: 'Caja', requiresOperationalContext: true },
  {
    title: 'Caja',
    icon: { icon: 'ri-safe-2-line' },
    requiresOperationalContext: true,
    children: [
      { title: 'Mi caja', to: 'nightpos-cash', action: 'access', subject: 'cash.access' },
      { title: 'Venta directa', to: 'nightpos-cash-direct-sale', action: 'access', subject: 'sales.direct_create' },
      { title: 'Ventas del turno', to: 'nightpos-sales', action: 'access', subject: 'sales.list' },
      { title: 'Fiscalización de cajas', to: 'nightpos-finance-cash-sessions', action: 'access', subject: 'admin.cash_sessions.list' },
    ],
  },

  // ─────────────────────────────────────────
  // 3. FINANZAS — liquidaciones, cierre, turnos (admin)
  // ─────────────────────────────────────────
  { heading: 'Finanzas', requiresOperationalContext: true },
  {
    title: 'Finanzas',
    icon: { icon: 'ri-funds-line' },
    requiresOperationalContext: true,
    children: [
      {
        title: 'Liquidaciones',
        children: [
          { title: 'Resumen', to: 'nightpos-settlements', action: 'access', subject: 'settlements.access' },
          { title: 'Garzones', to: 'nightpos-settlements-waiters', action: 'access', subject: 'settlements.access' },
          { title: 'Chicas', to: 'nightpos-settlements-girls', action: 'access', subject: 'settlements.access' },
          { title: 'Limpieza', to: 'nightpos-settlements-cleaning', action: 'access', subject: 'settlements.access' },
          { title: 'Historial', to: 'nightpos-settlements-history', action: 'access', subject: 'settlements.history' },
        ],
      },
      { title: 'Reportes', to: 'nightpos-finance-reports', action: 'access', subject: 'reports.access' },
      { title: 'Cierre de turno', to: 'nightpos-shifts-close', action: 'access', subject: 'shifts.close' },
      // Gestión de turnos para admin/senior (historial, apertura)
      {
        title: 'Turnos',
        children: [
          { title: 'Turno actual', to: 'nightpos-shifts-current', action: 'access', subject: 'shifts.access' },
          { title: 'Abrir turno', to: 'nightpos-shifts-open', action: 'access', subject: 'shifts.open' },
          { title: 'Historial turnos', to: 'nightpos-shifts-history', action: 'access', subject: 'shifts.list' },
        ],
      },
    ],
  },

  // ─────────────────────────────────────────
  // 4. CATÁLOGO
  // ─────────────────────────────────────────
  { heading: 'Catálogo', requiresOperationalContext: true },
  {
    title: 'Catálogo',
    icon: { icon: 'ri-goblet-line' },
    requiresOperationalContext: true,
    children: [
      {
        title: 'Productos',
        children: [
          { title: 'Listado', to: 'nightpos-products', action: 'access', subject: 'products.list' },
          { title: 'Crear producto', to: 'nightpos-products-create', action: 'access', subject: 'products.create' },
        ],
      },
      {
        title: 'Categorías',
        children: [
          { title: 'Listado', to: 'nightpos-categories', action: 'access', subject: 'products.list' },
          { title: 'Crear categoría', to: 'nightpos-categories-create', action: 'access', subject: 'products.create' },
        ],
      },
      { title: 'Vista precios', to: 'nightpos-catalog-prices', action: 'access', subject: 'products.list' },
      { title: 'Config precios', to: 'nightpos-catalog-prices-config', action: 'access', subject: 'products.list' },
    ],
  },

  // ─────────────────────────────────────────
  // 5. PERSONAL
  // ─────────────────────────────────────────
  { heading: 'Personal', requiresOperationalContext: true },
  {
    title: 'Personal',
    icon: { icon: 'ri-team-line' },
    requiresOperationalContext: true,
    children: [
      {
        title: 'Usuarios',
        children: [
          { title: 'Listado', to: 'nightpos-users', action: 'access', subject: 'admin.users.list' },
          { title: 'Crear usuario', to: 'nightpos-users-create', action: 'access', subject: 'admin.users.create' },
        ],
      },
      { title: 'Garzones', to: 'nightpos-staff-waiters', action: 'access', subject: 'admin.users.list' },
      { title: 'Asignar mesas', to: 'nightpos-staff-waiter-assignments', action: 'access', subject: 'settings.waiter_assignments' },
      { title: 'Cajeras', to: 'nightpos-staff-cashiers', action: 'access', subject: 'admin.users.list' },
      { title: 'Chicas', to: 'nightpos-staff-girls', action: 'access', subject: 'admin.users.list' },
      { title: 'Roles y permisos', to: 'nightpos-staff-roles', action: 'access', subject: 'roles.access' },
    ],
  },

  // ─────────────────────────────────────────
  // 6. CONFIGURACIÓN
  // ─────────────────────────────────────────
  { heading: 'Configuración', requiresOperationalContext: true },
  {
    title: 'Configuración',
    icon: { icon: 'ri-settings-3-line' },
    requiresOperationalContext: true,
    children: [
      { title: 'Métodos de pago', to: 'nightpos-settings-payments', action: 'access', subject: 'settings.payment_methods' },
      { title: 'Motivos de caja', to: 'nightpos-settings-cash-reasons', action: 'access', subject: 'settings.cash_reasons' },
      { title: 'Ambientes', to: 'nightpos-settings-service-areas', action: 'access', subject: 'settings.service_areas' },
      { title: 'Mesas', to: 'nightpos-settings-service-tables', action: 'access', subject: 'settings.service_tables' },
      { title: 'Tipos habitación', to: 'nightpos-settings-room-types', action: 'access', subject: 'settings.room_types' },
      { title: 'Checklist 1ª noche', to: 'nightpos-settings-first-night-checklist', action: 'access', subject: 'settings.checklist' },
      { title: 'Sucursal actual', to: 'nightpos-settings-branch' },
      { title: 'Impresoras', to: 'nightpos-settings-printers' },
      { title: 'Preferencias', to: 'nightpos-settings-preferences' },
      { title: 'Seguridad', to: 'nightpos-settings-security' },
      { title: 'Bitácora auditoría', to: 'nightpos-settings-audit-logs', action: 'access', subject: 'audits.list' },
    ],
  },

  // ─────────────────────────────────────────
  // 7. PLATAFORMA SAAS (solo superadmin)
  // ─────────────────────────────────────────
  { heading: 'Plataforma SaaS', requiresSuperAdmin: true },
  {
    title: 'Plataforma SaaS',
    icon: { icon: 'ri-cloud-line' },
    requiresSuperAdmin: true,
    children: [
      { title: 'Dashboard SaaS', to: 'nightpos-platform-dashboard' },
      { title: 'Setup empresa', to: 'nightpos-platform-setup', action: 'access', subject: 'platform.setup' },
      {
        title: 'Empresas',
        children: [
          { title: 'Listado', to: 'nightpos-platform-tenants' },
          { title: 'Crear empresa', to: 'nightpos-platform-tenants-create', action: 'access', subject: 'admin.tenants.create' },
        ],
      },
      {
        title: 'Sucursales',
        children: [
          { title: 'Listado', to: 'nightpos-platform-branches' },
          { title: 'Crear sucursal', to: 'nightpos-platform-branches-create', action: 'access', subject: 'admin.branches.create' },
        ],
      },
      { title: 'Planes / Suscripciones', to: 'nightpos-platform-plans' },
      { title: 'Configuración SaaS', to: 'nightpos-platform-settings' },
    ],
  },
]
