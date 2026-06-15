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
        v-model="form.branch.name"
        label="Nombre sucursal inicial"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.branch.code"
        label="Código sucursal"
        hint="Ej: CENTRO"
        persistent-hint
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol cols="12">
      <VTextField
        v-model="form.branch.address"
        label="Dirección (opcional)"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.admin.name"
        label="Nombre administrador"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.admin.username"
        label="Usuario administrador"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.admin.email"
        label="Email (opcional)"
        type="email"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.admin.password"
        label="Contraseña"
        type="password"
        :rules="[v => !!v || 'Requerido', v => (v?.length >= 6) || 'Mínimo 6 caracteres']"
      />
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.admin.pin"
        label="PIN operativo (opcional)"
        hint="4 a 6 dígitos"
        persistent-hint
      />
    </VCol>
  </VRow>
</template>
