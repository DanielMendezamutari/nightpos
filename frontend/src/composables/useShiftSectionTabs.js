import { useAuthStore } from '@/stores/auth'

export const SHIFT_SECTION_TABS = [
  { title: 'Turno actual', value: 'current', to: 'nightpos-shifts-current', icon: 'ri-time-line' },
  { title: 'Abrir turno', value: 'open', to: 'nightpos-shifts-open', icon: 'ri-play-circle-line', permission: 'shifts.open' },
  { title: 'Historial', value: 'history', to: 'nightpos-shifts-history', icon: 'ri-history-line', permission: 'shifts.list' },
  { title: 'Cierre', value: 'close', to: 'nightpos-shifts-close', icon: 'ri-lock-line', permission: 'shifts.close' },
]

export function useFilteredShiftTabs() {
  const auth = useAuthStore()

  return computed(() => SHIFT_SECTION_TABS.filter(
    tab => !tab.permission || auth.hasPermission(tab.permission),
  ))
}
