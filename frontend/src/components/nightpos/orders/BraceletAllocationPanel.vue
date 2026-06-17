<script setup>
import BraceletDotsIndicator from '@/components/nightpos/orders/BraceletDotsIndicator.vue'
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import {
  assignedUnitsFromMap,
  buildAllocationPayload,
  initialUnitsMap,
  normalizeOperationalGirls,
} from '@/composables/useComboAllocation'

const props = defineProps({
  requiredUnits: { type: Number, required: true },
  quantity: { type: Number, default: 1 },
  unitsPerCombo: { type: Number, default: 1 },
  girls: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  initialRows: { type: Array, default: () => [] },
  canQuickCreateGirl: { type: Boolean, default: false },
})

const emit = defineEmits(['save', 'cancel', 'girl-created'])

const unitMap = ref({})
const showQuickGirl = ref(false)

const girlList = computed(() => normalizeOperationalGirls(props.girls))

watch(
  () => [props.requiredUnits, props.initialRows, props.girls],
  () => {
    unitMap.value = initialUnitsMap(props.initialRows, props.girls)
  },
  { immediate: true, deep: true },
)

const assignedUnits = computed(() => assignedUnitsFromMap(unitMap.value))

const allocationDelta = computed(() => assignedUnits.value - props.requiredUnits)

const isComplete = computed(() => allocationDelta.value === 0 && assignedUnits.value > 0)

const allocationMessage = computed(() => {
  if (allocationDelta.value === 0)
    return null
  if (allocationDelta.value < 0)
    return `Faltan ${Math.abs(allocationDelta.value)} manilla(s)`
  return `Te pasaste por ${allocationDelta.value} manilla(s)`
})

const canSave = computed(() => !props.readonly && isComplete.value)

const bumpGirl = (girlId, delta) => {
  const key = String(girlId)
  const current = Number(unitMap.value[key] ?? 0)
  unitMap.value[key] = Math.max(0, current + delta)
}

const onGirlCreated = girl => {
  if (!girl?.id)
    return

  const key = String(girl.id)
  if (!(key in unitMap.value))
    unitMap.value[key] = 0

  emit('girl-created', girl)
}

const save = () => {
  const payload = buildAllocationPayload(unitMap.value, props.requiredUnits)
  if (!payload)
    return
  emit('save', payload)
}
</script>

<template>
  <div class="bracelet-allocation-panel">
    <div
      v-if="quantity > 1"
      class="text-body-2 mb-3 pa-3 rounded-lg bg-surface-variant"
    >
      <strong>{{ quantity }} combos</strong>
      × {{ unitsPerCombo }} manillas
      <strong class="mx-1">=</strong>
      <strong>{{ requiredUnits }} manillas</strong>
    </div>

    <p class="text-body-1 mb-1">
      <strong>{{ requiredUnits }}</strong> manillas por repartir
    </p>

    <div class="d-flex align-center flex-wrap gap-3 mb-2">
      <BraceletDotsIndicator
        :total="requiredUnits"
        :filled="assignedUnits"
        :complete="isComplete"
      />
      <span
        class="text-body-2 font-weight-medium"
        :class="isComplete ? 'text-success' : 'text-warning'"
      >
        {{ assignedUnits }} / {{ requiredUnits }}
      </span>
    </div>

    <p
      v-if="allocationMessage"
      class="text-body-2 text-warning mb-4"
    >
      {{ allocationMessage }}
    </p>
    <div
      v-else-if="isComplete"
      class="text-body-2 text-success mb-4"
    >
      Reparto completo
    </div>
    <div
      v-else
      class="mb-4"
    />

    <div
      v-for="girl in girlList"
      :key="girl.id"
      class="d-flex align-center gap-2 mb-3 bracelet-allocation-panel__row"
    >
      <span class="flex-grow-1 text-body-1 font-weight-medium text-truncate">
        {{ girl.name }}
      </span>
      <VBtn
        icon
        variant="outlined"
        size="small"
        :disabled="readonly || (unitMap[String(girl.id)] ?? 0) <= 0"
        @click="bumpGirl(girl.id, -1)"
      >
        <VIcon icon="ri-subtract-line" />
      </VBtn>
      <span
        class="text-h6 bracelet-allocation-panel__count"
      >
        {{ unitMap[String(girl.id)] ?? 0 }}
      </span>
      <VBtn
        icon
        variant="outlined"
        size="small"
        color="primary"
        :disabled="readonly"
        @click="bumpGirl(girl.id, 1)"
      >
        <VIcon icon="ri-add-line" />
      </VBtn>
    </div>

    <VAlert
      v-if="!girlList.length"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      No hay chicas del turno cargadas.
    </VAlert>

    <VBtn
      v-if="canQuickCreateGirl && !readonly"
      variant="text"
      prepend-icon="ri-user-add-line"
      class="mb-4 px-0"
      @click="showQuickGirl = true"
    >
      Nueva chica
    </VBtn>

    <div
      v-if="!readonly"
      class="d-flex gap-2 mt-2"
    >
      <VBtn
        variant="text"
        @click="emit('cancel')"
      >
        Cancelar
      </VBtn>
      <VBtn
        color="primary"
        size="large"
        class="flex-grow-1"
        :loading="loading"
        :disabled="!canSave"
        @click="save"
      >
        Guardar
      </VBtn>
    </div>

    <QuickGirlCreateDialog
      v-model="showQuickGirl"
      @created="onGirlCreated"
    />
  </div>
</template>

<style scoped>
.bracelet-allocation-panel__count {
  min-inline-size: 1.75rem;
  text-align: center;
}

.bracelet-allocation-panel__row {
  min-block-size: 2.75rem;
}
</style>
