export const ROOMS_SECTION_TABS = [
  { title: 'Dashboard', value: 'dashboard', to: 'nightpos-rooms-dashboard', icon: 'ri-dashboard-line', permission: 'rooms.access' },
  { title: 'Habitaciones', value: 'list', to: 'nightpos-rooms-list', icon: 'ri-hotel-bed-line', permission: 'rooms.access' },
  { title: 'Disponibles', value: 'available', to: 'nightpos-rooms-available', icon: 'ri-checkbox-circle-line', permission: 'rooms.access' },
  { title: 'Limpieza', value: 'cleaning', to: 'nightpos-rooms-cleaning', icon: 'ri-brush-line', permission: 'rooms.access' },
  { title: 'Mantenimiento', value: 'maintenance', to: 'nightpos-rooms-maintenance', icon: 'ri-tools-line', permission: 'rooms.access' },
]

export function useFilteredRoomsTabs() {
  const auth = useAuthStore()

  return computed(() => ROOMS_SECTION_TABS.filter(tab => !tab.permission || auth.hasPermission(tab.permission)))
}
