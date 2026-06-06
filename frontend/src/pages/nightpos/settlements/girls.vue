<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { useCurrentShiftSettlements } from '@/composables/useCurrentShiftSettlements'
import { useFilteredSettlementTabs } from '@/composables/useSettlementSectionTabs'
import { markSettlementPaid } from '@/api/settlements'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settlements.access' } })

const settlementTabs = useFilteredSettlementTabs()
const router = useRouter()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { loading, shift, girls, reload } = useCurrentShiftSettlements()

const canPay = computed(() => can('settlements.pay'))
const paying = ref(false)
const showPayDialog = ref(false)
const payingItem = ref(null)

const headers = [
  { title: 'Chica', key: 'staff_name' },
  { title: 'Consumos', key: 'consumption_total' },
  { title: 'Manillas', key: 'bracelets_total' },
  { title: 'Piezas', key: 'pieces_total' },
  { title: 'Shows', key: 'shows_total' },
  { title: 'Total', key: 'total_amount' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const statusColor = status => ({
  PENDING: 'warning',
  PAID: 'success',
  CANCELLED: 'secondary',
}[status] || 'default')

const openPayDialog = item => {
  payingItem.value = item
  showPayDialog.value = true
}

const confirmPay = async () => {
  if (!payingItem.value)
    return
  paying.value = true
  try {
    await markSettlementPaid(payingItem.value.id)
    notify('Liquidación marcada como pagada. Egreso registrado en caja.', 'success')
    showPayDialog.value = false
    payingItem.value = null
    await reload()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
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
      subtitle="Consumos con acompañante, manillas, piezas y shows del turno."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Chicas', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="settlementTabs" />

    <VAlert
      v-if="!loading && !shift"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Sin turno clasificado. Registre servicios o cobre ventas CON_ACOMPANANTE y genere desde el resumen.
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

    <!-- Confirmación pago -->
    <VDialog
      v-model="showPayDialog"
      max-width="420"
    >
      <VCard title="Confirmar pago chica">
        <VCardText>
          <p class="text-body-2 mb-3">
            Pagar liquidación de <strong>{{ payingItem?.staff_name }}</strong>.
          </p>
          <VAlert
            type="info"
            variant="tonal"
            density="compact"
            class="mb-0"
          >
            Se registrará un <strong>egreso de {{ payingItem?.total_amount }} BOB</strong>
            en su caja abierta. El efectivo esperado bajará automáticamente.
          </VAlert>
        </VCardText>
        <VCardActions>
          <VBtn
            variant="text"
            @click="showPayDialog = false"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            color="success"
            :loading="paying"
            :disabled="paying"
            @click="confirmPay"
          >
            Confirmar pago
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
