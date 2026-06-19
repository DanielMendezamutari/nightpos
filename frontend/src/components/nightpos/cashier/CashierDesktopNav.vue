<script setup>

import { useCashierShellTabs } from '@/utils/cashierShellNav'



defineProps({

  activeTab: { type: String, default: 'cobrar' },

})



const { visibleTabs } = useCashierShellTabs()



const desktopTitles = {

  venta: 'Venta directa',

  caja: 'Mi caja',

}

</script>



<template>

  <VToolbar

    class="cashier-desktop-nav d-none d-md-flex px-4"

    density="comfortable"

    color="surface"

    elevation="1"

  >

    <VBtn

      v-for="tab in visibleTabs"

      :key="tab.key"

      class="me-1"

      :color="activeTab === tab.key ? 'primary' : undefined"

      :variant="activeTab === tab.key ? 'flat' : 'text'"

      :to="{ name: tab.to }"

      :prepend-icon="tab.icon"

    >

      {{ desktopTitles[tab.key] ?? tab.title }}

    </VBtn>

  </VToolbar>

</template>



<style scoped>

.cashier-desktop-nav {

  position: sticky;

  top: 0;

  z-index: 9;

  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));

}

</style>

