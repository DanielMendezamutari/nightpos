<script setup>
import ProductPricingFields from '@/components/nightpos/catalog/ProductPricingFields.vue'
import QuickCategoryCreateDialog from '@/components/nightpos/catalog/QuickCategoryCreateDialog.vue'
import { productPreviewLabel } from '@/composables/useProductForm'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { preventNumberWheelScroll } from '@/composables/usePreventNumberWheel'

const form = defineModel({ type: Object, required: true })

defineProps({
  mode: {
    type: String,
    default: 'create',
  },
  categories: {
    type: Array,
    default: () => [],
  },
  saving: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const { can } = useNightPosPermissions()

const refForm = ref()
const showQuickCategory = ref(false)

const preview = computed(() => productPreviewLabel(form.value))

const behaviorOptions = [
  { title: 'Producto normal', value: false },
  { title: 'Combo con manillas', value: true },
]

const onBehaviorChange = isCombo => {
  form.value.is_combo = isCombo
  if (isCombo && (!form.value.bracelet_units_per_line || form.value.bracelet_units_per_line < 1))
    form.value.bracelet_units_per_line = 6
  if (isCombo && form.value.unit === 'unit')
    form.value.unit = 'combo'
}

const onCategoryCreated = category => {
  if (category?.id)
    form.value.category_id = category.id
}

const submit = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  if (form.value.is_combo && Number(form.value.bracelet_units_per_line) < 1) {
    return
  }

  emit('submit')
}

defineExpose({ submit })
</script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent="submit"
  >
    <VAlert
      type="info"
      variant="tonal"
      class="mb-4"
      prominent
    >
      <div class="text-caption text-medium-emphasis">
        Vista previa
      </div>
      <div class="text-subtitle-1 font-weight-medium">
        {{ preview }}
      </div>
    </VAlert>

    <div class="text-subtitle-2 font-weight-bold mb-2">
      Información
    </div>
    <VRow>
      <VCol cols="12" md="6">
        <VTextField
          v-model="form.name"
          label="Nombre"
          placeholder="Ej. Paceña, Combo 6 Cervezas"
          :rules="[v => !!v?.trim() || 'Requerido']"
        />
      </VCol>
      <VCol cols="12" md="6">
        <VSelect
          v-model="form.category_id"
          label="Categoría"
          :items="categories.map(c => ({ title: c.name, value: c.id }))"
          :rules="[v => !!v || 'Requerido']"
        >
          <template
            v-if="can('product-categories.create')"
            #append-item
          >
            <VDivider class="my-2" />
            <VListItem
              prepend-icon="ri-folder-add-line"
              title="+ Nueva categoría"
              class="text-primary"
              @click="showQuickCategory = true"
            />
          </template>
        </VSelect>
      </VCol>
      <VCol cols="12" md="4">
        <VTextField
          v-model="form.sku"
          label="Código (opcional)"
          placeholder="SKU interno"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VSelect
          v-model="form.status"
          label="Estado"
          :items="[
            { title: 'Activo', value: 'active' },
            { title: 'Inactivo', value: 'inactive' },
          ]"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VSelect
          v-model="form.product_type"
          label="Tipo"
          :items="[
            { title: 'Bebida', value: 'beverage' },
            { title: 'Servicio', value: 'service' },
            { title: 'Comida', value: 'food' },
          ]"
        />
      </VCol>
    </VRow>

    <VDivider class="my-4" />

    <ProductPricingFields v-model="form" />

    <VDivider class="my-4" />

    <div class="text-subtitle-2 font-weight-bold mb-2">
      Comportamiento
    </div>
    <VRadioGroup
      :model-value="form.is_combo"
      inline
      @update:model-value="onBehaviorChange"
    >
      <VRadio
        v-for="opt in behaviorOptions"
        :key="String(opt.value)"
        :label="opt.title"
        :value="opt.value"
      />
    </VRadioGroup>

    <VExpandTransition>
      <div v-if="form.is_combo">
        <VAlert
          type="warning"
          variant="tonal"
          density="compact"
          class="mb-3"
        >
          Al vender este producto con acompañante, el garzón deberá repartir exactamente las manillas indicadas entre una o más chicas antes de enviar a barra o cobrar.
        </VAlert>
        <VRow>
          <VCol cols="12" md="4">
            <VTextField
              v-model.number="form.bracelet_units_per_line"
              type="number"
              min="1"
              label="Cantidad de manillas por combo"
              :rules="[v => Number(v) >= 1 || 'Mínimo 1']"
              @wheel="preventNumberWheelScroll"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.unit"
              label="Unidad"
              hint="Ej. combo"
              persistent-hint
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              model-value="GIRL_BRACELET_ALLOCATION"
              label="Comportamiento liquidación"
              readonly
              hint="Combo con reparto de manillas"
              persistent-hint
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              model-value="GIRL_BRACELET_UNITS"
              label="Tipo allocation"
              readonly
              hint="Asignado automáticamente"
              persistent-hint
            />
          </VCol>
        </VRow>
      </div>
    </VExpandTransition>

    <slot name="actions" />

    <QuickCategoryCreateDialog
      v-model="showQuickCategory"
      @created="onCategoryCreated"
    />
  </VForm>
</template>
