import { fetchCurrentShiftSettlements } from '@/api/settlements'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

export function useCurrentShiftSettlements() {
  const { notify } = useNightPosNotify()

  const loading = ref(true)
  const shift = ref(null)
  const summary = ref(null)
  const context = ref(null)
  const sourcesSummary = ref(null)
  const autoSyncEnabled = ref(true)
  const lastAutoSyncAt = ref(null)
  const pendingSourcesCount = ref(0)
  const syncStatus = ref('UP_TO_DATE')
  const syncMessage = ref(null)
  const waiters = ref([])
  const girls = ref([])
  const cleaning = ref([])

  const load = async () => {
    loading.value = true

    try {
      const data = await fetchCurrentShiftSettlements()

      shift.value = data.shift
      summary.value = data.summary
      context.value = data.context ?? null
      sourcesSummary.value = data.sources_summary ?? null
      autoSyncEnabled.value = Boolean(data.auto_sync_enabled ?? true)
      lastAutoSyncAt.value = data.last_auto_sync_at ?? null
      pendingSourcesCount.value = Number(data.pending_sources_count ?? 0)
      syncStatus.value = data.sync_status ?? 'UP_TO_DATE'
      syncMessage.value = data.sync_message ?? null
      waiters.value = data.waiters ?? []
      girls.value = data.girls ?? []
      cleaning.value = data.cleaning ?? []

      if (data.context?.shift_rotated) {
        notify('Se inició un nuevo turno automáticamente.', 'info')
      }
    }
    catch (error) {
      if (import.meta.env.DEV) {
        console.error('[settlements/current-shift]', error?.response?.status, error?.response?.data?.message ?? error)
      }
      notify(getApiErrorMessage(error), 'error')
    }
    finally {
      loading.value = false
    }
  }

  onMounted(load)
  useOnContextChange(load)

  return {
    loading,
    shift,
    summary,
    context,
    sourcesSummary,
    autoSyncEnabled,
    lastAutoSyncAt,
    pendingSourcesCount,
    syncStatus,
    syncMessage,
    waiters,
    girls,
    cleaning,
    reload: load,
  }
}
