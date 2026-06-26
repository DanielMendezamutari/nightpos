<script setup>
/**
 * InstallPwaBanner — promueve instalar NightPOS como PWA.
 *
 * Android/Chrome/Edge: captura `beforeinstallprompt`, muestra botón "Instalar app".
 * iOS Safari: no tiene prompt nativo; muestra instrucciones manuales.
 *
 * Props:
 *   context  'waiter' | 'cashier'  — etiqueta del nombre de la app.
 *
 * El banner se oculta permanentemente si el usuario lo descarta.
 * Clave de localStorage: `nightpos_pwa_install_dismissed_${context}`
 *
 * No se muestra si:
 *   - Ya está en modo standalone (ya instalado).
 *   - El usuario ya lo descartó.
 */
const props = defineProps({
  context: {
    type: String,
    default: 'waiter',
    validator: v => ['waiter', 'cashier'].includes(v),
  },
})

const appName = computed(() =>
  props.context === 'waiter' ? 'NightPOS Garzón' : 'NightPOS Caja',
)

const dismissKey = computed(() =>
  `nightpos_pwa_install_dismissed_${props.context}`,
)

const isStandalone = ref(false)
const isIOS = ref(false)
const isDismissed = ref(false)
const deferredPrompt = ref(null)
const showIOSInstructions = ref(false)

onMounted(() => {
  isStandalone.value
    = window.matchMedia('(display-mode: standalone)').matches
    || navigator.standalone === true

  isIOS.value = /iphone|ipad|ipod/i.test(navigator.userAgent)
    && !window.MSStream

  isDismissed.value
    = localStorage.getItem(dismissKey.value) === '1'

  window.addEventListener('beforeinstallprompt', handleBeforeInstall)
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstall)
})

function handleBeforeInstall(e) {
  e.preventDefault()
  deferredPrompt.value = e
}

const showBanner = computed(() => {
  if (isStandalone.value || isDismissed.value)
    return false

  // Android/Chrome/Edge: only if we have the deferred prompt.
  if (!isIOS.value)
    return Boolean(deferredPrompt.value)

  // iOS: always show if not standalone and not dismissed.
  return true
})

async function install() {
  if (isIOS.value) {
    showIOSInstructions.value = true
    return
  }

  if (!deferredPrompt.value)
    return

  deferredPrompt.value.prompt()
  const { outcome } = await deferredPrompt.value.userChoice
  if (outcome === 'accepted')
    isDismissed.value = true

  deferredPrompt.value = null
}

function dismiss() {
  isDismissed.value = true
  localStorage.setItem(dismissKey.value, '1')
  showIOSInstructions.value = false
}
</script>

<template>
  <!-- Android / Chrome / Edge prompt -->
  <VAlert
    v-if="showBanner && !isIOS"
    type="info"
    variant="tonal"
    density="compact"
    closable
    class="mb-3 nightpos-install-banner"
    icon="ri-download-2-line"
    @click:close="dismiss"
  >
    <div class="d-flex align-center justify-space-between flex-wrap gap-2">
      <span class="text-body-2">
        Instala <strong>{{ appName }}</strong> para acceso directo desde tu pantalla.
      </span>
      <VBtn
        size="small"
        color="primary"
        variant="tonal"
        prepend-icon="ri-add-line"
        @click="install"
      >
        Instalar
      </VBtn>
    </div>
  </VAlert>

  <!-- iOS: trigger instructions dialog -->
  <VAlert
    v-else-if="showBanner && isIOS"
    type="info"
    variant="tonal"
    density="compact"
    closable
    class="mb-3 nightpos-install-banner"
    icon="ri-smartphone-line"
    @click:close="dismiss"
  >
    <div class="d-flex align-center justify-space-between flex-wrap gap-2">
      <span class="text-body-2">
        Instala <strong>{{ appName }}</strong> en tu iPhone/iPad.
      </span>
      <VBtn
        size="small"
        color="primary"
        variant="tonal"
        prepend-icon="ri-information-line"
        @click="showIOSInstructions = true"
      >
        Cómo instalar
      </VBtn>
    </div>
  </VAlert>

  <!-- iOS instructions dialog -->
  <VDialog
    v-model="showIOSInstructions"
    max-width="360"
  >
    <VCard>
      <VCardTitle class="text-h6 pt-4 px-4">
        Instalar {{ appName }}
      </VCardTitle>

      <VCardText class="px-4 pb-2">
        <p class="text-body-2 mb-3">
          Safari no tiene botón de instalación automático. Sigue estos pasos:
        </p>

        <ol class="text-body-2 ps-4">
          <li class="mb-2">
            Toca el botón
            <VIcon
              icon="ri-share-line"
              size="16"
              class="mx-1"
            />
            <strong>Compartir</strong> en la barra inferior de Safari.
          </li>
          <li class="mb-2">
            Desplázate y toca
            <strong>«Agregar a pantalla de inicio»</strong>.
          </li>
          <li>
            Toca <strong>«Agregar»</strong> — aparecerá el ícono de NightPOS.
          </li>
        </ol>

        <VAlert
          type="warning"
          variant="tonal"
          density="compact"
          class="mt-3"
        >
          Debes estar en <strong>Safari</strong>. Chrome para iOS no permite instalar PWAs.
        </VAlert>
      </VCardText>

      <VCardActions class="px-4 pb-4">
        <VSpacer />
        <VBtn
          color="primary"
          @click="dismiss"
        >
          Entendido
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
.nightpos-install-banner {
  font-size: 0.875rem;
}
</style>
