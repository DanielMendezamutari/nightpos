<script setup>
defineProps({
  title: { type: String, default: 'Información' },
  hint: { type: String, default: '' },
})
</script>

<template>
  <VRow>
    <VCol
      cols="12"
      lg="8"
    >
      <VCard>
        <VCardItem v-if="$slots['form-title']">
          <VCardTitle>
            <slot name="form-title" />
          </VCardTitle>
        </VCardItem>
        <VCardText>
          <slot />
        </VCardText>
      </VCard>
      <div
        v-if="$slots.actions"
        class="mt-4"
      >
        <slot name="actions" />
      </div>
    </VCol>
    <VCol
      cols="12"
      lg="4"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>{{ title }}</VCardTitle>
          <VCardSubtitle v-if="hint">
            {{ hint }}
          </VCardSubtitle>
        </VCardItem>
        <VCardText>
          <slot name="aside" />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
