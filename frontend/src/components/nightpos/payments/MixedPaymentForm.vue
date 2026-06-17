<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'
import { useMixedPayments } from '@/composables/useMixedPayments'
import { preventNumberWheelScroll } from '@/composables/usePreventNumberWheel'

const props = defineProps({
  total: { type: Number, default: 0 },
  currency: { type: String, default: 'BOB' },
  disabled: { type: Boolean, default: false },
  showQuickButtons: { type: Boolean, default: true },
})

const totalRef = computed(() => props.total)

const {
  form,
  paymentsSum,
  cashPortion,
  changeAmount,
  remaining,
  overpaid,
  isBalanced,
  setAllCash,
  setAllQr,
  setAllCard,
  clearAmounts,
  reset,
  validate,
  toPayload,
} = useMixedPayments(totalRef)

defineExpose({
  reset,
  validate,
  toPayload,
})

const fmt = amount => formatMoney(amount, props.currency)
</script>

<template>
  <div class="mixed-payment-form">
    <p class="text-subtitle-2 font-weight-medium mb-2">
      Métodos de pago
    </p>
    <p class="text-caption text-medium-emphasis mb-3">
      Ingrese el monto en cada método. No necesita seleccionar el método por separado.
    </p>

    <VTextField
      v-model.number="form.cashAmount"
      type="number"
      label="Efectivo"
      min="0"
      step="0.01"
      density="compact"
      class="mb-2"
      :disabled="disabled"
      @wheel="preventNumberWheelScroll"
    />
    <VTextField
      v-model.number="form.qrAmount"
      type="number"
      label="QR"
      min="0"
      step="0.01"
      density="compact"
      class="mb-2"
      :disabled="disabled"
      @wheel="preventNumberWheelScroll"
    />
    <VTextField
      v-model.number="form.cardAmount"
      type="number"
      label="Tarjeta"
      min="0"
      step="0.01"
      density="compact"
      class="mb-2"
      :disabled="disabled"
      @wheel="preventNumberWheelScroll"
    />

    <div class="text-body-2 mb-3">
      <div class="d-flex justify-space-between">
        <span class="text-medium-emphasis">Total ingresado</span>
        <span :class="isBalanced ? 'text-success font-weight-medium' : ''">
          {{ fmt(paymentsSum) }}
        </span>
      </div>
      <div
        v-if="remaining > 0.01"
        class="d-flex justify-space-between text-warning"
      >
        <span>Faltante</span>
        <span class="font-weight-medium">{{ fmt(remaining) }}</span>
      </div>
      <div
        v-else-if="overpaid"
        class="d-flex justify-space-between text-error"
      >
        <span>Excedente</span>
        <span class="font-weight-medium">{{ fmt(paymentsSum - total) }}</span>
      </div>
      <div
        v-else-if="isBalanced"
        class="d-flex justify-space-between text-success"
      >
        <span>Faltante</span>
        <span class="font-weight-medium">{{ fmt(0) }}</span>
      </div>
    </div>

    <div
      v-if="showQuickButtons"
      class="d-flex flex-wrap gap-1 mb-3"
    >
      <VBtn
        size="small"
        variant="tonal"
        :disabled="disabled || total <= 0"
        @click="setAllCash"
      >
        Todo efectivo
      </VBtn>
      <VBtn
        size="small"
        variant="tonal"
        :disabled="disabled || total <= 0"
        @click="setAllQr"
      >
        Todo QR
      </VBtn>
      <VBtn
        size="small"
        variant="tonal"
        :disabled="disabled || total <= 0"
        @click="setAllCard"
      >
        Todo tarjeta
      </VBtn>
      <VBtn
        size="small"
        variant="text"
        :disabled="disabled"
        @click="clearAmounts"
      >
        Limpiar
      </VBtn>
    </div>

    <VTextField
      v-if="cashPortion > 0"
      v-model.number="form.receivedAmount"
      type="number"
      label="Monto recibido (efectivo)"
      min="0"
      step="0.01"
      density="compact"
      class="mb-2"
      inputmode="decimal"
      :disabled="disabled"
      @wheel="preventNumberWheelScroll"
    />

    <VAlert
      v-if="changeAmount > 0"
      type="success"
      variant="tonal"
      density="compact"
    >
      Cambio: {{ fmt(changeAmount) }}
    </VAlert>
  </div>
</template>
