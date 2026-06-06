<script setup>
import { useTheme } from 'vuetify'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import TheCustomizer from '@core/components/TheCustomizer.vue'
import initCore from '@core/initCore'
import {
  initConfigStore,
  useConfigStore,
} from '@core/stores/config'
import { useShowMaterializeCustomizer } from '@/composables/useShowMaterializeCustomizer'
import NightPosGlobalSnackbar from '@/components/nightpos/layout/NightPosGlobalSnackbar.vue'
import { hexToRgb } from '@layouts/utils'

const { global } = useTheme()

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()
const { showMaterializeCustomizer } = useShowMaterializeCustomizer()
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      <NightPosGlobalSnackbar />
      <ScrollToTop />
      <TheCustomizer v-if="showMaterializeCustomizer" />
    </VApp>
  </VLocaleProvider>
</template>
