<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: 'Funcionalidad en desarrollo.' },
  module: { type: String, default: 'NightPOS' },
  breadcrumbs: { type: Array, default: () => [] },
  backendNote: { type: String, default: 'Pendiente de endpoint o fase backend.' },
})
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="title"
      :subtitle="subtitle"
      :breadcrumbs="breadcrumbs"
    />
    <VAlert
      type="info"
      variant="tonal"
      prominent
      class="mb-4"
    >
      <VAlertTitle>{{ module }}</VAlertTitle>
      {{ backendNote }}
    </VAlert>
    <VCard>
      <VCardText class="text-center py-12">
        <VIcon
          icon="ri-tools-line"
          size="64"
          color="primary"
          class="mb-4"
        />
        <p class="text-h6 mb-2">
          Próximamente
        </p>
        <p class="text-body-2 text-medium-emphasis mb-0">
          La estructura de menú y rutas ya está preparada para esta sección.
        </p>
      </VCardText>
    </VCard>
  </div>
</template>
