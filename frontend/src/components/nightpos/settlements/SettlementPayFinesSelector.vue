<script setup>
import { formatBob } from '@/constants/settlements'

const props = defineProps({
  availableFines: {
    type: Array,
    default: () => [],
  },
  selectedFineIds: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:selectedFineIds'])

const hasFines = computed(() => props.availableFines.length > 0)

function isSelected(fineId) {
  return props.selectedFineIds.includes(fineId)
}

function toggleFine(fineId, checked) {
  const next = checked
    ? [...new Set([...props.selectedFineIds, fineId])]
    : props.selectedFineIds.filter(id => id !== fineId)

  emit('update:selectedFineIds', next)
}

const selectedTotal = computed(() => props.availableFines
  .filter(fine => isSelected(fine.id))
  .reduce((sum, fine) => sum + Number(fine.amount || 0), 0))
</script>

<template>
  <div>
    <p class="text-subtitle-2 mb-2">
      Multas pendientes
    </p>

    <VAlert
      v-if="!hasFines"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-0"
    >
      Sin multas pendientes.
    </VAlert>

    <template v-else>
      <div
        v-for="fine in availableFines"
        :key="fine.id"
        class="d-flex align-center justify-space-between py-2"
      >
        <VCheckbox
          :model-value="isSelected(fine.id)"
          :label="`${fine.reason} (${formatBob(fine.amount)})`"
          :disabled="disabled || loading"
          hide-details
          density="compact"
          @update:model-value="checked => toggleFine(fine.id, checked)"
        />
      </div>

      <div class="d-flex justify-space-between text-caption text-medium-emphasis mt-2">
        <span>Total multas seleccionadas</span>
        <span>{{ formatBob(selectedTotal) }}</span>
      </div>
    </template>
  </div>
</template>
