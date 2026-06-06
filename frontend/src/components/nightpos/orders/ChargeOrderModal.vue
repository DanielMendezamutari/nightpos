<script setup>

import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'

import MixedPaymentForm from '@/components/nightpos/payments/MixedPaymentForm.vue'

import { formatMoney } from '@/composables/useOrderHelpers'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'



const props = defineProps({

  modelValue: { type: Boolean, default: false },

  order: { type: Object, default: null },

  cashSessionOpen: { type: Boolean, default: false },

  loading: { type: Boolean, default: false },

})



const emit = defineEmits(['update:modelValue', 'confirm', 'cash-opened'])



const { can } = useNightPosPermissions()



const paymentFormRef = ref(null)

const showOpenCash = ref(false)



const orderTotal = computed(() => Number(props.order?.total ?? 0))



const canChargeNow = computed(() => props.cashSessionOpen)



watch(() => props.modelValue, open => {

  if (open)

    paymentFormRef.value?.reset()

})



const close = () => emit('update:modelValue', false)



const onCashOpened = () => {

  emit('cash-opened')

}



const confirm = () => {

  const payload = paymentFormRef.value?.toPayload()



  if (!payload)

    return



  emit('confirm', {

    payments: payload.payments,

    chargePaymentsSum: payload.paymentsSum,

    orderTotal: payload.total,

    cashPortion: payload.cashPortion,

    receivedAmount: payload.receivedAmount,

  })

}

</script>



<template>

  <VDialog

    :model-value="modelValue"

    max-width="480"

    persistent

    @update:model-value="emit('update:modelValue', $event)"

  >

    <VCard>

      <VCardTitle>Cobrar comanda</VCardTitle>

      <VCardText>

        <VAlert

          v-if="!canChargeNow"

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

          :disabled="!canChargeNow"

          variant="selector"

        />

      </VCardText>

      <VCardActions>

        <VBtn

          variant="text"

          @click="close"

        >

          Cancelar

        </VBtn>

        <VBtn

          color="success"

          :loading="loading"

          :disabled="!canChargeNow"

          @click="confirm"

        >

          Confirmar cobro

        </VBtn>

      </VCardActions>

    </VCard>

  </VDialog>



  <QuickOpenCashDialog

    v-model="showOpenCash"

    @opened="onCashOpened"

  />

</template>

