<script setup>
import { registerCashMovement } from '@/api/cash'
import { fetchCashMovementReasonsForCash, createCashMovementReason } from '@/api/cashMovementReasons'
import { PAYMENT_METHOD_OPTIONS } from '@/constants/paymentMethods'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { getApiErrorMessage } from '@/services/http'

const emit = defineEmits(['registered'])

const model = defineModel({ type: Boolean, default: false })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(false)
const movementReasons = ref([])
const showQuickReason = ref(false)
const quickReasonSaving = ref(false)
const quickReasonForm = ref({ name: '' })

const form = ref({
  movement_type: 'EXPENSE',
  amount: null,
  cash_movement_reason_id: null,
  payment_method: 'CASH',
  notes: '',
})

const canManageCashReasons = computed(() => can('settings.cash_reasons.manage'))

const reasonAppliesToMovement = (reason, movementType) =>
  reason.status === 'active'
  && (reason.type === movementType || reason.type === 'BOTH')

const movementReasonOptions = computed(() => movementReasons.value
  .filter(r => reasonAppliesToMovement(r, form.value.movement_type))
  .map(r => ({ title: r.name, value: r.id })))

const hasMovementReasonsForType = computed(() => movementReasonOptions.value.length > 0)

async function loadMovementReasons() {
  try {
    movementReasons.value = await fetchCashMovementReasonsForCash({ active_only: true })
  }
  catch {
    movementReasons.value = []
  }
}

function resetForm() {
  form.value = {
    movement_type: 'EXPENSE',
    amount: null,
    cash_movement_reason_id: null,
    payment_method: 'CASH',
    notes: '',
  }
  showQuickReason.value = false
  quickReasonForm.value = { name: '' }
}

watch(model, open => {
  if (open) {
    resetForm()
    loadMovementReasons()
  }
})

watch(() => form.value.movement_type, () => {
  form.value.cash_movement_reason_id = null
})

async function saveQuickReason() {
  if (!quickReasonForm.value.name?.trim()) {
    notify('Indique el nombre del motivo', 'warning')

    return
  }

  quickReasonSaving.value = true
  try {
    const reason = await createCashMovementReason({
      type: form.value.movement_type,
      name: quickReasonForm.value.name.trim(),
      status: 'active',
    })

    await loadMovementReasons()
    if (reason?.id)
      form.value.cash_movement_reason_id = reason.id
    showQuickReason.value = false
    quickReasonForm.value = { name: '' }
    notify('Motivo creado')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    quickReasonSaving.value = false
  }
}

async function submit() {
  if (!form.value.cash_movement_reason_id) {
    notify('Seleccione un motivo', 'warning')

    return
  }

  loading.value = true
  try {
    const session = await registerCashMovement({
      movement_type: form.value.movement_type,
      amount: Number(form.value.amount),
      cash_movement_reason_id: form.value.cash_movement_reason_id,
      payment_method: form.value.payment_method,
      notes: form.value.notes || null,
    })

    model.value = false
    notify('Movimiento registrado')
    emit('registered', session)
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
  canConfirm: () => Boolean(form.value.amount) && Boolean(form.value.cash_movement_reason_id),
  loading,
})
</script>

<template>
  <VDialog
    v-model="model"
    max-width="440"
  >
    <VCard title="Registrar movimiento">
      <VCardText>
        <VSelect
          v-model="form.movement_type"
          :items="[
            { title: 'Ingreso manual', value: 'INCOME' },
            { title: 'Egreso manual', value: 'EXPENSE' },
          ]"
          label="Tipo"
          class="mb-4"
        />

        <VTextField
          v-model.number="form.amount"
          type="number"
          label="Monto (BOB)"
          min="0.01"
          class="mb-4"
        />

        <VSelect
          v-model="form.payment_method"
          :items="PAYMENT_METHOD_OPTIONS"
          label="Método de pago *"
          class="mb-4"
        />

        <VSelect
          v-model="form.cash_movement_reason_id"
          :items="movementReasonOptions"
          label="Motivo *"
          class="mb-2"
          :rules="[v => v != null || 'Seleccione un motivo']"
        />

        <VAlert
          v-if="!hasMovementReasonsForType"
          type="warning"
          variant="tonal"
          class="mb-4"
        >
          No hay motivos configurados para este tipo de movimiento.
          Pide al administrador crear motivos en
          <strong>Configuración → Motivos de caja</strong>.
        </VAlert>

        <div
          v-if="canManageCashReasons"
          class="d-flex flex-wrap gap-2 mb-4"
        >
          <VBtn
            v-if="!hasMovementReasonsForType"
            size="small"
            variant="tonal"
            color="primary"
            prepend-icon="ri-add-line"
            @click="showQuickReason = true"
          >
            Crear motivo
          </VBtn>
          <VBtn
            size="small"
            variant="text"
            :to="{ name: 'nightpos-settings-cash-reasons' }"
          >
            Gestionar motivos
          </VBtn>
        </div>

        <VExpandTransition>
          <div v-if="showQuickReason && canManageCashReasons">
            <VTextField
              v-model="quickReasonForm.name"
              label="Nombre del nuevo motivo"
              class="mb-2"
            />
            <div class="d-flex gap-2 mb-4">
              <VBtn
                size="small"
                color="primary"
                :loading="quickReasonSaving"
                @click="saveQuickReason"
              >
                Guardar motivo
              </VBtn>
              <VBtn
                size="small"
                variant="text"
                @click="showQuickReason = false"
              >
                Cancelar
              </VBtn>
            </div>
          </div>
        </VExpandTransition>

        <VTextField
          v-model="form.notes"
          label="Notas adicionales"
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
          :disabled="loading || !form.amount || !form.cash_movement_reason_id"
          @click="submit"
        >
          Registrar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
