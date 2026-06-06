<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { useAdminCashSessionsList } from '@/composables/useAdminCashSessionsList'
import { useFilteredCashSessionTabs } from '@/composables/useCashSessionSectionTabs'
import { formatMoney } from '@/composables/useOrderHelpers'

definePage({ meta: { permission: 'admin.cash_sessions.summary' } })

const cashSessionTabs = useFilteredCashSessionTabs()

const {
  summaryLoading,
  summary,
  filters,
  shifts,
  cashiers,
  loadSummary,
} = useAdminCashSessionsList(null)

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Cajas abiertas', color: 'success', icon: 'ri-safe-2-line', value: s.total_open_sessions ?? 0 },
    { title: 'Cajas cerradas', color: 'secondary', icon: 'ri-lock-line', value: s.total_closed_sessions ?? 0 },
    { title: 'Efectivo esperado', color: 'primary', icon: 'ri-money-dollar-circle-line', value: `${formatMoney(s.expected_cash_total)} BOB` },
    { title: 'QR total', color: 'info', icon: 'ri-qr-code-line', value: `${formatMoney(s.total_qr)} BOB` },
    { title: 'Tarjeta total', color: 'warning', icon: 'ri-bank-card-line', value: `${formatMoney(s.total_card)} BOB` },
    { title: 'Diferencia total', color: 'error', icon: 'ri-scales-line', value: `${formatMoney(s.total_difference)} BOB` },
    { title: 'Ventas totales', color: 'success', icon: 'ri-shopping-bag-line', value: `${formatMoney(s.total_sales)} BOB` },
    { title: 'Egresos totales', color: 'error', icon: 'ri-arrow-down-circle-line', value: `${formatMoney(s.total_expenses)} BOB` },
  ]
})

const applyFilters = () => loadSummary()

const resetFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
    official_shift_id: null,
    cashier_user_id: null,
    status: null,
  }
  loadSummary()
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Resumen de cajas"
      subtitle="Totales de fiscalización según filtros aplicados."
      :breadcrumbs="[
        { title: 'Finanzas' },
        { title: 'Fiscalización de cajas', to: { name: 'nightpos-finance-cash-sessions' } },
        { title: 'Resumen' },
      ]"
    />

    <NightPosSectionTabs :tabs="cashSessionTabs" />

    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="filters.date_from"
              label="Desde"
              type="date"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="filters.date_to"
              label="Hasta"
              type="date"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.cashier_user_id"
              :items="cashiers"
              item-title="name"
              item-value="id"
              label="Cajera"
              clearable
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.official_shift_id"
              :items="shifts"
              :item-title="s => `${s.shift_type} · ${s.business_date}`"
              item-value="id"
              label="Turno"
              clearable
              density="compact"
            />
          </VCol>
        </VRow>
        <div class="d-flex gap-2 mt-2">
          <VBtn
            color="primary"
            :loading="summaryLoading"
            @click="applyFilters"
          >
            Actualizar resumen
          </VBtn>
          <VBtn
            variant="tonal"
            @click="resetFilters"
          >
            Limpiar
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <VRow>
      <VCol
        v-for="card in summaryCards"
        :key="card.title"
        cols="12"
        sm="6"
        md="3"
      >
        <VCard :loading="summaryLoading">
          <VCardText>
            <div class="d-flex align-center gap-3">
              <VAvatar
                :color="card.color"
                variant="tonal"
                size="42"
              >
                <VIcon :icon="card.icon" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis">
                  {{ card.title }}
                </div>
                <div class="text-h6">
                  {{ card.value }}
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
