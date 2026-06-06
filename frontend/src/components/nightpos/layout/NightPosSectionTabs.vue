<script setup>
const props = defineProps({
  tabs: {
    type: Array,
    required: true,
  },
})

const route = useRoute()

const activeTab = computed(() => {
  const match = props.tabs.find(t => t.to && route.name === t.to)
  return match?.value ?? props.tabs[0]?.value
})
</script>

<template>
  <VTabs
    :model-value="activeTab"
    class="v-tabs-pill mb-6"
  >
    <VTab
      v-for="tab in tabs"
      :key="tab.value"
      :value="tab.value"
      :to="tab.to ? { name: tab.to } : undefined"
      :disabled="tab.disabled"
    >
      <VIcon
        v-if="tab.icon"
        :icon="tab.icon"
        start
        size="20"
      />
      {{ tab.title }}
      <VChip
        v-if="tab.badge"
        size="x-small"
        class="ms-2"
        label
      >
        {{ tab.badge }}
      </VChip>
    </VTab>
  </VTabs>
</template>
