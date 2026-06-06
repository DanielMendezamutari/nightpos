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
    const key = session.official_shift?.id ?? 'unknown'
    const label = session.official_shift
      ? `${session.official_shift.shift_type} · ${session.official_shift.business_date}`
      : 'Sin turno'

    if (!map.has(key)) {
      map.set(key, {
        shift_id: key,
        shift_label: label,
        sessions_count: 0,
        open_count: 0,
        total_sales: 0,
        expected_cash: 0,
        total_qr: 0,
        total_card: 0,
      })
    }

    const row = map.get(key)

    row.sessions_count++
    if (session.status === 'OPEN') row.open_count++
    row.total_sales += Number(session.total_sales || 0)
    row.expected_cash += Number(session.expected_cash || 0)
    row.total_qr += Number(session.total_qr || 0)
    row.total_card += Number(session.total_card || 0)
  }

  return [...map.values()]
})

const headers = [
  { title: 'Turno', key: 'shift_label' },
  { title: 'Sesiones', key: 'sessions_count' },
  { title: 'Abiertas', key: 'open_count' },
  { title: 'Efectivo esperado', key: 'expected_cash' },
  { title: 'QR', key: 'total_qr' },
  { title: 'Tarjeta', key: 'total_card' },
  { title: 'Ventas', key: 'total_sales' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const viewShiftSessions = shiftId => {
  router.push({
    name: 'nightpos-finance-cash-sessions-history',
    query: { official_shift_id: shiftId },
  })
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Cajas por turno"
      subtitle="Agregado de sesiones agrupadas por turno oficial."
      :breadcrumbs="[
        { title: 'Finanzas' },
        { title: 'Fiscalización de cajas', to: { name: 'nightpos-finance-cash-sessions' } },
        { title: 'Por turno' },
      ]"
    />

    <NightPosSectionTabs :tabs="cashSessionTabs" />

    <VCard>
      <VCardText>
        <VDataTable
          :headers="headers"
          :items="grouped"
          :loading="loading"
          item-value="shift_id"
        >
          <template #item.expected_cash="{ item }">
            {{ formatMoney(item.expected_cash) }}
          </template>
          <template #item.total_qr="{ item }">
            {{ formatMoney(item.total_qr) }}
          </template>
          <template #item.total_card="{ item }">
            {{ formatMoney(item.total_card) }}
          </template>
          <template #item.total_sales="{ item }">
            {{ formatMoney(item.total_sales) }}
          </template>
          <template #item.actions="{ item }">
            <VBtn
              v-if="item.shift_id !== 'unknown'"
              size="small"
              variant="text"
              @click="viewShiftSessions(item.shift_id)"
            >
              Ver historial
            </VBtn>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </div>
</template>
