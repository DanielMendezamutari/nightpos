<script setup>
import SettlementAdjustmentSummary from '@/components/nightpos/settlements/SettlementAdjustmentSummary.vue'
import SettlementPayFinesSelector from '@/components/nightpos/settlements/SettlementPayFinesSelector.vue'
import { fetchSettlementPayPreview } from '@/api/settlements'
import { PAYMENT_METHOD_OPTIONS, paymentMethodLabel } from '@/constants/paymentMethods'
import { formatBob } from '@/constants/settlements'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { getApiErrorMessage } from '@/services/http'

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

const emit = defineEmits(['confirm', 'register-fine'])

const model = defineModel({ type: Boolean, default: false })

const { canManageSettlementFines } = useNightPosPermissions()

const paymentMethod = ref('CASH')
const notes = ref('')
const previewLoading = ref(false)
const previewError = ref('')
const preview = ref(null)
const selectedFineIds = ref([])
const shouldAutoSelectFines = ref(true)

const settlementId = computed(() => props.settlement?.id ?? null)

const previewAdjustments = computed(() => preview.value?.adjustments ?? [])

const netAmount = computed(() => preview.value?.net_amount ?? props.settlement?.net_amount ?? props.settlement?.total_amount ?? '0.00')

const availableFines = computed(() => preview.value?.available_fines ?? [])

const canConfirm = computed(() =>
  Boolean(paymentMethod.value)
  && Boolean(settlementId.value)
  && !previewLoading.value
  && !previewError.value,
)

const confirmMessage = computed(() => {
  const method = paymentMethodLabel(paymentMethod.value)

  return `Se registrará un egreso de ${formatBob(netAmount.value)} por ${method.toUpperCase()} en tu caja abierta.`
})

let previewRequestId = 0

async function loadPreview(ids = selectedFineIds.value) {
  if (!settlementId.value)
    return

  const requestId = ++previewRequestId
  previewLoading.value = true
  previewError.value = ''

  try {
    const data = await fetchSettlementPayPreview(settlementId.value, ids)

    if (requestId !== previewRequestId)
      return

    preview.value = data

    if (shouldAutoSelectFines.value && !ids.length && (data.available_fines?.length ?? 0) > 0) {
      shouldAutoSelectFines.value = false
      selectedFineIds.value = data.available_fines.map(fine => fine.id)

      return
    }

    shouldAutoSelectFines.value = false
  }
  catch (error) {
    if (requestId !== previewRequestId)
      return

    preview.value = null
    previewError.value = getApiErrorMessage(error)
  }
  finally {
    if (requestId === previewRequestId)
      previewLoading.value = false
  }
}

watch(model, open => {
  if (open) {
    paymentMethod.value = 'CASH'
    notes.value = ''
    preview.value = null
    previewError.value = ''
    selectedFineIds.value = []
    shouldAutoSelectFines.value = true
    loadPreview([])
  }
})

watch(selectedFineIds, ids => {
  if (model.value && settlementId.value)
    loadPreview(ids)
}, { deep: true })

function submit() {
  if (!canConfirm.value)
    return

  emit('confirm', {
    payment_method: paymentMethod.value,
    notes: notes.value?.trim() || null,
    applied_fine_ids: [...selectedFineIds.value],
  })
}

useDialogKeyboardShortcuts({
  active: model,
  onConfirm: submit,
  onCancel: () => { model.value = false },
  canConfirm: () => canConfirm.value,
  loading: toRef(props, 'loading'),
})

defineExpose({
  reloadPreview: () => loadPreview(selectedFineIds.value),
})
</script>

<template>
  <VDialog
    v-model="model"
    max-width="560"
  >
    <VCard :title="title">
      <VCardText>
        <p class="text-body-2 mb-4">
          Persona: <strong>{{ settlement?.staff_name }}</strong><br>
          Tipo: <strong>{{ typeLabel }}</strong>
        </p>

        <VProgressLinear
          v-if="previewLoading"
          indeterminate
          class="mb-4"
        />

        <VAlert
          v-if="previewError"
          type="error"
          variant="tonal"
          class="mb-4"
        >
          {{ previewError }}
        </VAlert>

        <SettlementAdjustmentSummary
          v-if="preview && !previewError"
          class="mb-4"
          :gross-amount="preview.gross_amount"
          :net-amount="preview.net_amount"
          :adjustments="previewAdjustments"
        />

        <SettlementPayFinesSelector
          v-if="preview && !previewError"
          v-model:selected-fine-ids="selectedFineIds"
          class="mb-4"
          :available-fines="availableFines"
          :loading="previewLoading"
          :disabled="loading"
        />

        <VBtn
          v-if="canManageSettlementFines && preview && !previewError"
          color="warning"
          variant="tonal"
          prepend-icon="ri-error-warning-line"
          class="mb-4"
          block
          @click="emit('register-fine')"
        >
          Registrar multa
        </VBtn>

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
          v-if="!previewError"
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
          :loading="loading || previewLoading"
          :disabled="loading || previewLoading || !canConfirm"
          @click="submit"
        >
          Pagar {{ formatBob(netAmount) }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
