/**
 * Catálogo único de navegación secundaria NightPOS.
 * Usado por el menú «Más» de cajera básica.
 *
 * Regla: agregar aquí nuevas entradas con permission + shells: ['cashier'];
 * no duplicar listas en composables.
 */
export const NIGHTPOS_SECONDARY_NAV_CATALOG = [
  // ── Operación ──
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Liquidaciones',
    subtitle: 'Garzones, chicas, multas y limpieza',
    icon: 'ri-wallet-3-line',
    to: 'nightpos-settlements',
    permission: 'settlements.access',
  },
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Ventas del turno',
    subtitle: 'Cobros de la sesión actual',
    icon: 'ri-bill-line',
    to: 'nightpos-sales',
    permission: 'sales.list',
  },
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Consola de turno',
    subtitle: 'Resumen operativo del turno',
    icon: 'ri-dashboard-3-line',
    to: 'nightpos-shift-console',
    permission: 'shift_console.access',
  },
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Manillas',
    subtitle: 'Servicio de manillas',
    icon: 'ri-vip-crown-line',
    to: 'nightpos-services-bracelets',
    permission: 'bracelets.access',
  },
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Shows',
    subtitle: 'Servicios de show',
    icon: 'ri-mic-line',
    to: 'nightpos-services-shows',
    permission: 'shows.access',
  },
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Control piezas',
    subtitle: 'Estado de limpieza',
    icon: 'ri-brush-line',
    to: 'nightpos-services-room-control',
    permission: 'room_services.cleaning_view',
  },
  {
    section: 'Operación',
    shells: ['cashier'],
    title: 'Habitaciones',
    subtitle: 'Dashboard de piezas',
    icon: 'ri-door-open-line',
    to: 'nightpos-rooms-dashboard',
    permission: 'rooms.access',
  },
  // ── Catálogo ──
  {
    section: 'Catálogo',
    shells: ['cashier'],
    title: 'Productos',
    subtitle: 'Listado de productos',
    icon: 'ri-goblet-line',
    to: 'nightpos-products',
    permission: 'products.list',
  },
  {
    section: 'Catálogo',
    shells: ['cashier'],
    title: 'Categorías',
    subtitle: 'Categorías de productos',
    icon: 'ri-folder-line',
    to: 'nightpos-categories',
    permission: 'product-categories.list',
  },
  {
    section: 'Catálogo',
    shells: ['cashier'],
    title: 'Vista precios',
    subtitle: 'Precios vigentes',
    icon: 'ri-price-tag-3-line',
    to: 'nightpos-catalog-prices',
    permission: 'products.list',
  },
  // ── Configuración ──
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Motivos de caja',
    subtitle: 'Ingresos y egresos manuales',
    icon: 'ri-file-list-3-line',
    to: 'nightpos-settings-cash-reasons',
    permission: 'settings.cash_reasons',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Métodos de pago',
    subtitle: 'Efectivo, QR y tarjeta',
    icon: 'ri-bank-card-line',
    to: 'nightpos-settings-payments',
    permission: 'settings.payment_methods',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Ambientes',
    subtitle: 'Salones y zonas',
    icon: 'ri-store-2-line',
    to: 'nightpos-settings-service-areas',
    permission: 'settings.service_areas',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Mesas',
    subtitle: 'Mesas por ambiente',
    icon: 'ri-table-line',
    to: 'nightpos-settings-service-tables',
    permission: 'settings.service_tables',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Asignar mesas',
    subtitle: 'Garzones y mesas',
    icon: 'ri-user-shared-line',
    to: 'nightpos-staff-waiter-assignments',
    permission: 'settings.waiter_assignments',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Tipos de habitación',
    subtitle: 'Clasificación de piezas',
    icon: 'ri-layout-grid-line',
    to: 'nightpos-settings-room-types',
    permission: 'settings.room_types',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Impresoras',
    subtitle: 'Agente local y cola de impresión',
    icon: 'ri-printer-line',
    to: 'nightpos-settings-printers',
    permission: 'settings.printers',
  },
  {
    section: 'Configuración',
    shells: ['cashier'],
    title: 'Checklist 1ª noche',
    subtitle: 'Arranque operativo',
    icon: 'ri-checkbox-multiple-line',
    to: 'nightpos-settings-first-night-checklist',
    permission: 'settings.checklist',
  },
  // ── Finanzas y turno ──
  {
    section: 'Finanzas y turno',
    shells: ['cashier'],
    title: 'Historial liquidaciones',
    subtitle: 'Pagos anteriores',
    icon: 'ri-history-line',
    to: 'nightpos-settlements-history',
    permission: 'settlements.history',
  },
  {
    section: 'Finanzas y turno',
    shells: ['cashier'],
    title: 'Cierre de turno',
    subtitle: 'Cerrar turno oficial',
    icon: 'ri-logout-circle-line',
    to: 'nightpos-shifts-close',
    permission: 'shifts.close',
  },
  {
    section: 'Finanzas y turno',
    shells: ['cashier'],
    title: 'Turno actual',
    subtitle: 'Estado del turno',
    icon: 'ri-time-line',
    to: 'nightpos-shifts-current',
    permission: 'shifts.access',
  },
  {
    section: 'Finanzas y turno',
    shells: ['cashier'],
    title: 'Reportes',
    subtitle: 'Resúmenes financieros',
    icon: 'ri-bar-chart-line',
    to: 'nightpos-finance-reports',
    permission: 'reports.access',
  },
  {
    section: 'Finanzas y turno',
    shells: ['cashier'],
    title: 'Fiscalización de cajas',
    subtitle: 'Sesiones de caja del local',
    icon: 'ri-safe-line',
    to: 'nightpos-finance-cash-sessions',
    permission: 'admin.cash_sessions.list',
  },
]

/**
 * @param {'cashier'} shell
 * @param {(slug: string) => boolean} can
 */
export function buildSecondaryNavSections(shell, can) {
  const sectionOrder = []
  const sectionMap = new Map()

  for (const entry of NIGHTPOS_SECONDARY_NAV_CATALOG) {
    if (!entry.shells.includes(shell))
      continue
    if (!can(entry.permission))
      continue

    if (!sectionMap.has(entry.section)) {
      sectionMap.set(entry.section, [])
      sectionOrder.push(entry.section)
    }

    sectionMap.get(entry.section).push({
      title: entry.title,
      subtitle: entry.subtitle,
      icon: entry.icon,
      to: entry.to,
      action: entry.action,
      permission: entry.permission,
    })
  }

  return sectionOrder.map(title => ({
    title,
    items: sectionMap.get(title) ?? [],
  }))
}
