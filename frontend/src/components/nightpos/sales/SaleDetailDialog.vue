<script setup>
import { fetchSale } from '@/api/sales'
import { formatCompanionBraceletLine, formatMoney, SALE_MODE_LABELS, shouldShowCompanionBraceletLine } from '@/composables/useOrderHelpers'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: Boolean,
  saleId: {
    type: Number,
    default: null,
  },
  userName: {
    type: Function,
    default: id => (id ? `#${id}` : '—'),
  },
})

const emit = defineEmits(['update:modelValue'])

const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()

const sale = ref(null)
const loading = ref(false)

const PAYMENT_LABELS = {
  CASH: 'Efectivo',
  QR: 'QR',
  CARD: 'Tarjeta',
  MIXED: 'Mixto',
}

const PAYMENT_CHIP_COLOR = {
  CASH: 'success',
  QR: 'info',
  CARD: 'primary',
  MIXED: 'warning',
}

const itemHeaders = [
  { title: 'Producto', key: 'product_name_snapshot' },
  { title: 'Modalidad', key: 'sale_mode' },
  { title: 'Cant.', key: 'quantity', width: '80px' },
  { title: 'P. unitario', key: 'unit_price_snapshot' },
  { title: 'Total línea', key: 'line_total' },
  { title: 'Chica', key: 'girl_user_id' },
  { title: 'Com. garzón', key: 'waiter_commission_amount_snapshot' },
]

const paymentHeaders = [
  { title: 'Método', key: 'payment_method' },
  { title: 'Monto', key: 'amount' },
]

const open = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v),
})

const formatDateTime = value => {
  if (!value)
    return '—'

  try {
    return new Date(value).toLocaleString('es-BO', {
      dateStyle: 'short',
      timeStyle: 'short',
    })
  }
  catch {
    return value
  }
}

watch(
  () => [open.value, props.saleId],
  async ([isOpen, id]) => {
    if (!isOpen || !id) {
      sale.value = null

      return
    }

    loading.value = true

    try {
      sale.value = await fetchSale(id)
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
      sale.value = null
    }
    finally {
      loading.value = false
    }
  },
)
</script>

<template>
  <VDialog
    v-model="open"
    max-width="960"
    scrollable
  >
    <VCard>
      <VCardTitle class="d-flex flex-wrap align-center gap-2">
        <span>Detalle de venta</span>
        <VChip
          v-if="sale?.sale_number"
          label
          color="primary"
          size="small"
        >
          {{ sale.sale_number }}
        </VChip>
        <VSpacer />
        <VBtn
          icon="ri-close-line"
          variant="text"
          @click="open = false"
        />
      </VCardTitle>

      <VDivider />

      <VCardText>
        <VProgressLinear
          v-if="loading"
          indeterminate
          color="primary"
          class="mb-4"
        />

        <template v-else-if="sale">
          <VRow class="mb-4">
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                Comanda
              </p>
              <p class="text-body-1 font-weight-medium mb-0">
                #{{ sale.order_id }}
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                Cajera / Garzón
              </p>
              <p class="text-body-2 mb-0">
                {{ userName(sale.cashier_user_id) }} · {{ userName(sale.waiter_user_id) }}
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                Fecha y hora
              </p>
              <p class="text-body-2 mb-0">
                {{ formatDateTime(sale.paid_at) }}
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                Método de pago
              </p>
              <VChip
                :color="PAYMENT_CHIP_COLOR[sale.payment_mode] || 'secondary'"
                label
                size="small"
              >
                {{ PAYMENT_LABELS[sale.payment_mode] || sale.payment_mode }}
              </VChip>
            </VCol>
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                Total
              </p>
              <p class="text-h6 text-primary mb-0">
                {{ formatMoney(sale.total, sale.currency) }}
              </p>
            </VCol>
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                Estado
              </p>
              <VChip
                color="success"
                label
                size="small"
              >
                {{ sale.status === 'PAID' ? 'Pagada' : sale.status }}
              </VChip>
            </VCol>
          </VRow>

          <p class="text-subtitle-1 mb-2">
            Ítems vendidos
          </p>
          <VDataTable
            :headers="itemHeaders"
            :items="sale.items ?? []"
            density="compact"
            :items-per-page="-1"
            hide-default-footer
            class="mb-6 text-no-wrap"
          >
            <template #item.sale_mode="{ item }">
              <VChip
                size="x-small"
                variant="tonal"
                :color="item.sale_mode === 'CON_ACOMPANANTE' ? 'warning' : 'secondary'"
              >
                {{ SALE_MODE_LABELS[item.sale_mode] || item.sale_mode }}
              </VChip>
            </template>
            <template #item.unit_price_snapshot="{ item }">
              {{ formatMoney(item.unit_price_snapshot, sale.currency) }}
            </template>
            <template #item.line_total="{ item }">
              {{ formatMoney(item.line_total, sale.currency) }}
            </template>
            <template #item.girl_user_id="{ item }">
              <span v-if="shouldShowCompanionBraceletLine(item)">
                {{ formatCompanionBraceletLine(item) }}
                <span
                  v-if="item.girl_amount_snapshot"
                  class="text-caption d-block"
                >
                  Chica: {{ formatMoney(item.girl_amount_snapshot, sale.currency) }}
                </span>
              </span>
              <span v-else-if="item.sale_mode === 'CON_ACOMPANANTE' && item.requires_allocation">
                —
              </span>
              <span v-else>—</span>
            </template>
            <template #item.waiter_commission_amount_snapshot="{ item }">
              <template v-if="item.waiter_commission_amount_snapshot != null">
                {{ formatMoney(item.waiter_commission_amount_snapshot, sale.currency) }}
                <span
                  v-if="item.waiter_commission_percent_snapshot != null"
                  class="text-caption d-block"
                >
                  {{ item.waiter_commission_percent_snapshot }}%
                </span>
              </template>
              <span v-else>—</span>
            </template>
            <template #no-data>
              Sin ítems en esta venta.
            </template>
          </VDataTable>

          <p
            v-if="sale.payments?.length"
            class="text-subtitle-1 mb-2"
          >
            Desglose de pago
          </p>
          <VDataTable
            v-if="sale.payments?.length"
            :headers="paymentHeaders"
            :items="sale.payments"
            density="compact"
            :items-per-page="-1"
            hide-default-footer
          >
            <template #item.payment_method="{ item }">
              <VChip
                size="small"
                label
                :color="PAYMENT_CHIP_COLOR[item.payment_method] || 'secondary'"
              >
                {{ PAYMENT_LABELS[item.payment_method] || item.payment_method }}
              </VChip>
            </template>
            <template #item.amount="{ item }">
              {{ formatMoney(item.amount, sale.currency) }}
            </template>
          </VDataTable>
        </template>

        <VAlert
          v-else-if="!loading"
          type="info"
          variant="tonal"
        >
          No se pudo cargar el detalle de la venta.
        </VAlert>
      </VCardText>

      <VCardActions>
        <VBtn
          v-if="sale"
          color="primary"
          variant="tonal"
          prepend-icon="ri-printer-line"
          @click="openPrintRoute({ name: 'nightpos-print-sale-id', params: { id: sale.id } })"
        >
          Ver ticket
        </VBtn>
        <VSpacer />
        <VBtn @click="open = false">
          Cerrar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
