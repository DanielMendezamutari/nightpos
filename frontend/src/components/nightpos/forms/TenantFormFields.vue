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
        label="Nombre comercial"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.slug"
        label="Slug (URL / login)"
        hint="Ej: casa-norte"
        persistent-hint
        :rules="[v => !!v?.trim() || 'Requerido']"
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
          { title: 'Suspendida', value: 'suspended' },
        ]"
        label="Estado"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.plan_name"
        label="Plan"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.subscription_starts_at"
        label="Suscripción desde"
        type="date"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.subscription_ends_at"
        label="Suscripción hasta"
        type="date"
      />
    </VCol>
  </VRow>
</template>
