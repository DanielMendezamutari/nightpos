<script setup>
const form = defineModel({ type: Object, required: true })

const TYPE_ITEMS = [
  { title: 'General', value: 'general' },
  { title: 'Bebidas', value: 'beverage' },
  { title: 'Servicios', value: 'service' },
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
        label="Nombre"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.type"
        label="Tipo"
        :items="TYPE_ITEMS"
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
          { title: 'Activa', value: 'active' },
          { title: 'Inactiva', value: 'inactive' },
        ]"
      />
    </VCol>
  </VRow>
</template>
