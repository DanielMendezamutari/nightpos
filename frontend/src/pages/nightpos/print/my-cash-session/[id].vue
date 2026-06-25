<script setup>
import { fetchCashSession } from '@/api/cash'
import PrintableCashSessionReport from '@/components/nightpos/print/PrintableCashSessionReport.vue'
import { buildShiftLabel } from '@/constants/printTicket'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'cash.access',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const loading = ref(true)
const session = ref(null)
const summary = ref(null)
const operational = ref(null)

const ticketWidth = computed(() => route.query.width === '58' ? '58mm' : '80mm')

const reportData = computed(() => {
  const s = session.value
  if (!s)
    return null

  return {
    id: s.id,
    cashierName: s.cashier_name || s.opened_by_name || '',
    openedAt: s.opened_at,
    closedAt: s.closed_at,
    openingNotes: s.opening_notes || '',
    closingNotes: s.closing_notes || '',
    isForcedClose: !!s.is_forced_close,
  }
})

onMounted(async () => {
  try {
    const data = await fetchCashSession(Number(route.params.id))
    session.value = data?.session ?? null
    summary.value = data?.summary ?? null
    operational.value = data?.operational ?? null
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableCashSessionReport
    :data="reportData"
    :summary="summary"
    :operational="operational"
    :shift-label="buildShiftLabel(session?.official_shift)"
    :width="ticketWidth"
    :loading="loading"
  />
</template>
