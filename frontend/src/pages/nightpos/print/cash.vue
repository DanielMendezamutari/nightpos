<script setup>
import PrintableCashSessionReport from '@/components/nightpos/print/PrintableCashSessionReport.vue'
import { fetchCurrentCashSession } from '@/api/cash'
import { fetchProductReconciliation } from '@/api/reports'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'cash.access',
  },
})

const { triggerAutoPrint } = useNightPosPrint()
const session = ref(null)
const reconciliation = ref(null)
const loading = ref(true)

const reportData = computed(() => {
  const s = session.value
  if (!s)
    return null

  const byMethod = s.sales_by_method ?? {}

  return {
    id: s.id,
    cashierName: s.cashier_name || '',
    openedAt: s.opened_at,
    closedAt: s.closed_at,
    openingAmount: s.opening_amount,
    salesCash: byMethod.cash ?? byMethod.CASH ?? 0,
    salesQr: byMethod.qr ?? byMethod.QR ?? 0,
    salesCard: byMethod.card ?? byMethod.CARD ?? 0,
    income: s.income_total,
    expense: s.expense_total,
    expected: s.expected_amount,
    counted: s.declared_closing_amount,
    difference: s.difference_amount,
  }
})

onMounted(async () => {
  try {
    session.value = await fetchCurrentCashSession()
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
