import { useAuthStore } from '@/stores/auth'

export const SETTLEMENT_SECTION_TABS = [
  { title: 'Resumen', value: 'summary', to: 'nightpos-settlements', icon: 'ri-pie-chart-line' },
  { title: 'Garzones', value: 'waiters', to: 'nightpos-settlements-waiters', icon: 'ri-user-star-line' },
  { title: 'Chicas', value: 'girls', to: 'nightpos-settlements-girls', icon: 'ri-women-line' },
  { title: 'Limpieza', value: 'cleaning', to: 'nightpos-settlements-cleaning', icon: 'ri-brush-line' },
  { title: 'Historial', value: 'history', to: 'nightpos-settlements-history', icon: 'ri-history-line', permission: 'settlements.history' },
]

export function useFilteredSettlementTabs() {
  const auth = useAuthStore()

  return computed(() => SETTLEMENT_SECTION_TABS.filter(
    tab => !tab.permission || auth.hasPermission(tab.permission),
  ))
}
