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
    waiters,
    girls,
    cleaning,
    reload: load,
  }
}
