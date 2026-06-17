import { fetchCurrentCashSession } from '@/api/cash'
import { fetchCashierChargeableOrders } from '@/api/orders'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOperationalEvents } from '@/composables/useOperationalEvents'

/**
 * Estado compartido del shell cajera: caja, cola pendiente, SSE.
 */
export function useCashierShell() {
  const { canChargeOrders } = useNightPosPermissions()

  const cashSessionOpen = ref(false)
  const pendingCount = ref(0)
  const pendingTotalBob = ref('0.00')
  const loading = ref(false)
  const showOpenCash = ref(false)

  const refresh = async () => {
    loading.value = true

    try {
      const tasks = [loadCashSession()]

      if (canChargeOrders.value)
        tasks.push(loadPendingQueue())

      await Promise.all(tasks)
    }
    finally {
      loading.value = false
    }
  }

  const loadCashSession = async () => {
    try {
      const session = await fetchCurrentCashSession()

      cashSessionOpen.value = session?.status === 'OPEN'
    }
    catch {
      cashSessionOpen.value = false
    }
  }

  const loadPendingQueue = async () => {
    try {
      const rows = await fetchCashierChargeableOrders()

      pendingCount.value = rows?.length ?? 0
      const total = (rows ?? []).reduce((sum, row) => sum + Number(row.total ?? 0), 0)

      pendingTotalBob.value = total.toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    }
    catch {
      pendingCount.value = 0
      pendingTotalBob.value = '0.00'
    }
  }

  const openCashDialog = () => {
    showOpenCash.value = true
  }

  const { on, start: startSse, stop: stopSse, connected: sseConnected, reconnecting: sseReconnecting } = useOperationalEvents()

  let debounce = null
  const debouncedRefresh = () => {
    clearTimeout(debounce)
    debounce = setTimeout(refresh, 600)
  }

  on('order.created', debouncedRefresh)
  on('order.billed', debouncedRefresh)
  on('order.updated', debouncedRefresh)
  on('order.cancelled', debouncedRefresh)
  on('cash.session.opened', debouncedRefresh)
  on('cash.session.closed', debouncedRefresh)
  on('sale.created', debouncedRefresh)
  on('direct_sale.created', debouncedRefresh)

  onMounted(async () => {
    await refresh()
    startSse()
  })

  onUnmounted(() => {
    stopSse()
    clearTimeout(debounce)
  })

  return {
    cashSessionOpen,
    pendingCount,
    pendingTotalBob,
    loading,
    showOpenCash,
    refresh,
    openCashDialog,
    sseConnected,
    sseReconnecting,
  }
}
