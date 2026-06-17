<script setup>
import { useCashierShellTabs } from '@/utils/cashierShellNav'

defineProps({
  activeTab: { type: String, default: 'cobrar' },
})

const { visibleTabs } = useCashierShellTabs()
</script>

<template>
  <VSheet
    class="cashier-bottom-nav d-flex d-md-none"
    elevation="8"
    rounded="0"
  >
    <VBtn
      v-for="tab in visibleTabs"
      :key="tab.key"
      class="cashier-bottom-nav__btn flex-grow-1"
      variant="text"
      :color="activeTab === tab.key ? 'primary' : undefined"
      :to="{ name: tab.to }"
      stacked
    >
      <VIcon :icon="tab.icon" />
      <span class="text-caption">{{ tab.title }}</span>
    </VBtn>
  </VSheet>
</template>

<style scoped>
.cashier-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 10;
  padding-bottom: env(safe-area-inset-bottom, 0);
  min-height: 64px;
}

.cashier-bottom-nav__btn {
  min-height: 56px;
  border-radius: 0;
}
</style>
