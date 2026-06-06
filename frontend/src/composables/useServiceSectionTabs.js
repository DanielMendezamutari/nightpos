import { useAuthStore } from '@/stores/auth'

export const SERVICE_SECTION_TABS = [
  { title: 'Manillas', value: 'bracelets', to: 'nightpos-services-bracelets', icon: 'ri-gem-line', permission: 'bracelets.access' },
  { title: 'Piezas', value: 'room-services', to: 'nightpos-services-room-services', icon: 'ri-door-line', permission: 'room_services.access' },
  { title: 'Shows', value: 'shows', to: 'nightpos-services-shows', icon: 'ri-mic-line', permission: 'shows.access' },
  { title: 'Control piezas', value: 'room-control', to: 'nightpos-services-room-control', icon: 'ri-alarm-warning-line', permission: 'room_services.cleaning_view' },
]

export function useFilteredServiceTabs() {
  const auth = useAuthStore()

  return computed(() => SERVICE_SECTION_TABS.filter(
    tab => !tab.permission || auth.hasPermission(tab.permission),
  ))
}
