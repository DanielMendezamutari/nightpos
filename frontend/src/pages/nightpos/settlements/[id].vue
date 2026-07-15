<script setup>
import SettlementPaymentBreakdown from '@/components/nightpos/settlements/SettlementPaymentBreakdown.vue'
import SettlementsCashBanner from '@/components/nightpos/settlements/SettlementsCashBanner.vue'
import SettlementPayDialog from '@/components/nightpos/settlements/SettlementPayDialog.vue'
import SettlementManualDiscountDialog from '@/components/nightpos/settlements/SettlementManualDiscountDialog.vue'
import SettlementAdjustmentSummary from '@/components/nightpos/settlements/SettlementAdjustmentSummary.vue'
import StaffFineDialog from '@/components/nightpos/settlements/StaffFineDialog.vue'
import StaffFinesList from '@/components/nightpos/settlements/StaffFinesList.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchSettlement, updateSettlementCleaningDeduction } from '@/api/settlements'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useSettlementPayment } from '@/composables/useSettlementPayment'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { paymentMethodLabel } from '@/constants/paymentMethods'
import {
  formatBob,
  settlementTypeLabel,
  sourceTypeLabel,
} from '@/constants/settlements'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settlements.access' } })

const route = useRoute('nightpos-settlements-id')
const router = useRouter()
const { can, canManageSettlementFines } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(true)
const paying = ref(false)
const reprintLoading = ref(false)
const showPayDialog = ref(false)
const showFineDialog = ref(false)
const showDiscountDialog = ref(false)
const settlement = ref(null)
const savingCleaning = ref(false)
const cleaningAmountInput = ref('0.00')
const payDialogRef = ref(null)
const items = ref([])
const adjustments = ref([])
const finesListRef = ref(null)
const lastPaidResult = ref(null)

async function load() {
  loading.value = true

  try {
    const data = await fetchSettlement(route.params.id)

    settlement.value = data.settlement
    items.value = data.items ?? []
    adjustments.value = data.adjustments ?? []
    await refreshCashSession()
  }
  catch (error) {
    if (import.meta.env.DEV)
      console.error('[settlements/:id]', error?.response?.status, error?.response?.data?.message ?? error)

    notify(getApiErrorMessage(error), 'error')
    router.replace({ name: 'nightpos-settlements' })
  }
  finally {
    loading.value = false
  }
}

const {
  cashSessionOpen,
  loadingCash,
  showOpenCash,
  paySettlement,
  refreshCashSession,
  openReceipt,
  reprintReceipt,
} = useSettlementPayment({ onPaid: load })

const isWaiter = computed(() => settlement.value?.settlement_type === 'WAITER')
const canPayPending = computed(() => settlement.value?.status === 'PENDING' && can('settlements.pay'))
const canAddFine = computed(() => settlement.value?.status === 'PENDING' && canManageSettlementFines.value)
const canAddDiscount = computed(() => settlement.value?.status === 'PENDING' && canManageSettlementFines.value)
const canReprint = computed(() => settlement.value?.status === 'PAID' && can('settlements.pay'))
const canEditCleaning = computed(() => settlement.value?.status === 'PENDING' && settlement.value?.settlement_type === 'GIRL' && can('settlements.pay'))
const settlementTypeLabelText = computed(() => settlementTypeLabel(settlement.value?.settlement_type))
const requiresCashToPay = computed(() => canPayPending.value && !loadingCash.value && !cashSessionOpen.value)

const currentCleaningAdjustment = computed(() =>
  adjustments.value.find(row => row.adjustment_type === 'CLEANING_DEDUCTION'))

const currentCleaningAmount = computed(() => {
  const amount = Number(currentCleaningAdjustment.value?.amount ?? 0)

  return amount < 0 ? Math.abs(amount) : amount
})

const paymentBreakdownAdjustments = computed(() => summaryAdjustments.value)

watch(currentCleaningAmount, value => {
  cleaningAmountInput.value = value.toFixed(2)
}, { immediate: true })

