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
import { usePwaManifest } from '@/composables/usePwaManifest'
import { useSwUpdate } from '@/composables/useSwUpdate'
import { isPwaEnabled } from '@/utils/pwaEnabled'

const { global } = useTheme()

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()
const { showMaterializeCustomizer } = useShowMaterializeCustomizer()

const pwaEnabled = isPwaEnabled()

if (pwaEnabled) {
  usePwaManifest()
}

const { needsUpdate, applyUpdate } = pwaEnabled ? useSwUpdate() : { needsUpdate: ref(false), applyUpdate: () => {} }
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      <NightPosGlobalSnackbar />
      <ScrollToTop />
      <TheCustomizer v-if="showMaterializeCustomizer" />

      <!-- PWA update notification -->
      <VSnackbar
        v-if="pwaEnabled && needsUpdate"
        :model-value="needsUpdate"
        location="bottom"
        color="primary"
        timeout="-1"
        multi-line
      >
        <span>Nueva versión disponible.</span>
        <template #actions>
          <VBtn
            variant="text"
            @click="applyUpdate"
          >
            Actualizar
          </VBtn>
        </template>
      </VSnackbar>
    </VApp>
  </VLocaleProvider>
</template>
