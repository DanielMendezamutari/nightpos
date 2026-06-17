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
import { useOperationalEvents } from '@/composables/useOperationalEvents'

definePage({ meta: { permission: 'settlements.access' } })

const settlementTabs = useFilteredSettlementTabs()
const router = useRouter()
const { can } = useNightPosPermissions()
const { loading, shift, girls, reload } = useCurrentShiftSettlements()
const { paySettlement, showOpenCash, refreshCashSession } = useSettlementPayment({ onPaid: reload })

const { on, start: startSse, stop: stopSse } = useOperationalEvents()

let settlementDebounce = null
const debouncedReload = () => {
  clearTimeout(settlementDebounce)
  settlementDebounce = setTimeout(reload, 600)
}

on('settlement.generated', debouncedReload)
on('settlement.paid', debouncedReload)

onMounted(() => { startSse() })
onUnmounted(() => { stopSse() })

const canPay = computed(() => can('settlements.pay'))
const paying = ref(false)
const showPayDialog = ref(false)
const payingItem = ref(null)

const headers = [
  { title: 'Chica', key: 'staff_name' },
  { title: 'Corte', key: 'cut_label' },
  { title: 'Total', key: 'total_amount' },
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
      title="Liquidaciones — Chicas"
      subtitle="Liquidación del turno por chica."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Chicas', disabled: true },
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
      Sin turno clasificado. Genere liquidaciones desde el resumen cuando haya ventas cobradas.
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="girls"
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
      title="Confirmar pago chica"
      type-label="Chica"
      :loading="paying"
      @confirm="confirmPay"
    />

    <QuickOpenCashDialog v-model="showOpenCash" @opened="refreshCashSession" />
  </div>
</template>
