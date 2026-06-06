import { fetchCurrentCashSession } from '@/api/cash'
import { useContextStore } from '@/stores/context'

export function useServiceCashSession() {
  const contextStore = useContextStore()
  const cashSession = ref(null)
  const cashSessionOpen = ref(false)
  const showOpenCash = ref(false)
  const loadingCash = ref(true)

  const loadCashSession = async () => {
    loadingCash.value = true
    try {
      const session = await fetchCurrentCashSession()
      cashSession.value = session ?? null
      cashSessionOpen.value = session?.status === 'OPEN'
    }
    catch (error) {
      cashSession.value = null
      cashSessionOpen.value = false
      if (import.meta.env.DEV) {
        console.error('[cash/session/current]', error?.response?.status, error?.response?.data?.message ?? error)
      }
    }
    finally {
      loadingCash.value = false
    }
  }

  const onCashOpened = async session => {
    showOpenCash.value = false
    if (session?.status === 'OPEN')
      cashSessionOpen.value = true

    await loadCashSession()
  }

  onMounted(loadCashSession)
  onActivated(loadCashSession)

  watch(() => contextStore.branchCode, () => {
    loadCashSession()
  })

  watch(() => contextStore.version, () => {
    loadCashSession()
  })

  return {
    cashSession,
    cashSessionOpen,
    showOpenCash,
    loadingCash,
    loadCashSession,
    onCashOpened,
  }
}
