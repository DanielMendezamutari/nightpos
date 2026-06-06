<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { useAdminCashSessionsList } from '@/composables/useAdminCashSessionsList'
import { useFilteredCashSessionTabs } from '@/composables/useCashSessionSectionTabs'
import { formatMoney } from '@/composables/useOrderHelpers'

definePage({ meta: { permission: 'admin.cash_sessions.list' } })

const cashSessionTabs = useFilteredCashSessionTabs()
const router = useRouter()

const { loading, sessions } = useAdminCashSessionsList(null)

const grouped = computed(() => {
  const map = new Map()

  for (const session of sessions.value) {
    const key = session.cashier?.id ?? 'unknown'
    const label = session.cashier?.name ?? 'Sin cajera'

    if (!map.has(key)) {
      map.set(key, {
        cashier_id: key,
        cashier_name: label,
        sessions_count: 0,
        open_count: 0,
        total_sales: 0,
        expected_cash: 0,
        total_expense: 0,
      })
    }

    const row = map.get(key)

    row.sessions_count++
    if (session.status === 'OPEN') row.open_count++
    row.total_sales += Number(session.total_sales || 0)
    row.expected_cash += Number(session.expected_cash || 0)
    row.total_expense += Number(session.total_manual_expense || 0)
  }

  return [...map.values()]
})

const headers = [
  { title: 'Cajera', key: 'cashier_name' },
  { title: 'Sesiones', key: 'sessions_count' },
  { title: 'Abiertas', key: 'open_count' },
  { title: 'Efectivo esperado', key: 'expected_cash' },
  { title: 'Ventas', key: 'total_sales' },
  { title: 'Egresos', key: 'total_expense' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const viewCashierSessions = cashierId => {
  router.push({
    name: 'nightpos-finance-cash-sessions-history',
    query: { cashier_user_id: cashierId },
  })
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Cajas por cajera"
      subtitle="Agregado de sesiones agrupadas por cajera."
      :breadcrumbs="[
        { title: 'Finanzas' },
        { title: 'Fiscalización de cajas', to: { name: 'nightpos-finance-cash-sessions' } },
        { title: 'Por cajera' },
      ]"
    />

    <NightPosSectionTabs :tabs="cashSessionTabs" />

    <VCard>
      <VCardText>
        <VDataTable
          :headers="headers"
          :items="grouped"
          :loading="loading"
          item-value="cashier_id"
        >
          <template #item.expected_cash="{ item }">
            {{ formatMoney(item.expected_cash) }}
          </template>
          <template #item.total_sales="{ item }">
            {{ formatMoney(item.total_sales) }}
          </template>
          <template #item.total_expense="{ item }">
            {{ formatMoney(item.total_expense) }}
          </template>
          <template #item.actions="{ item }">
            <VBtn
              v-if="item.cashier_id !== 'unknown'"
              size="small"
              variant="text"
              @click="viewCashierSessions(item.cashier_id)"
            >
              Ver historial
            </VBtn>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </div>
</template>
