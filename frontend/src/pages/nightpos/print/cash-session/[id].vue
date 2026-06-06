<script setup>
import PrintableCashSessionReport from '@/components/nightpos/print/PrintableCashSessionReport.vue'
import { fetchAdminCashSession } from '@/api/adminCashSessions'
import { fetchProductReconciliation } from '@/api/reports'
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
const reconciliation = ref(null)

const reportData = computed(() => {
  const s = session.value
  const sum = summary.value ?? {}
  if (!s)
    return null

  return {
    id: s.id,
    cashierName: s.cashier?.name || '',
    openedAt: s.opened_at,
    closedAt: s.closed_at,
    openingAmount: s.opening_amount,
    salesCash: sum.total_cash,
    salesQr: sum.total_qr,
    salesCard: sum.total_card,
    income: sum.total_manual_income,
    expense: sum.total_manual_expense,
    expected: sum.expected_cash,
    counted: s.counted_cash,
    difference: sum.cash_difference,
  }
})

onMounted(async () => {
  try {
    const data = await fetchAdminCashSession(route.params.id)

    session.value = data.session
    summary.value = data.summary
    if (session.value?.id)
      reconciliation.value = await fetchProductReconciliation({ cashSessionId: session.value.id }).catch(() => null)
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
    :reconciliation="reconciliation"
    :loading="loading"
  />
</template>
