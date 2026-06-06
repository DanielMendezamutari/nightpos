<script setup>
const form = defineModel({ type: Object, required: true })
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
      <VTextField
        v-model="form.code"
        label="Código"
        hint="Ej: CENTRO"
        persistent-hint
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol cols="12">
      <VTextField
        v-model="form.address"
        label="Dirección"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.status"
        :items="[
          { title: 'Activa', value: 'active' },
          { title: 'Inactiva', value: 'inactive' },
        ]"
        label="Estado"
      />
    </VCol>
  </VRow>
</template>
