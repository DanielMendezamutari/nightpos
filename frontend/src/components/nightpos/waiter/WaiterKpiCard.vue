<script setup>
/**
 * KPI táctil inspirado en CardStatisticsWithIcon (@core).
 */
const props = defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: '' },
  subtitle: { type: String, default: '' },
  icon: { type: String, required: true },
  color: { type: String, default: 'primary' },
  highlight: { type: Boolean, default: false },
})

const emit = defineEmits(['click'])
</script>

<template>
  <VCard
    class="waiter-kpi-card"
    :class="{ 'waiter-kpi-card--highlight': highlight }"
    variant="elevated"
    @click="emit('click')"
  >
    <VCardText class="d-flex align-center gap-3 pa-4">
      <VAvatar
        :color="color"
        variant="tonal"
        rounded="lg"
        size="48"
      >
        <VIcon
          :icon="icon"
          size="26"
        />
      </VAvatar>
      <div class="flex-grow-1 min-w-0">
        <div
          v-if="value !== '' && value !== null"
          class="text-h4 font-weight-bold"
        >
          {{ value }}
        </div>
        <div class="text-body-1 font-weight-medium">
          {{ title }}
        </div>
        <div
          v-if="subtitle"
          class="text-caption text-medium-emphasis"
        >
          {{ subtitle }}
        </div>
      </div>
      <VIcon
        icon="ri-arrow-right-s-line"
        class="text-medium-emphasis"
      />
    </VCardText>
  </VCard>
</template>

<style scoped>
.waiter-kpi-card {
  cursor: pointer;
  border-radius: 12px;
  transition: transform 0.15s ease;
}

.waiter-kpi-card:active {
  transform: scale(0.98);
}

.waiter-kpi-card--highlight {
  border: 2px solid rgb(var(--v-theme-primary));
}
</style>
