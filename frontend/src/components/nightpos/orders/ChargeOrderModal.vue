<script setup>

import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'

import MixedPaymentForm from '@/components/nightpos/payments/MixedPaymentForm.vue'

import { formatMoney } from '@/composables/useOrderHelpers'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'



const props = defineProps({

  modelValue: { type: Boolean, default: false },

  order: { type: Object, default: null },

  cashSessionOpen: { type: Boolean, default: false },

  loading: { type: Boolean, default: false },

})



const emit = defineEmits(['update:modelValue', 'confirm', 'cash-opened'])



const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()



const paymentFormRef = ref(null)

const showOpenCash = ref(false)



const orderTotal = computed(() => Number(props.order?.total ?? 0))



const canChargeNow = computed(() => props.cashSessionOpen && !props.loading)



watch(() => props.modelValue, open => {

  if (open)

    paymentFormRef.value?.reset()

})



const close = () => emit('update:modelValue', false)



const onCashOpened = () => {

  emit('cash-opened')

}



const confirm = () => {

  if (!canChargeNow.value)

    return



  const paymentCheck = paymentFormRef.value?.validate()

  if (!paymentCheck?.valid) {

    notify(paymentCheck?.message ?? 'Revise los montos de pago.', 'warning')

    return

  }



  const payload = paymentFormRef.value?.toPayload()



  if (!payload?.payments?.length)

    return



  emit('confirm', {

    payments: payload.payments,

    chargePaymentsSum: payload.paymentsSum,

    orderTotal: payload.total,

    cashPortion: payload.cashPortion,

    receivedAmount: payload.receivedAmount,

  })

}

useDialogKeyboardShortcuts({
  active: toRef(props, 'modelValue'),
  onConfirm: confirm,
  onCancel: close,
  canConfirm: () => canChargeNow.value,
  loading: toRef(props, 'loading'),
})

</script>



<template>

  <VDialog

    :model-value="modelValue"

    max-width="480"

    persistent

    @update:model-value="emit('update:modelValue', $event)"

  >

    <VCard>

      <VCardTitle class="d-flex flex-column align-start gap-1 pb-2">
        <span>Cobrar comanda</span>
        <span
          v-if="order?.table_label"
          class="text-body-1 font-weight-medium"
        >
          {{ order.table_label }}
          <span
            v-if="order?.order_number"
            class="text-medium-emphasis"
          >
            · {{ order.order_number }}
          </span>
        </span>
      </VCardTitle>

      <VForm @submit.prevent="confirm">

        <VCardText>

          <VAlert

            v-if="!cashSessionOpen"

            type="warning"

            variant="tonal"

            class="mb-4"

          >

            <div class="d-flex flex-column flex-sm-row align-sm-center justify-space-between gap-2">

              <span>No hay caja abierta. Abra caja para cobrar esta comanda.</span>

              <VBtn

                v-if="can('cash.access')"

                color="primary"

                size="small"

                variant="tonal"

                @click="showOpenCash = true"

              >

                Abrir caja ahora

              </VBtn>

            </div>

          </VAlert>



          <VAlert
            v-if="order?.status === 'OPEN'"
            type="warning"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            Esta comanda aún no fue enviada a barra. Puede cobrarla de todas formas.
          </VAlert>

          <VAlert

            type="info"

            variant="tonal"

            class="mb-4"

          >

            Total a cobrar:

            <strong>{{ formatMoney(order?.total, order?.currency) }}</strong>

          </VAlert>



          <MixedPaymentForm
            ref="paymentFormRef"
            :total="orderTotal"
            :currency="order?.currency ?? 'BOB'"
            :disabled="!cashSessionOpen"
            show-quick-buttons
          />

        </VCardText>

        <VCardActions>

          <VBtn

            variant="text"

            type="button"

            @click="close"

          >

            Cancelar

          </VBtn>

          <VBtn

            color="success"

            type="submit"

            :loading="loading"

            :disabled="!cashSessionOpen"

          >

            Confirmar cobro

          </VBtn>

        </VCardActions>

      </VForm>

    </VCard>

  </VDialog>



  <QuickOpenCashDialog

    v-model="showOpenCash"

    @opened="onCashOpened"

  />

</template>
