<script setup>
import BraceletAllocationPanel from '@/components/nightpos/orders/BraceletAllocationPanel.vue'
import BraceletDotsIndicator from '@/components/nightpos/orders/BraceletDotsIndicator.vue'
import { comboRequiredUnits, normalizeOperationalGirls } from '@/composables/useComboAllocation'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  productName: { type: String, default: '' },
  quantity: { type: Number, default: 1 },
  unitsPerCombo: { type: Number, default: 6 },
  requiredUnits: { type: Number, default: 0 },
  girls: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  initialRows: { type: Array, default: () => [] },
  editMode: { type: Boolean, default: false },
  canQuickCreateGirl: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'save', 'girl-created', 'update:quantity'])

const step = ref('split-choice')

const localQuantity = ref(1)

watch(
  () => props.quantity,
  value => {
    localQuantity.value = Math.max(1, Number(value) || 1)
  },
  { immediate: true },
)

watch(localQuantity, value => {
  emit('update:quantity', Math.max(1, Number(value) || 1))
})

const totalUnits = computed(() =>
  props.requiredUnits > 0
    ? props.requiredUnits
    : comboRequiredUnits(localQuantity.value, props.unitsPerCombo),
)

const girlList = computed(() => normalizeOperationalGirls(props.girls))

const splitOptions = [
  { title: 'Todas para una sola chica', value: 'single', subtitle: 'Recomendado' },
  { title: 'Repartir entre varias chicas', value: 'multi', subtitle: null },
]

const splitChoice = ref('single')

watch(
  () => props.modelValue,
  open => {
    if (!open)
      return
    if (props.editMode)
      step.value = 'multi'
    else
      step.value = 'split-choice'
    splitChoice.value = 'single'
  },
)

const close = () => emit('update:modelValue', false)

const goBack = () => {
  if (props.editMode) {
    close()
    return
  }
  if (step.value === 'single-girl' || step.value === 'multi')
    step.value = 'split-choice'
  else
    close()
}

const onToolbarNav = () => {
  if (props.editMode || (step.value === 'split-choice'))
    close()
  else
    goBack()
}

const continueFromSplit = () => {
  step.value = splitChoice.value === 'single' ? 'single-girl' : 'multi'
}

const selectSingleGirl = girlId => {
  emit('save', [{ girl_user_id: Number(girlId), units: totalUnits.value }])
}

const onMultiSave = allocations => {
  emit('save', allocations)
}

const onGirlCreated = girl => {
  emit('girl-created', girl)
}

const toolbarTitle = computed(() => {
  if (props.editMode)
    return 'Editar reparto'
  if (step.value === 'split-choice')
    return 'Repartir manillas'
  if (step.value === 'single-girl')
    return '¿Para quién?'
  return 'Repartir manillas'
})
</script>

<template>
  <VDialog
    :model-value="modelValue"
    fullscreen
    transition="dialog-bottom-transition"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="combo-allocation-dialog">
      <VToolbar color="primary">
        <VBtn
          icon
          @click="onToolbarNav"
        >
          <VIcon :icon="editMode || step === 'split-choice' ? 'ri-close-line' : 'ri-arrow-left-line'" />
        </VBtn>
        <VToolbarTitle>{{ toolbarTitle }}</VToolbarTitle>
      </VToolbar>

      <VCardText class="pt-4 pb-6">
        <p class="text-h6 mb-1">
          {{ productName }}
        </p>

        <div
          v-if="localQuantity > 1"
          class="text-body-1 mb-3"
        >
          {{ localQuantity }} combos =
          <strong>{{ totalUnits }} manillas</strong>
        </div>
        <div
          v-else
          class="text-body-1 mb-3"
        >
          Este combo genera
          <strong>{{ totalUnits }} manillas</strong>
        </div>

        <VTextField
          v-if="!editMode && step === 'split-choice'"
          v-model.number="localQuantity"
          type="number"
          label="Cantidad"
          min="1"
          max="99"
          inputmode="numeric"
          class="mb-4"
        />

        <BraceletDotsIndicator
          v-if="step !== 'single-girl'"
          class="mb-4"
          :total="totalUnits"
          :filled="0"
        />

        <!-- Paso 1: cómo repartir -->
        <template v-if="step === 'split-choice'">
          <p class="text-subtitle-1 font-weight-medium mb-3">
            ¿Cómo quieres repartirlas?
          </p>
          <VRadioGroup
            v-model="splitChoice"
            class="mb-4"
          >
            <VRadio
              v-for="opt in splitOptions"
              :key="opt.value"
              :value="opt.value"
            >
              <template #label>
                <span>{{ opt.title }}</span>
                <VChip
                  v-if="opt.subtitle"
                  size="x-small"
                  color="success"
                  variant="tonal"
                  class="ms-2"
                >
                  {{ opt.subtitle }}
                </VChip>
              </template>
            </VRadio>
          </VRadioGroup>
          <VBtn
            color="primary"
            size="x-large"
            block
            @click="continueFromSplit"
          >
            Continuar
          </VBtn>
        </template>

        <!-- Caso A: una sola chica -->
        <template v-else-if="step === 'single-girl'">
          <p class="text-body-2 text-medium-emphasis mb-4">
            Toca la chica — se asignan las {{ totalUnits }} manillas al instante.
          </p>
          <VRow>
            <VCol
              v-for="girl in girlList"
              :key="girl.id"
              cols="6"
              sm="4"
            >
              <VBtn
                variant="tonal"
                size="x-large"
                block
                class="combo-allocation-dialog__girl-btn"
                :loading="loading"
                @click="selectSingleGirl(girl.id)"
              >
                {{ girl.name }}
              </VBtn>
            </VCol>
          </VRow>
          <VAlert
            v-if="!girlList.length"
            type="warning"
            variant="tonal"
            class="mt-4"
          >
            No hay chicas del turno disponibles.
          </VAlert>
        </template>

        <!-- Caso B: varias chicas -->
        <template v-else-if="step === 'multi'">
          <BraceletAllocationPanel
            :required-units="totalUnits"
            :quantity="localQuantity"
            :units-per-combo="unitsPerCombo"
            :girls="girls"
            :loading="loading"
            :initial-rows="initialRows"
            :can-quick-create-girl="canQuickCreateGirl"
            @save="onMultiSave"
            @cancel="editMode ? close() : goBack()"
            @girl-created="onGirlCreated"
          />
        </template>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.combo-allocation-dialog__girl-btn {
  min-block-size: 3.5rem;
  white-space: normal;
  line-height: 1.2;
}
</style>
