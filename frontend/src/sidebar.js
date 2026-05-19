/**
 * Lógica de navegación lateral (y menú móvil / submenús) extraída de AppLayout.
 * Mantiene el mismo comportamiento; el template y el resto del layout siguen en AppLayout.vue.
 */
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

/** @param {object} auth store de autenticación (useAuthStore) */
export function useSidebar(auth) {
  const route = useRoute()
  const mobileMenuOpen = ref(false)
  const reportsMenuOpen = ref(false)
  const adminMenuOpen = ref(false)
  const adminDetallesOpen = ref(false)
  const maintenanceMenuOpen = ref(false)
  const posMenuOpen = ref(false)

  const branchContextLabel = computed(() => {
    const role = auth.user.value?.role
    if (role === 'owner') return ''
    const d = auth.activeSiteDisplay.value
    if (!d) return ''
    return `${d.code} — ${d.name}`
  })

  const isWaiterShell = computed(() => auth.user.value?.role === 'waiter')

  const showReportsMenu = computed(() =>
    ['cashier', 'manager', 'admin', 'super_admin', 'owner'].includes(auth.user.value?.role),
  )

  const menuItems = computed(() => {
    if (auth.user.value?.role === 'waiter') {
      return [
        { to: '/pos/mesero', label: 'Mis mesas' },
        { to: '/productos', label: 'Carta rápida' },
      ]
    }

    if (auth.user.value?.role === 'cashier') {
      return [
        { to: '/dashboard', label: 'Inicio' },
        { to: '/pos/cajero', label: 'Cobrar (POS)' },
        { to: '/piezas/control', label: 'Piezas / tiempo' },
        { to: '/productos', label: 'Carta / productos' },
      ]
    }

    const items = [{ to: '/dashboard', label: 'Dashboard' }]
    const catalogInMaintenance = ['manager', 'admin', 'super_admin'].includes(auth.user.value?.role)
    if (!auth.isOwner.value && !catalogInMaintenance) {
      items.push({ to: '/productos', label: 'Productos' })
    }
    if (auth.isOwner.value) {
      items.push({ to: '/saas', label: 'SaaS Owner' })
    }
    if (auth.canManageBranches.value) {
      items.push({ to: '/sucursales', label: 'Sucursales' })
    }
    if (auth.isOwner.value) {
      items.push({ to: '/sistema', label: 'Bloqueo SaaS' })
    }
    return items
  })

  const showAdminMenu = computed(() => auth.canConfigureBranch.value)
  const showMaintenanceMenu = computed(() =>
    ['manager', 'admin', 'super_admin', 'owner'].includes(auth.user.value?.role),
  )
  const showCajaMenu = computed(() => ['cashier', 'admin', 'super_admin'].includes(auth.user.value?.role))
  const showPosMenu = computed(() =>
    ['waiter', 'admin', 'super_admin'].includes(auth.user.value?.role),
  )

  function syncAdminSubmenus(path) {
    if (!path.startsWith('/administracion')) {
      return
    }
    adminMenuOpen.value = true
    if (path.includes('/administracion/detalles')) {
      adminDetallesOpen.value = true
    }
  }

  function syncMaintenanceSubmenu(path) {
    if (!path.startsWith('/mantenimiento')) {
      return
    }
    maintenanceMenuOpen.value = true
  }

  function syncPosSubmenu(path) {
    if (!path.startsWith('/pos') && !path.startsWith('/piezas')) {
      return
    }
    posMenuOpen.value = true
  }

  function syncReportsSubmenu(path) {
    if (!path.startsWith('/reportes')) {
      return
    }
    reportsMenuOpen.value = true
  }

  watch(
    () => route.path,
    (path) => {
      syncReportsSubmenu(path)
      syncAdminSubmenus(path)
      syncMaintenanceSubmenu(path)
      syncPosSubmenu(path)
    },
    { immediate: true },
  )

  function toggleAdminMenu() {
    adminMenuOpen.value = !adminMenuOpen.value
  }

  function toggleDetallesMenu() {
    adminDetallesOpen.value = !adminDetallesOpen.value
  }

  function toggleMaintenanceMenu() {
    maintenanceMenuOpen.value = !maintenanceMenuOpen.value
  }

  function togglePosMenu() {
    posMenuOpen.value = !posMenuOpen.value
  }

  function toggleReportsMenu() {
    reportsMenuOpen.value = !reportsMenuOpen.value
  }

  function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value
  }

  function closeMobileMenu() {
    mobileMenuOpen.value = false
  }

  return {
    route,
    mobileMenuOpen,
    reportsMenuOpen,
    adminMenuOpen,
    adminDetallesOpen,
    maintenanceMenuOpen,
    posMenuOpen,
    branchContextLabel,
    isWaiterShell,
    menuItems,
    showReportsMenu,
    showAdminMenu,
    showMaintenanceMenu,
    showCajaMenu,
    showPosMenu,
    toggleAdminMenu,
    toggleDetallesMenu,
    toggleMaintenanceMenu,
    togglePosMenu,
    toggleReportsMenu,
    toggleMobileMenu,
    closeMobileMenu,
  }
}
