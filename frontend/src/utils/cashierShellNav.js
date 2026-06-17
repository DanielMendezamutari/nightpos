import { computed } from 'vue'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

/** Tabs principales del shell cajera — orden operativo real. */
export const CASHIER_SHELL_TABS = [
  { key: 'cobrar', title: 'Cobrar', icon: 'ri-bank-card-line', to: 'nightpos-cashier-orders', permission: 'sales.charge' },
  { key: 'piezas', title: 'Piezas', icon: 'ri-hotel-bed-line', to: 'nightpos-cashier-piezas', permissions: ['room_services.access', 'rooms.access'] },
  { key: 'venta', title: 'Venta', icon: 'ri-shopping-cart-line', to: 'nightpos-cashier-venta', permission: 'sales.direct_create' },
  { key: 'caja', title: 'Caja', icon: 'ri-safe-2-line', to: 'nightpos-cashier-caja', permission: 'cash.access' },
  { key: 'mas', title: 'Más', icon: 'ri-more-line', to: 'nightpos-cashier-more' },
]

export function isCashierShellTabVisible(tab, can) {
  if (tab.permissions?.length)
    return tab.permissions.some(permission => can(permission))

  if (tab.permission)
    return can(tab.permission)

  return true
}

export function useCashierShellTabs() {
  const { can } = useNightPosPermissions()

  const visibleTabs = computed(() =>
    CASHIER_SHELL_TABS.filter(tab => isCashierShellTabVisible(tab, can)),
  )

  return { visibleTabs }
}
