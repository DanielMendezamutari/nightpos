<script setup>
import SettlementsCashBanner from '@/components/nightpos/settlements/SettlementsCashBanner.vue'
import SettlementPayDialog from '@/components/nightpos/settlements/SettlementPayDialog.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { useCurrentShiftSettlements } from '@/composables/useCurrentShiftSettlements'
import { useFilteredSettlementTabs } from '@/composables/useSettlementSectionTabs'
import { useSettlementPayment } from '@/composables/useSettlementPayment'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

definePage({ meta: { permission: 'settlements.access' } })

const settlementTabs = useFilteredSettlementTabs()
const router = useRouter()
const { can } = useNightPosPermissions()
const { loading, shift, cleaning, reload } = useCurrentShiftSettlements()
const { paySettlement, showOpenCash, refreshCashSession } = useSettlementPayment({ onPaid: reload })

const canPay = computed(() => can('settlements.pay'))
const paying = ref(false)
const showPayDialog = ref(false)
const payingItem = ref(null)

const headers = [
  { title: 'Personal limpieza', key: 'staff_name' },
  { title: 'Corte', key: 'cut_label' },
  { title: 'Base', key: 'cleaning_base_total' },
  { title: 'Piezas limpias', key: 'cleaning_rooms_count' },
  { title: 'Pago por pieza', key: 'cleaning_room_rate' },
  { title: 'Total piezas', key: 'cleaning_rooms_total' },
  { title: 'Total a pagar', key: 'total_amount' },
  { title: 'Estado', key: 'status' },
  { title: 'Generado', key: 'created_at' },
  { title: 'Pagado', key: 'paid_at' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const statusColor = status => ({
  PENDING: 'warning',
  PAID: 'success',
  CANCELLED: 'secondary',
}[status] || 'default')

const openPayDialog = async item => {
  await refreshCashSession()
  payingItem.value = item
  showPayDialog.value = true
}

const confirmPay = async ({ payment_method, notes }) => {
  if (!payingItem.value)
    return
  paying.value = true
  try {
    const result = await paySettlement(payingItem.value.id, { payment_method, notes })
    if (result.ok) {
      showPayDialog.value = false
      payingItem.value = null
    }
  }
  finally {
    paying.value = false
  }
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Liquidaciones — Limpieza"
      subtitle="Base por turno y pago por pieza limpiada."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Limpieza', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="settlementTabs" />

    <SettlementsCashBanner emphasize-pay-requirement />

    <VAlert
      v-if="!loading && !shift"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Sin turno clasificado. Marque piezas como limpias y genere liquidaciones desde el resumen.
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="cleaning"
        :items-per-page="15"
        class="text-no-wrap"
      >
        <template #item.status="{ item }">
          <VChip
            size="small"
            :color="statusColor(item.status)"
            variant="tonal"
          >
            {{ item.status === 'PENDING' ? 'Pendiente' : item.status === 'PAID' ? 'Pagado' : item.status }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              v-if="canPay && item.status === 'PENDING'"
              size="small"
              color="success"
              variant="tonal"
              prepend-icon="ri-check-line"
              @click="openPayDialog(item)"
            >
              Pagar
            </VBtn>
            <VBtn
              size="small"
              variant="text"
              @click="router.push({ name: 'nightpos-settlements-id', params: { id: item.id } })"
            >
              Ver detalle
            </VBtn>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <SettlementPayDialog
      v-model="showPayDialog"
      :settlement="payingItem"
      title="Confirmar pago limpieza"
      type-label="Limpieza"
      :loading="paying"
      @confirm="confirmPay"
    />

    <QuickOpenCashDialog v-model="showOpenCash" @opened="refreshCashSession" />
  </div>
</template>
