<script setup>
import { createStaffFine } from '@/api/staffFines'
import { fetchStaffGirls, fetchStaffWaiters } from '@/api/staff'
import { STAFF_LABELS } from '@/composables/useUserAdminForm'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  staffUserId: {
    type: Number,
    default: null,
  },
  staffRole: {
    type: String,
    default: null,
  },
  staffName: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['created'])

const model = defineModel({ type: Boolean, default: false })

const { notify } = useNightPosNotify()

const loading = ref(false)
const staffLoading = ref(false)
const staffOptions = ref([])

const form = ref({
  staff_user_id: null,
  staff_role: null,
  amount: null,
  reason: '',
  notes: '',
})

const roleOptions = [
  { title: STAFF_LABELS.GIRL || 'Chica', value: 'GIRL' },
  { title: STAFF_LABELS.WAITER || 'Garzón', value: 'WAITER' },
  { title: STAFF_LABELS.CLEANING || 'Limpieza', value: 'CLEANING' },
]

const canSubmit = computed(() =>
  Boolean(form.value.staff_user_id)
  && Boolean(form.value.staff_role)
  && Number(form.value.amount) > 0
  && form.value.reason.trim().length > 0,
)

async function loadStaffOptions() {
  staffLoading.value = true

  try {
    const [girls, waiters] = await Promise.all([
      fetchStaffGirls().catch(() => []),
      fetchStaffWaiters().catch(() => []),
    ])

    staffOptions.value = [
      ...girls.map(item => ({
        title: `${item.name} (Chica)`,
        value: item.id,
        staff_role: 'GIRL',
        name: item.name,
      })),
      ...waiters.map(item => ({
        title: `${item.name} (Garzón)`,
        value: item.id,
        staff_role: 'WAITER',
        name: item.name,
      })),
    ]
  }
  finally {
    staffLoading.value = false
  }
}

function resetForm() {
  form.value = {
    staff_user_id: props.staffUserId ?? null,
    staff_role: props.staffRole ?? null,
    amount: null,
    reason: '',
    notes: '',
  }
}

function onStaffSelected(userId) {
  const match = staffOptions.value.find(option => option.value === userId)

  if (match)
    form.value.staff_role = match.staff_role
}

function resolvePersonName() {
  if (props.staffName?.trim())
    return props.staffName.trim()

  const match = staffOptions.value.find(option => option.value === form.value.staff_user_id)

  return match?.name || 'la persona'
}

function roleLabel(role) {
  return roleOptions.find(option => option.value === role)?.title || role
}

watch(model, async open => {
  if (open) {
    resetForm()
    await loadStaffOptions()
  }
})

async function submit() {
  if (!canSubmit.value)
    return

  loading.value = true

  try {
    const data = await createStaffFine({
      staff_user_id: form.value.staff_user_id,
      staff_role: form.value.staff_role,
      amount: Number(form.value.amount),
      reason: form.value.reason.trim(),
      notes: form.value.notes?.trim() || null,
    })

    notify(`Multa registrada para ${resolvePersonName()}.`, 'success')
    emit('created', data.fine)
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
    max-width="520"
  >
    <VCard title="Registrar multa">
      <VCardText>
        <VAlert
          v-if="staffUserId && staffName"
          type="info"
          variant="tonal"
          density="compact"
          class="mb-4"
        >
          Persona: <strong>{{ staffName }}</strong>
          <span v-if="staffRole"> · {{ roleLabel(staffRole) }}</span>
        </VAlert>

        <VAutocomplete
          v-if="!staffUserId"
          v-model="form.staff_user_id"
          :items="staffOptions"
          item-title="title"
          item-value="value"
          label="Persona *"
          :loading="staffLoading"
          class="mb-3"
          @update:model-value="onStaffSelected"
        />

        <VSelect
          v-if="!staffRole"
          v-model="form.staff_role"
          :items="roleOptions"
          item-title="title"
          item-value="value"
          label="Rol *"
          class="mb-3"
        />

        <VTextField
          v-model.number="form.amount"
          label="Monto (Bs) *"
          type="number"
          min="0.01"
          step="0.01"
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
          Registrar multa
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
