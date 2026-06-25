<script setup>
import { cancelStaffFine, fetchStaffFines } from '@/api/staffFines'
import {
  formatBob,
  staffFineStatusLabel,
  STAFF_FINE_STATUS_COLORS,
} from '@/constants/settlements'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useDialogKeyboardShortcuts } from '@/composables/useDialogKeyboardShortcuts'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  staffUserId: {
    type: Number,
    default: null,
  },
  officialShiftId: {
    type: Number,
    default: null,
  },
  status: {
    type: String,
    default: null,
  },
  title: {
    type: String,
    default: 'Multas',
  },
  showTitle: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['changed'])

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(false)
const fines = ref([])
const showCancelDialog = ref(false)
const cancelling = ref(false)
const cancelReason = ref('')
const cancellingFine = ref(null)

const canManage = computed(() => can('settlements.fines.manage'))

const headers = [
  { title: 'Persona', key: 'staff_name' },
  { title: 'Monto', key: 'amount' },
  { title: 'Motivo', key: 'reason' },
  { title: 'Estado', key: 'status' },
  { title: 'Registrado por', key: 'created_by_name' },
  { title: 'Fecha', key: 'created_at' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

async function load() {
  loading.value = true

  try {
    const data = await fetchStaffFines({
      staff_user_id: props.staffUserId || undefined,
      official_shift_id: props.officialShiftId || undefined,
      status: props.status || undefined,
    })

    fines.value = data.fines ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    fines.value = []
  }
  finally {
    loading.value = false
  }
}

function openCancel(fine) {
  cancellingFine.value = fine
  cancelReason.value = ''
  showCancelDialog.value = true
}

async function confirmCancel() {
  if (!cancellingFine.value || !cancelReason.value.trim()) {
    notify('Debe indicar el motivo de cancelación.', 'warning')

    return
  }

  cancelling.value = true

  try {
    await cancelStaffFine(cancellingFine.value.id, {
      cancellation_reason: cancelReason.value.trim(),
    })

    notify('Multa cancelada.', 'success')
    showCancelDialog.value = false
    cancellingFine.value = null
    await load()
    emit('changed')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    cancelling.value = false
  }
}

watch(
  () => [props.staffUserId, props.officialShiftId, props.status],
  () => { load() },
  { immediate: true },
)

defineExpose({ reload: load })

useDialogKeyboardShortcuts({
  active: showCancelDialog,
  onConfirm: confirmCancel,
  onCancel: () => { showCancelDialog.value = false },
  canConfirm: () => cancelReason.value.trim().length > 0,
  loading: cancelling,
})
</script>

<template>
  <VCard>
    <VCardTitle v-if="showTitle">
      {{ title }}
    </VCardTitle>

    <VDataTable
      :headers="headers"
      :items="fines"
      :loading="loading"
      :items-per-page="10"
      class="text-no-wrap"
    >
      <template #item.amount="{ item }">
        {{ formatBob(item.amount) }}
      </template>

      <template #item.status="{ item }">
        <VChip
          size="small"
          variant="tonal"
          :color="STAFF_FINE_STATUS_COLORS[item.status] || 'default'"
        >
          {{ staffFineStatusLabel(item.status) }}
        </VChip>
      </template>

      <template #item.created_by_name="{ item }">
        {{ item.created_by_name || '—' }}
      </template>

      <template #item.actions="{ item }">
        <VBtn
          v-if="canManage && item.status === 'PENDING'"
          size="small"
          variant="text"
          color="error"
          @click="openCancel(item)"
        >
          Cancelar
        </VBtn>
        <span
          v-else
          class="text-caption text-medium-emphasis"
        >—</span>
      </template>
    </VDataTable>
  </VCard>

  <VDialog
    v-model="showCancelDialog"
    max-width="440"
  >
    <VCard title="Cancelar multa">
      <VCardText>
        <p
          v-if="cancellingFine"
          class="text-body-2 mb-3"
        >
          Multa: <strong>{{ cancellingFine.reason }}</strong> · {{ formatBob(cancellingFine.amount) }}
        </p>

        <VTextarea
          v-model="cancelReason"
          label="Motivo de cancelación *"
          rows="3"
        />
      </VCardText>
      <VCardActions>
        <VBtn
          variant="text"
          @click="showCancelDialog = false"
        >
          Cerrar
        </VBtn>
        <VSpacer />
        <VBtn
          color="error"
          :loading="cancelling"
          :disabled="cancelling || !cancelReason.trim()"
          @click="confirmCancel"
        >
          Confirmar cancelación
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
