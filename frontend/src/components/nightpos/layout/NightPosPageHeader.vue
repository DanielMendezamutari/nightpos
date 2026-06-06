<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="nightpos-page-header mb-6">
    <VBreadcrumbs
      v-if="breadcrumbs.length"
      :items="breadcrumbs"
      class="pa-0 mb-2"
    />
    <div class="d-flex flex-wrap justify-space-between align-center gap-4">
      <div>
        <h4 class="text-h4 mb-1">
          {{ title }}
        </h4>
        <p
          v-if="subtitle"
          class="mb-0 text-body-2 text-medium-emphasis"
        >
          {{ subtitle }}
        </p>
      </div>
      <div
        v-if="$slots.actions"
        class="d-flex flex-wrap gap-2"
      >
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
