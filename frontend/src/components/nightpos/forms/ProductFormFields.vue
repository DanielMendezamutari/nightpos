<script setup>
const form = defineModel({ type: Object, required: true })

defineProps({
  categories: { type: Array, default: () => [] },
  showQuickCategory: { type: Boolean, default: false },
})

defineEmits(['new-category'])

const PRODUCT_TYPES = [
  { title: 'Bebida', value: 'beverage' },
  { title: 'Servicio', value: 'service' },
  { title: 'Comida', value: 'food' },
]
</script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.name"
        label="Nombre del producto"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.product_type"
        label="Tipo"
        :items="PRODUCT_TYPES"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.category_id"
        label="Categoría"
        :items="categories.map(c => ({ title: c.name, value: c.id }))"
        clearable
      >
        <template
          v-if="showQuickCategory"
          #append-item
        >
          <VDivider class="my-2" />
          <VListItem
            prepend-icon="ri-folder-add-line"
            title="+ Nueva categoría"
            class="text-primary"
            @click="$emit('new-category')"
          />
        </template>
      </VSelect>
      <VBtn
        v-if="showQuickCategory"
        variant="text"
        size="small"
        prepend-icon="ri-folder-add-line"
        class="mt-1 px-0"
        @click="$emit('new-category')"
      >
        + Nueva categoría
      </VBtn>
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.unit"
        label="Unidad"
        hint="unit, botella, etc."
        persistent-hint
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.status"
        label="Estado"
        :items="[
          { title: 'Activo', value: 'active' },
          { title: 'Inactivo', value: 'inactive' },
        ]"
      />
    </VCol>
  </VRow>
</template>
