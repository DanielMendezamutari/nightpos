import { useAuthStore } from '@/stores/auth'

export const CASH_SESSION_SECTION_TABS = [
  { title: 'Cajas abiertas', value: 'open', to: 'nightpos-finance-cash-sessions', icon: 'ri-safe-2-line' },
  { title: 'Historial', value: 'history', to: 'nightpos-finance-cash-sessions-history', icon: 'ri-history-line' },
  { title: 'Resumen', value: 'summary', to: 'nightpos-finance-cash-sessions-summary', icon: 'ri-pie-chart-line' },
  { title: 'Por cajera', value: 'by-cashier', to: 'nightpos-finance-cash-sessions-by-cashier', icon: 'ri-user-line' },
  { title: 'Por turno', value: 'by-shift', to: 'nightpos-finance-cash-sessions-by-shift', icon: 'ri-time-line' },
]

export function useFilteredCashSessionTabs() {
  const auth = useAuthStore()

  return computed(() => CASH_SESSION_SECTION_TABS.filter(
    tab => !tab.permission || auth.hasPermission(tab.permission),
  ))
}
