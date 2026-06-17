import { computed } from 'vue'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

/**
 * Ítems secundarios del tab «Más» para cajera básica.
 * Solo se muestran entradas con permiso explícito (sin menú admin completo).
 */
export function useCashierMoreMenu() {
  const { can } = useNightPosPermissions()

  const sections = [
    {
      title: 'Operación',
      items: [
        {
          title: 'Liquidaciones',
          subtitle: 'Garzones, chicas y limpieza',
          icon: 'ri-wallet-3-line',
          to: 'nightpos-settlements',
          permission: 'settlements.access',
        },
        {
          title: 'Ventas del turno',
          subtitle: 'Cobros de la sesión actual',
          icon: 'ri-bill-line',
          to: 'nightpos-sales',
          permission: 'sales.list',
        },
        {
          title: 'Consola de turno',
          subtitle: 'Resumen operativo del turno',
          icon: 'ri-dashboard-3-line',
          to: 'nightpos-shift-console',
          permission: 'shift_console.access',
        },
        {
          title: 'Manillas',
          subtitle: 'Servicio de manillas',
          icon: 'ri-vip-crown-line',
          to: 'nightpos-services-bracelets',
          permission: 'bracelets.access',
        },
        {
          title: 'Shows',
          subtitle: 'Servicios de show',
          icon: 'ri-mic-line',
          to: 'nightpos-services-shows',
          permission: 'shows.access',
        },
        {
          title: 'Control piezas',
          subtitle: 'Estado de limpieza',
          icon: 'ri-brush-line',
          to: 'nightpos-services-room-control',
          permission: 'room_services.cleaning_view',
        },
        {
          title: 'Habitaciones',
          subtitle: 'Dashboard de piezas',
          icon: 'ri-door-open-line',
          to: 'nightpos-rooms-dashboard',
          permission: 'rooms.access',
        },
      ],
    },
    {
      title: 'Catálogo',
      items: [
        {
          title: 'Productos',
          subtitle: 'Listado de productos',
          icon: 'ri-goblet-line',
          to: 'nightpos-products',
          permission: 'products.list',
        },
        {
          title: 'Categorías',
          subtitle: 'Categorías de productos',
          icon: 'ri-folder-line',
          to: 'nightpos-categories',
          permission: 'product-categories.list',
        },
        {
          title: 'Vista precios',
          subtitle: 'Precios vigentes',
          icon: 'ri-price-tag-3-line',
          to: 'nightpos-catalog-prices',
          permission: 'products.list',
        },
      ],
    },
    {
      title: 'Configuración',
      items: [
        {
          title: 'Motivos de caja',
          subtitle: 'Ingresos y egresos manuales',
          icon: 'ri-file-list-3-line',
          to: 'nightpos-settings-cash-reasons',
          permission: 'settings.cash_reasons',
        },
        {
          title: 'Métodos de pago',
          subtitle: 'Efectivo, QR y tarjeta',
          icon: 'ri-bank-card-line',
          to: 'nightpos-settings-payments',
          permission: 'settings.payment_methods',
        },
        {
          title: 'Ambientes',
          subtitle: 'Salones y zonas',
          icon: 'ri-store-2-line',
          to: 'nightpos-settings-service-areas',
          permission: 'settings.service_areas',
        },
        {
          title: 'Mesas',
          subtitle: 'Mesas por ambiente',
          icon: 'ri-table-line',
          to: 'nightpos-settings-service-tables',
          permission: 'settings.service_tables',
        },
        {
          title: 'Asignar mesas',
          subtitle: 'Garzones y mesas',
          icon: 'ri-user-shared-line',
          to: 'nightpos-staff-waiter-assignments',
          permission: 'settings.waiter_assignments',
        },
        {
          title: 'Tipos de habitación',
          subtitle: 'Clasificación de piezas',
          icon: 'ri-layout-grid-line',
          to: 'nightpos-settings-room-types',
          permission: 'settings.room_types',
        },
      ],
    },
    {
      title: 'Finanzas y turno',
      items: [
        {
          title: 'Historial liquidaciones',
          subtitle: 'Pagos anteriores',
          icon: 'ri-history-line',
          to: 'nightpos-settlements-history',
          permission: 'settlements.history',
        },
        {
          title: 'Cierre de turno',
          subtitle: 'Cerrar turno oficial',
          icon: 'ri-logout-circle-line',
          to: 'nightpos-shifts-close',
          permission: 'shifts.close',
        },
        {
          title: 'Turno actual',
          subtitle: 'Estado del turno',
          icon: 'ri-time-line',
          to: 'nightpos-shifts-current',
          permission: 'shifts.access',
        },
        {
          title: 'Reportes',
          subtitle: 'Resúmenes financieros',
          icon: 'ri-bar-chart-line',
          to: 'nightpos-finance-reports',
          permission: 'reports.access',
        },
        {
          title: 'Fiscalización de cajas',
          subtitle: 'Sesiones de caja del local',
          icon: 'ri-safe-line',
          to: 'nightpos-finance-cash-sessions',
          permission: 'admin.cash_sessions.list',
        },
      ],
    },
  ]

  const visibleSections = computed(() =>
    sections
      .map(section => ({
        ...section,
        items: section.items.filter(item => can(item.permission)),
      }))
      .filter(section => section.items.length > 0),
  )

  const hasItems = computed(() => visibleSections.value.length > 0)

  return {
    visibleSections,
    hasItems,
  }
}
