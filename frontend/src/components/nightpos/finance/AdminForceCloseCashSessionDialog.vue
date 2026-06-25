<script setup>
import {
  fetchAdminCashSessionCloseCheck,
  forceCloseAdminCashSession,
  FORCE_CLOSE_REASONS,
} from '@/api/adminCashSessions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  session: { type: Object, default: null },
})

const emit = defineEmits(['update:modelValue', 'closed'])

const { notify } = useNightPosNotify()
const loading = ref(false)
const saving = ref(false)
const closeCheck = ref(null)
const confirmed = ref(false)

const form = ref({
  forced_close_reason: null,
  forced_close_notes: '',
})

const refForm = ref()

const formatDate = value => value ? new Date(value).toLocaleString('es-BO') : '—'

const blockers = computed(() => closeCheck.value?.blockers ?? [])
const financialPreview = computed(() => closeCheck.value?.financial_preview ?? {})
const canSubmit = computed(() =>
  !!form.value.forced_close_reason
  && (form.value.forced_close_notes?.trim().length ?? 0) >= 3
  && confirmed.value
  && !saving.value,
)

const loadCloseCheck = async () => {
  if (!props.session?.id)
    return

  loading.value = true

  try {
    closeCheck.value = await fetchAdminCashSessionCloseCheck(props.session.id)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    close()
  }
  finally {
    loading.value = false
  }
}

const reset = () => {
  form.value = { forced_close_reason: null, forced_close_notes: '' }
  confirmed.value = false
  closeCheck.value = null
  refForm.value?.resetValidation?.()
}

const close = () => emit('update:modelValue', false)

watch(() => props.modelValue, open => {
  if (open) {
    reset()
    loadCloseCheck()
  }
})

const submit = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid || !canSubmit.value || !props.session?.id)
    return

  saving.value = true

  try {
    await forceCloseAdminCashSession(props.session.id, {
      forced_close_reason: form.value.forced_close_reason,
      forced_close_notes: form.value.forced_close_notes.trim(),
      declared_closing_amount: null,
    })
    notify('Caja cerrada administrativamente', 'success')
    emit('closed')
    close()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

useDialogKeyboardShortcuts({
  active: toRef(props, 'modelValue'),
  onConfirm: submit,
  onCancel: close,
  canConfirm: () => canSubmit.value,
  loading: saving,
})
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="640"
    persistent
    scrollable
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-shield-keyhole-line" />
        Cerrar administrativamente
      </VCardTitle>

      <VCardText>
        <VProgressLinear
          v-if="loading"
          indeterminate
          class="mb-4"
        />

        <template v-else-if="session">
          <VAlert
            type="warning"
            variant="tonal"
            class="mb-4"
          >
            Este cierre libera la caja operativamente. No paga pendientes ni corrige diferencias.
          </VAlert>

          <div class="text-body-2 mb-4">
            <div><strong>Cajera:</strong> {{ session.cashier?.name || '—' }}</div>
            <div><strong>Apertura:</strong> {{ formatDate(session.opened_at) }}</div>
            <div v-if="session.official_shift">
              <strong>Turno:</strong> {{ session.official_shift.shift_type }} · {{ session.official_shift.business_date }}
            </div>
            <div><strong>Efectivo esperado:</strong> {{ formatMoney(financialPreview.expected_cash ?? session.expected_cash) }} BOB</div>
          </div>

          <div
            v-if="blockers.length"
            class="mb-4"
          >
            <div class="text-subtitle-2 mb-2">
              Esta caja tiene pendientes:
            </div>
            <VAlert
              v-for="(blocker, index) in blockers"
              :key="`${blocker.code}-${index}`"
              type="error"
              variant="tonal"
              density="compact"
              class="mb-2"
            >
              {{ blocker.message }}
            </VAlert>
          </div>

          <VAlert
            v-else
            type="success"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            No hay blockers operativos detectados al momento del cierre.
          </VAlert>

          <VForm
            ref="refForm"
            @submit.prevent="submit"
          >
            <VSelect
              v-model="form.forced_close_reason"
              :items="FORCE_CLOSE_REASONS"
              item-title="title"
              item-value="value"
              label="Motivo *"
              class="mb-3"
              :rules="[v => !!v || 'Seleccione un motivo']"
            />
            <VTextarea
              v-model="form.forced_close_notes"
              label="Notas *"
              rows="3"
              class="mb-3"
              :rules="[
                v => !!v?.trim() || 'Las notas son obligatorias',
                v => (v?.trim().length ?? 0) >= 3 || 'Mínimo 3 caracteres',
              ]"
            />
            <VCheckbox
              v-model="confirmed"
              label="Entiendo que este cierre no paga pendientes ni corrige diferencias."
              hide-details
            />
          </VForm>
        </template>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          variant="text"
          :disabled="saving"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VBtn
          color="error"
          :loading="saving"
          :disabled="!canSubmit"
          @click="submit"
        >
          Confirmar cierre administrativo
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
