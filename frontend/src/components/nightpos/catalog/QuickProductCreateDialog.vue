<script setup>
import { fetchCategories } from '@/api/categories'
import { quickCreateProduct } from '@/api/products'
import ProductPricingFields from '@/components/nightpos/catalog/ProductPricingFields.vue'
import QuickCategoryCreateDialog from '@/components/nightpos/catalog/QuickCategoryCreateDialog.vue'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'created'])

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const saving = ref(false)
const refForm = ref()
const categories = ref([])
const showQuickCategory = ref(false)

const form = ref({
  name: '',
  category_id: null,
  solo_price: null,
  companion_price: null,
  girl_amount: null,
  house_amount: null,
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = {
    name: '',
    category_id: null,
    solo_price: null,
    companion_price: null,
    girl_amount: null,
    house_amount: null,
  }
  refForm.value?.resetValidation?.()
}

const reloadCategories = async () => {
  try {
    categories.value = await fetchCategories()
  }
  catch {
    categories.value = []
  }
}

watch(() => props.modelValue, async open => {
  if (open) {
    reset()
    await reloadCategories()
  }
})

const onCategoryCreated = async category => {
  await reloadCategories()
  if (category?.id)
    form.value.category_id = category.id
}

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    const payload = {
      name: form.value.name.trim(),
      category_id: form.value.category_id,
      solo_price: Number(form.value.solo_price),
    }

    const companion = Number(form.value.companion_price)
    if (companion > 0) {
      payload.companion_price = companion
      payload.girl_amount = Number(form.value.girl_amount)
      payload.house_amount = Number(form.value.house_amount)
    }

    const result = await quickCreateProduct(payload)
    notify('Producto creado')
    emit('created', result)
    close()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="520"
    persistent
    scrollable
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-cup-line" />
        Nuevo producto rápido
      </VCardTitle>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VTextField
            v-model="form.name"
            label="Nombre *"
            autofocus
            :rules="[v => !!v?.trim() || 'Requerido']"
            class="mb-3"
          />

          <VAutocomplete
            v-model="form.category_id"
            :items="categories.map(c => ({ title: c.name, value: c.id }))"
            label="Categoría *"
            :rules="[v => !!v || 'Requerido']"
            class="mb-3"
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
          </VAutocomplete>

          <ProductPricingFields v-model="form" />
        </VForm>
      </VCardText>
      <VCardActions>
        <VBtn
          variant="text"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VSpacer />
        <VBtn
          color="primary"
          :loading="saving"
          @click="save"
        >
          Crear y usar
        </VBtn>
      </VCardActions>
    </VCard>

    <QuickCategoryCreateDialog
      v-model="showQuickCategory"
      @created="onCategoryCreated"
    />
  </VDialog>
</template>
