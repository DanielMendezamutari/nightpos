import { computed } from 'vue'
import { buildSecondaryNavSections } from '@/navigation/nightposSecondaryNavCatalog'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

/**
 * Menú «Más» dinámico según permisos efectivos del usuario.
 * Catálogo único: nightposSecondaryNavCatalog.js
 */
export function useSecondaryNavMenu(shell) {
  const { can } = useNightPosPermissions()

  const visibleSections = computed(() => buildSecondaryNavSections(shell, can))

  const hasItems = computed(() => visibleSections.value.some(section => section.items.length > 0))

  return {
    visibleSections,
    hasItems,
  }
}

/** @deprecated Use useSecondaryNavMenu('cashier') */
export function useCashierMoreMenu() {
  return useSecondaryNavMenu('cashier')
}
