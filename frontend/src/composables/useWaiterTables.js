import { computed, ref } from 'vue'
import { fetchWaiterMyTables, openWaiterTable } from '@/api/waiter'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useOrderOperationalEvents } from '@/composables/useOrderOperationalEvents'
import { useOperationalPollingFallback } from '@/composables/useOperationalPollingFallback'
import { getApiErrorMessage } from '@/services/http'

/**
 * Mesas del garzón — carga, agrupación, tap-to-open y refresco en vivo.
 */
export function useWaiterTables(options = {}) {
  const { autoLoad = true } = options

  const router = useRouter()
  const { notify } = useNightPosNotify()

  const tables = ref([])
  const loading = ref(false)
  const openingId = ref(null)

  const summary = computed(() => {
    const free = tables.value.filter(t => t.status === 'FREE').length
    const occupied = tables.value.filter(t => t.status === 'OCCUPIED').length

    return { free, occupied, total: tables.value.length }
  })

  const groupedByArea = computed(() => {
    const map = new Map()

    for (const table of tables.value) {
      const area = table.area?.trim() || 'General'
      if (!map.has(area))
        map.set(area, [])

      map.get(area).push(table)
    }

    return [...map.entries()]
      .map(([area, items]) => ({ area, items }))
      .sort((a, b) => a.area.localeCompare(b.area, 'es'))
  })

  const load = async () => {
    loading.value = true
    try {
      tables.value = await fetchWaiterMyTables()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
      tables.value = []
    }
    finally {
      loading.value = false
    }
  }

  const tapTable = async (table) => {
    if (openingId.value != null)
      return

    openingId.value = table.id
    try {
      const result = await openWaiterTable(table.id)
      const orderId = result.order?.id

      if (!orderId) {
        notify('No se pudo abrir la comanda.', 'error')
        await load()

        return
      }

      if (result.created)
        notify(`Comanda abierta — ${table.label}`, 'success')
      else
        notify(`Continuando ${table.label}`, 'info')

      await router.push({
        name: 'nightpos-waiter-orders-id',
        params: { id: orderId },
        query: result.created ? { add: '1' } : {},
      })
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
      await load()
    }
    finally {
      openingId.value = null
    }
  }

  const { connected, reconnecting } = useOrderOperationalEvents(load, {
    toastOnUpdated: false,
    updatedDebounceMs: 400,
  })

  useOperationalPollingFallback(load, { intervalMs: 30000, onlyWhenDisconnected: true })

  if (autoLoad) {
    onMounted(load)
    onActivated(() => {
      if (!loading.value && openingId.value == null)
        load()
    })
  }

  return {
    tables,
    loading,
    openingId,
    summary,
    groupedByArea,
    load,
    tapTable,
    sseConnected: connected,
    sseReconnecting: reconnecting,
  }
}
