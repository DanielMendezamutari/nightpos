<script setup>
import { PAYMENT_METHOD_OPTIONS, paymentMethodLabel } from '@/constants/paymentMethods'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'

const props = defineProps({
  settlement: {
    type: Object,
    default: null,
  },
  title: {
    type: String,
    default: 'Confirmar pago',
  },
  typeLabel: {
    type: String,
    default: 'Liquidación',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['confirm'])

const model = defineModel({ type: Boolean, default: false })

const paymentMethod = ref('CASH')
const notes = ref('')

const confirmMessage = computed(() => {
  if (!props.settlement)
    return ''

  const method = paymentMethodLabel(paymentMethod.value)

  return `Se registrará un egreso de ${props.settlement.total_amount} BOB por ${method.toUpperCase()} en tu caja abierta.`
})

watch(model, open => {
  if (open) {
    paymentMethod.value = 'CASH'
    notes.value = ''
  }
})

function submit() {
  emit('confirm', {
    payment_method: paymentMethod.value,
    notes: notes.value?.trim() || null,
  })
}

useDialogKeyboardShortcuts({
  active: model,
  onConfirm: submit,
  onCancel: () => { model.value = false },
  canConfirm: () => Boolean(paymentMethod.value),
  loading: toRef(props, 'loading'),
})
</script>

<template>
  <VDialog
    v-model="model"
    max-width="440"
  >
    <VCard :title="title">
      <VCardText>
        <p class="text-body-2 mb-3">
          Monto: <strong>{{ settlement?.total_amount }} BOB</strong><br>
          Persona: <strong>{{ settlement?.staff_name }}</strong><br>
          Tipo: <strong>{{ typeLabel }}</strong>
        </p>

        <VSelect
          v-model="paymentMethod"
          :items="PAYMENT_METHOD_OPTIONS"
          label="Método de pago *"
          class="mb-4"
        />

        <VTextField
          v-model="notes"
          label="Notas (opcional)"
          rows="2"
          class="mb-4"
        />

        <VAlert
          type="info"
          variant="tonal"
          density="compact"
          class="mb-0"
        >
          {{ confirmMessage }}
        </VAlert>
      </VCardText>
      <VCardActions>
        <VBtn
          variant="text"
          @click="model = false"
        >
          Cancelar
        </VBtn>
        <VSpacer />
        <VBtn
          color="success"
          :loading="loading"
          :disabled="loading || !paymentMethod"
          @click="submit"
        >
          Confirmar pago
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
