<script setup>
import {
  previewSettlementManualDiscount,
  applySettlementManualDiscount,
} from '@/api/settlements'
import {
  DISCOUNT_MODE_LABELS,
  formatBob,
  formatSignedBob,
} from '@/constants/settlements'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  settlementId: {
    type: Number,
    default: null,
  },
  staffName: {
    type: String,
    default: null,
  },
  grossAmount: {
    type: [String, Number],
    default: null,
  },
})

const emit = defineEmits(['created'])

const model = defineModel({ type: Boolean, default: false })
const { notify } = useNightPosNotify()

const loading = ref(false)
const previewLoading = ref(false)
const preview = ref(null)

const form = ref({
  discount_mode: 'PERCENT',
  discount_value: null,
  reason: '',
  notes: '',
})

const modeOptions = [
  { title: DISCOUNT_MODE_LABELS.PERCENT, value: 'PERCENT' },
  { title: DISCOUNT_MODE_LABELS.AMOUNT, value: 'AMOUNT' },
]

const canSubmit = computed(() =>
  Boolean(props.settlementId)
  && Number(form.value.discount_value) > 0
  && form.value.reason.trim().length > 0,
)

let previewRequestId = 0

async function loadPreview() {
  if (!props.settlementId || !canSubmit.value)
    return

  const requestId = ++previewRequestId
  previewLoading.value = true

  try {
    const data = await previewSettlementManualDiscount(props.settlementId, {
      discount_mode: form.value.discount_mode,
      discount_value: Number(form.value.discount_value),
    })

    if (requestId === previewRequestId)
      preview.value = data.preview
  }
  catch {
    if (requestId === previewRequestId)
      preview.value = null
  }
  finally {
    if (requestId === previewRequestId)
      previewLoading.value = false
  }
}

watch(
  () => [form.value.discount_mode, form.value.discount_value],
  () => {
    if (model.value)
      loadPreview()
  },
)

watch(model, open => {
  if (open) {
    form.value = {
      discount_mode: 'PERCENT',
      discount_value: null,
      reason: '',
      notes: '',
    }
    preview.value = null
  }
})

async function submit() {
  if (!canSubmit.value)
    return

  loading.value = true

  try {
    const data = await applySettlementManualDiscount(props.settlementId, {
      discount_mode: form.value.discount_mode,
      discount_value: Number(form.value.discount_value),
      reason: form.value.reason.trim(),
      notes: form.value.notes?.trim() || null,
    })

    notify('Descuento manual registrado.', 'success')
    emit('created', data)
    model.value = false
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

useDialogKeyboardShortcuts({
  active: model,
  onConfirm: submit,
  onCancel: () => { model.value = false },
  canConfirm: () => canSubmit.value,
  loading,
})
</script>

<template>
  <VDialog
    v-model="model"
    max-width="560"
  >
    <VCard title="Agregar descuento manual">
      <VCardText>
        <VAlert
          v-if="staffName"
          type="info"
          variant="tonal"
          density="compact"
          class="mb-4"
        >
          Persona: <strong>{{ staffName }}</strong>
          <span v-if="grossAmount"> · Bruto {{ formatBob(grossAmount) }}</span>
        </VAlert>

        <VSelect
          v-model="form.discount_mode"
          :items="modeOptions"
          item-title="title"
          item-value="value"
          label="Tipo de descuento *"
          class="mb-3"
        />

        <VTextField
          v-model.number="form.discount_value"
          :label="form.discount_mode === 'PERCENT' ? 'Porcentaje (%) *' : 'Monto (Bs) *'"
          type="number"
          min="0.01"
          :step="form.discount_mode === 'PERCENT' ? '0.01' : '0.01'"
          class="mb-3"
        />

        <VTextField
          v-model="form.reason"
          label="Motivo *"
          class="mb-3"
        />

        <VTextarea
          v-model="form.notes"
          label="Notas (opcional)"
          rows="2"
          class="mb-4"
        />

        <VCard
          v-if="preview"
          variant="outlined"
        >
          <VCardTitle class="text-subtitle-2">
            Vista previa
          </VCardTitle>
          <VCardText>
            <div class="d-flex justify-space-between mb-2">
              <span>Bruto</span>
              <strong>{{ formatBob(preview.gross_amount) }}</strong>
            </div>
            <div class="d-flex justify-space-between mb-2">
              <span>Limpieza</span>
              <span>{{ formatSignedBob(preview.cleaning_amount) }}</span>
            </div>
            <div class="d-flex justify-space-between mb-2">
              <span>Base descuento</span>
              <strong>{{ formatBob(preview.discount_base) }}</strong>
            </div>
            <div class="d-flex justify-space-between mb-2">
              <span>Descuento</span>
              <span>{{ formatSignedBob(preview.discount_amount) }}</span>
            </div>
            <VDivider class="my-2" />
            <div class="d-flex justify-space-between text-success">
              <span class="font-weight-medium">Neto estimado</span>
              <strong>{{ formatBob(preview.net_amount) }}</strong>
            </div>
          </VCardText>
        </VCard>

        <VProgressLinear
          v-if="previewLoading"
          indeterminate
          class="mt-4"
        />
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
          color="primary"
          :loading="loading"
          :disabled="loading || !canSubmit"
          @click="submit"
        >
          Guardar descuento
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
