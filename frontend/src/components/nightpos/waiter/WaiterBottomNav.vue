<script setup>
const route = useRoute()

const tabs = [
  { title: 'Mesas', icon: 'ri-layout-grid-line', to: 'nightpos-waiter' },
  { title: 'Comandas', icon: 'ri-restaurant-line', to: 'nightpos-waiter-orders' },
]

const isActive = name => route.name === name
</script>

<template>
  <VSheet
    class="waiter-bottom-nav d-flex"
    elevation="8"
    rounded="0"
  >
    <VBtn
      v-for="tab in tabs"
      :key="tab.to"
      class="waiter-bottom-nav__btn flex-grow-1"
      variant="text"
      :color="isActive(tab.to) ? 'primary' : undefined"
      :to="{ name: tab.to }"
      stacked
    >
      <VIcon :icon="tab.icon" />
      <span class="text-caption">{{ tab.title }}</span>
    </VBtn>
  </VSheet>
</template>

<style scoped>
.waiter-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 10;
  padding-bottom: env(safe-area-inset-bottom, 0);
  min-height: 64px;
}

.waiter-bottom-nav__btn {
  min-height: 56px;
  border-radius: 0;
}
</style>
