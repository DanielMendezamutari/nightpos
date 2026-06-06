<script setup>
import { preventNumberWheelScroll } from '@/composables/usePreventNumberWheel'

const props = defineProps({
  showCompanionFields: { type: Boolean, default: true },
  soloRequired: { type: Boolean, default: true },
})

const form = defineModel({
  type: Object,
  required: true,
})

const showCompanion = computed(() => {
  if (!props.showCompanionFields)
    return false

  return Number(form.value?.companion_price ?? 0) > 0
})
</script>

<template>
  <div class="product-pricing-fields">
    <p class="text-subtitle-1 font-weight-medium mb-3">
      Precios boliche
    </p>
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model.number="form.solo_price"
          type="number"
          label="Precio cliente (BOB)"
          min="0.01"
          step="0.01"
          :rules="props.soloRequired ? [v => v > 0 || 'Mayor a 0'] : []"
          @wheel="preventNumberWheelScroll"
        />
      </VCol>
      <VCol
        v-if="props.showCompanionFields"
        cols="12"
        md="6"
      >
        <VTextField
          v-model.number="form.companion_price"
          type="number"
          label="Precio con acompañante (opcional)"
          min="0"
          step="0.01"
          hint="Deje vacío si no aplica"
          persistent-hint
          @wheel="preventNumberWheelScroll"
        />
      </VCol>
      <template v-if="showCompanion">
        <VCol
          cols="12"
          md="6"
        >
          <VTextField
            v-model.number="form.girl_amount"
            type="number"
            label="Monto chica (BOB)"
            min="0"
            step="0.01"
            :rules="[v => v >= 0 || 'Requerido']"
            @wheel="preventNumberWheelScroll"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <VTextField
            v-model.number="form.house_amount"
            type="number"
            label="Monto casa (BOB)"
            min="0"
            step="0.01"
            :rules="[v => v >= 0 || 'Requerido']"
            @wheel="preventNumberWheelScroll"
          />
        </VCol>
      </template>
    </VRow>
  </div>
</template>