const summaryAdjustments = computed(() => {
  if (settlement.value?.status === 'PAID')
    return adjustments.value

  return adjustments.value.filter(row => row.adjustment_type !== 'MANUAL_FINE')
})

const appliedFineAdjustments = computed(() =>
  adjustments.value.filter(row => row.adjustment_type === 'MANUAL_FINE'))

const itemHeaders = computed(() => {
  if (isWaiter.value) {
    return [
      { title: 'Venta', key: 'sale_number' },
      { title: 'Comanda', key: 'order_number' },
      { title: 'Hora', key: 'registered_at' },
      { title: 'Pago', key: 'payment_method' },
      { title: 'Descripción', key: 'description' },
      { title: 'Base', key: 'base_amount' },
      { title: '%', key: 'percent' },
      { title: 'Comisión', key: 'amount' },
    ]
  }

  return [
    { title: 'Fuente', key: 'source_type' },
    { title: 'Descripción', key: 'description' },
    { title: 'Monto', key: 'amount' },
    { title: 'Hora', key: 'registered_at' },
  ]
})

const waiterSalesCount = computed(() => Number(settlement.value?.sales_count ?? 0))

const waiterSalesTotal = computed(() =>
  settlement.value?.sales_total_amount
  ?? settlement.value?.waiter_sales_total
  ?? '0.00')

const formatBobAmount = amount => {
  if (amount === null || amount === undefined || amount === '')
    return '—'

  const numeric = Number(amount)

  if (Number.isNaN(numeric))
    return `${amount} BOB`

  return `${new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(numeric)} BOB`
}

const openPayDialog = async () => {
  await refreshCashSession()

  if (!cashSessionOpen.value) {
    showOpenCash.value = true

    return
  }

  showPayDialog.value = true
}

const confirmPay = async ({ payment_method, notes, applied_fine_ids }) => {
  paying.value = true

  try {
    const result = await paySettlement(route.params.id, {
      payment_method,
      notes,
      applied_fine_ids,
    })

    if (result.ok) {
      lastPaidResult.value = result.data
      settlement.value = result.data.settlement
      showPayDialog.value = false
      await load()
    }
  }
  finally {
    paying.value = false
  }
}

const handleReprint = async () => {
  reprintLoading.value = true

  try {
    await reprintReceipt(route.params.id)
    await load()
  }
  finally {
    reprintLoading.value = false
  }
}

const onFineChanged = async () => {
  await finesListRef.value?.reload?.()
  if (settlement.value?.status === 'PENDING')
    await load()
  await payDialogRef.value?.reloadPreview?.()
}

const openFineFromPay = () => {
  showFineDialog.value = true
}

const onDiscountChanged = async () => {
  await load()
}

const onCashOpened = async () => {
  await refreshCashSession()
  await load()
}

