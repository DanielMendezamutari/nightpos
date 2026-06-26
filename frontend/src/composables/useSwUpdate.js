/**
 * Tracks service worker update availability and exposes a `reload` function.
 *
 * When vite-plugin-pwa detects a new SW waiting, this composable:
 *   1. Sets `needsUpdate = true`
 *   2. A snackbar/banner in App.vue shows the update prompt
 *   3. `applyUpdate()` calls `updateSW()` which skips the waiting SW and reloads
 *
 * Disabled when VITE_PWA_ENABLED=false.
 */
import { isPwaEnabled } from '@/utils/pwaEnabled'

export function useSwUpdate() {
  const needsUpdate = ref(false)
  const updateSW = ref(null)

  onMounted(async () => {
    if (import.meta.env.DEV || !isPwaEnabled())
      return

    try {
      // `virtual:pwa-register/vue` is resolved by vite-plugin-pwa at build time.
      const { useRegisterSW } = await import('virtual:pwa-register/vue')
      const { needRefresh, updateServiceWorker } = useRegisterSW({
        onRegistered(r) {
          if (r)
            r.update()
        },
      })

      // Bridge vite-plugin-pwa refs to our local state.
      watch(needRefresh, (v) => {
        if (v)
          needsUpdate.value = true
      }, { immediate: true })

      updateSW.value = updateServiceWorker
    }
    catch {
      // Plugin not installed or not in production build — silently ignore.
    }
  })

  async function applyUpdate() {
    if (updateSW.value)
      await updateSW.value(true)
  }

  return { needsUpdate, applyUpdate }
}
