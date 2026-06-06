import { fetchSettlementPendingSources } from '@/api/settlements'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { getApiErrorMessage } from '@/services/http'

/**
 * Carga opcional de fuentes pendientes sin bloquear la pantalla principal.
 */
export function useSettlementPendingSources() {
  const { can } = useNightPosPermissions()

  const loading = ref(false)
  const error = ref(null)
  const pendingSources = ref(null)

  const load = async () => {
    if (!can('settlements.pending_sources')) {
      pendingSources.value = null
      error.value = null

      return
    }

    loading.value = true
    error.value = null

    try {
      pendingSources.value = await fetchSettlementPendingSources()
    }
    catch (err) {
      pendingSources.value = null
      error.value = getApiErrorMessage(err)

      if (import.meta.env.DEV) {
        console.error('[settlements/current-shift/pending-sources]', err?.response?.status, error.value)
      }
    }
    finally {
      loading.value = false
    }
  }

  onMounted(load)
  useOnContextChange(load)

  return {
    loading,
    error,
    pendingSources,
    reload: load,
  }
}