const saveCleaning = async () => {
  if (!settlement.value?.id)
    return

  const amount = Number(cleaningAmountInput.value ?? 0)

  if (Number.isNaN(amount) || amount < 0) {
    notify('Ingrese un monto de limpieza válido.', 'error')
    return
  }

  savingCleaning.value = true

  try {
    const result = await updateSettlementCleaningDeduction(settlement.value.id, { amount })
    notify(result?.message || 'Cobro de limpieza guardado.', 'success')
    await load()
    await payDialogRef.value?.reloadPreview?.()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    savingCleaning.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="settlement?.staff_name || 'Detalle liquidación'"
      :subtitle="settlement ? `${settlement.cut_label || ''}${settlement.cut_label ? ' · ' : ''}${settlement.settlement_type} · ${formatBob(settlement.net_amount ?? settlement.total_amount)}` : ''"
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Detalle', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canAddDiscount"
          variant="tonal"
          prepend-icon="ri-percent-line"
          class="me-2"
          @click="showDiscountDialog = true"
        >
          Agregar descuento
        </VBtn>

        <VBtn
          v-if="canAddFine"
          color="warning"
          variant="flat"
          prepend-icon="ri-error-warning-line"
          class="me-2"
          @click="showFineDialog = true"
        >
          Agregar multa
        </VBtn>

        <VBtn
          v-if="canReprint"
          variant="tonal"
          prepend-icon="ri-printer-line"
          class="me-2"
          :loading="reprintLoading"
          @click="handleReprint"
        >
          Reimprimir ticket
        </VBtn>

        <VBtn
          v-if="settlement?.status === 'PAID'"
          variant="tonal"
          prepend-icon="ri-file-text-line"
          class="me-2"
          @click="openReceipt(settlement.id)"
        >
          Ver comprobante
        </VBtn>

        <VBtn
          v-if="canPayPending"
          color="success"
          prepend-icon="ri-check-line"
          @click="openPayDialog"
        >
          Marcar pagado
        </VBtn>
      </template>
    </NightPosPageHeader>

    <SettlementsCashBanner
      emphasize-pay-requirement
      @cash-opened="onCashOpened"
    />

    <VAlert
      v-if="requiresCashToPay"
      type="warning"
      variant="tonal"
      class="mb-4"
      prominent
    >
      Debe abrir caja para pagar esta liquidación.
    </VAlert>

    <VAlert
      v-if="lastPaidResult?.ticket_number"
      type="success"
      variant="tonal"
      class="mb-4"
      prominent
    >
      Liquidación pagada — Ticket <strong>{{ lastPaidResult.ticket_number }}</strong>
      <div class="mt-2 d-flex flex-wrap gap-2">
        <VBtn
          size="small"
          variant="tonal"
          prepend-icon="ri-printer-line"
          @click="openReceipt(route.params.id)"
        >
          Ver comprobante
        </VBtn>
      </div>
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else-if="settlement">
      <VCard
        v-if="settlement.status === 'PENDING' && (canPayPending || canAddFine)"
        variant="tonal"
        color="warning"
        class="mb-4"
      >
        <VCardText class="d-flex flex-wrap gap-3 align-center justify-space-between py-4">
          <div>
            <div class="text-subtitle-1 font-weight-medium">
              Acciones de liquidación
            </div>
            <div class="text-body-2 text-medium-emphasis">
              {{ settlement.staff_name }} · Neto {{ formatBob(settlement.net_amount ?? settlement.total_amount) }}
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <VBtn
              v-if="canAddFine"
              color="warning"
              variant="flat"
              prepend-icon="ri-error-warning-line"
              @click="showFineDialog = true"
            >
              Agregar multa
            </VBtn>
            <VBtn
              v-if="canPayPending"
              color="success"
              prepend-icon="ri-check-line"
              @click="openPayDialog"
            >
              Marcar pagado
            </VBtn>
          </div>
        </VCardText>
      </VCard>

      <VCard
        v-if="isWaiter"
        variant="outlined"
        class="mb-4"
      >
        <VCardTitle class="text-subtitle-1">
          Venta garzón
        </VCardTitle>
        <VCardText>
          <VRow dense>
            <VCol
              cols="12"
              sm="3"
            >
              <p class="text-caption mb-1">
                Cant. ventas
              </p>
              <p class="text-h6 mb-0">
                {{ waiterSalesCount }}
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="3"
            >
              <p class="text-caption mb-1">
                Total vendido
              </p>
              <p class="text-h6 mb-0">
                {{ formatBobAmount(waiterSalesTotal) }}
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="3"
            >
              <p class="text-caption mb-1">
                Porcentaje
              </p>
              <p class="text-h6 mb-0">
                {{ settlement.commission_percent ?? '—' }}%
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="3"
            >
              <p class="text-caption mb-1">
                Comisión
              </p>
              <p class="text-h6 mb-0">
                {{ formatBob(settlement.commission_amount ?? settlement.gross_amount) }}
              </p>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <VRow class="mb-4">
        <VCol
          cols="12"
          md="3"
        >
          <VCard variant="tonal">
            <VCardText>
              <p class="text-caption mb-1">
                Estado
              </p>
              <VChip
                :color="settlement.status === 'PAID' ? 'success' : 'warning'"
                variant="tonal"
              >
                {{ settlement.status }}
              </VChip>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="3"
        >
          <VCard variant="tonal">
            <VCardText>
              <p class="text-caption mb-1">
                Neto
              </p>
              <p class="text-h5 mb-0">
                {{ formatBob(settlement.net_amount ?? settlement.total_amount) }}
              </p>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="3"
        >
          <VCard variant="tonal">
            <VCardText>
              <p class="text-caption mb-1">
                Ticket
              </p>
              <p class="text-body-1 mb-0">
                {{ settlement.ticket_number || '—' }}
              </p>
              <p
                v-if="settlement.print_count"
                class="text-caption mb-0"
              >
                Reimpresiones: {{ settlement.print_count }}
              </p>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          v-if="settlement.paid_at"
          cols="12"
          md="3"
        >
          <VCard variant="tonal">
            <VCardText>
              <p class="text-caption mb-1">
                Pago
              </p>
              <p class="text-body-2 mb-0">
                {{ settlement.paid_by_name || '—' }}
              </p>
              <p class="text-caption mb-0">
                {{ settlement.paid_at }}
              </p>
              <p
                v-if="settlement.payment_method"
                class="text-caption mb-0"
              >
                {{ paymentMethodLabel(settlement.payment_method) }}
              </p>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VRow
        v-if="settlement.status === 'PAID'"
        class="mb-4"
      >
        <VCol
          cols="12"
          md="4"
        >
          <VCard variant="outlined">
            <VCardText>
              <p class="text-caption mb-1">
                Caja
              </p>
              <p class="mb-0">
                #{{ settlement.cash_session_id || '—' }}
              </p>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <VCard variant="outlined">
            <VCardText>
              <p class="text-caption mb-1">
                Movimiento caja
              </p>
              <p class="mb-0">
                #{{ settlement.cash_movement_id || '—' }}
              </p>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <VCard variant="outlined">
            <VCardText>
              <p class="text-caption mb-1">
                Bruto / Ajustes
              </p>
              <p class="mb-0">
                {{ formatBob(settlement.gross_amount) }} / {{ formatBob(settlement.adjustments_total) }}
              </p>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <SettlementAdjustmentSummary
        v-if="canEditCleaning"
        class="mb-4"
        :gross-amount="settlement.gross_amount ?? settlement.total_amount"
        :net-amount="settlement.net_amount ?? settlement.total_amount"
        :adjustments="paymentBreakdownAdjustments"
        title="Detalle de ajustes"
        :show-net-highlight="false"
      />

      <VCard
        v-if="canEditCleaning"
        class="mb-4"
        variant="outlined"
      >
        <VCardTitle class="text-subtitle-1 text-uppercase">
          Cobro de limpieza
        </VCardTitle>
        <VCardText>
          <VRow class="mb-2">
            <VCol cols="12" md="4">
              <VTextField
                v-model="cleaningAmountInput"
                type="number"
                min="0"
                step="0.01"
                label="Monto"
                suffix="Bs"
                :disabled="savingCleaning"
              />
            </VCol>
            <VCol cols="12" md="8" class="d-flex align-center">
              <VBtn
                color="primary"
                prepend-icon="ri-save-line"
                :loading="savingCleaning"
                :disabled="savingCleaning"
                @click="saveCleaning"
              >
                Guardar limpieza
              </VBtn>
            </VCol>
          </VRow>

          <VAlert type="info" variant="tonal" class="mb-4">
            Si guarda <strong>0 Bs</strong>, se elimina cualquier cobro de limpieza previo de esta liquidación.
          </VAlert>

          <SettlementPaymentBreakdown
            :gross-amount="settlement.gross_amount ?? settlement.total_amount"
            :net-amount="settlement.net_amount ?? settlement.total_amount"
            :adjustments="paymentBreakdownAdjustments"
            title="Resumen de liquidación"
            gross-label="Bruto"
          />
        </VCardText>
      </VCard>

      <SettlementAdjustmentSummary
        v-if="!canEditCleaning"
        class="mb-4"
        :gross-amount="settlement.gross_amount ?? settlement.total_amount"
        :net-amount="settlement.net_amount ?? settlement.total_amount"
        :adjustments="summaryAdjustments"
        :show-net-highlight="settlement.status === 'PENDING'"
        :title="settlement.status === 'PAID' ? 'Resumen pagado' : 'Resumen de liquidación'"
      />

      <StaffFinesList
        v-if="settlement.status === 'PENDING'"
        ref="finesListRef"
        class="mb-4"
        title="Multas pendientes de esta persona"
        :staff-user-id="settlement.staff_user_id"
        status="PENDING"
        @changed="onFineChanged"
      />

      <VCard
        v-if="settlement.status === 'PAID' && appliedFineAdjustments.length"
        class="mb-4"
        variant="outlined"
      >
        <VCardTitle class="text-subtitle-1">
          Multas aplicadas al pagar
        </VCardTitle>
        <VCardText>
          <div
            v-for="fine in appliedFineAdjustments"
            :key="fine.id"
            class="d-flex justify-space-between mb-2"
          >
            <span>{{ fine.reason || fine.notes }}</span>
            <span>{{ formatBob(fine.amount) }}</span>
          </div>
        </VCardText>
      </VCard>

      <VCard>
        <VCardTitle>Líneas de liquidación</VCardTitle>
        <VDataTable
          :headers="itemHeaders"
          :items="items"
          :items-per-page="20"
          class="text-no-wrap"
        >
          <template
            v-if="!isWaiter"
            #item.source_type="{ item }"
          >
            <VChip
              size="small"
              variant="tonal"
            >
              {{ sourceTypeLabel(item.source_type) }}
            </VChip>
          </template>
          <template #item.description="{ item }">
            <div>
              <div>{{ item.display_description || item.description }}</div>
              <div
                v-if="item.units"
                class="text-caption text-medium-emphasis"
              >
                {{ item.units }} manilla(s) · {{ item.unit_amount }} c/u · {{ item.allocation_total_amount || item.amount }} BOB
              </div>
              <div
                v-if="item.product_name"
                class="text-caption text-medium-emphasis"
              >
                {{ item.product_name }}
                <span v-if="item.sale_number"> · {{ item.sale_number }}</span>
              </div>
            </div>
          </template>
          <template #item.registered_at="{ item }">
            {{ item.registered_at || item.created_at || '—' }}
          </template>
          <template #item.sale_number="{ item }">
            {{ item.sale_number || '—' }}
          </template>
          <template #item.order_number="{ item }">
            {{ item.order_number || '—' }}
          </template>
          <template #item.payment_method="{ item }">
            {{ item.payment_method || '—' }}
          </template>
        </VDataTable>
      </VCard>
    </template>

    <SettlementPayDialog
      ref="payDialogRef"
      v-model="showPayDialog"
      :settlement="settlement"
      title="Confirmar pago"
      :type-label="settlementTypeLabelText"
      :loading="paying"
      @confirm="confirmPay"
      @register-fine="openFineFromPay"
    />

    <StaffFineDialog
      v-model="showFineDialog"
      :staff-user-id="settlement?.staff_user_id"
      :staff-role="settlement?.staff_role"
      :staff-name="settlement?.staff_name"
      @created="onFineChanged"
    />

    <SettlementManualDiscountDialog
      v-model="showDiscountDialog"
      :settlement-id="settlement?.id"
      :staff-name="settlement?.staff_name"
      :gross-amount="settlement?.gross_amount"
      @created="onDiscountChanged"
    />

    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />
  </div>
</template>
