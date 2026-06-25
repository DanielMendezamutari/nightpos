<script setup>
import PrintableCashSessionReport from '@/components/nightpos/print/PrintableCashSessionReport.vue'
import { fetchAdminCashSession, forceCloseReasonLabel } from '@/api/adminCashSessions'
import { buildShiftLabel } from '@/constants/printTicket'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'admin.cash_sessions.view',
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
    cashierName: s.cashier?.name || s.opened_by || '',
    openedAt: s.opened_at,
    closedAt: s.closed_at,
    isForcedClose: !!s.is_forced_close,
    forcedClosedBy: s.forced_closed_by?.name || '',
    forcedCloseReason: forceCloseReasonLabel(s.forced_close_reason),
    forcedCloseNotes: s.forced_close_notes || '',
    openingNotes: s.opening_notes || '',
    closingNotes: s.closing_notes || '',
    blockerMessages: (s.close_blockers_snapshot?.blockers ?? []).map(b => b.message).filter(Boolean),
  }
})

const adminName = computed(() => {
  const s = session.value
  return s?.forced_closed_by?.name || s?.closed_by?.name || ''
})

onMounted(async () => {
  try {
    const data = await fetchAdminCashSession(route.params.id)
    session.value = data.session
    summary.value = data.summary
    operational.value = data.operational ?? null
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
    :branch-name="session?.branch?.name || ''"
    :tenant-name="session?.tenant?.name || ''"
    :shift-label="buildShiftLabel(session?.official_shift)"
    :admin-name="adminName"
    :width="ticketWidth"
    :loading="loading"
  />
</template>
