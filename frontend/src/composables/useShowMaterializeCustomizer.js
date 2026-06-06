import { useAuthStore } from '@/stores/auth'
import { isMobileOperationalRole } from '@/utils/mobileOperationalRole'

/**
 * Theme Customizer de Materialize: visible solo para roles administrativos/operativos de escritorio.
 */
export function useShowMaterializeCustomizer() {
  const authStore = useAuthStore()
  const route = useRoute()

  const showMaterializeCustomizer = computed(() => {
    if (!authStore.isAuthenticated || !authStore.user)
      return false

    if (isMobileOperationalRole(authStore.user))
      return false

    if (route.meta.layout === 'blank')
      return false

    return true
  })

  return {
    showMaterializeCustomizer,
  }
}
