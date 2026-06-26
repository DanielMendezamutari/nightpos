import { ref } from 'vue'

/** No-op stub when vite-plugin-pwa is not loaded (VITE_PWA_ENABLED=false). */
export function useRegisterSW() {
  return {
    needRefresh: ref(false),
    updateServiceWorker: async () => {},
  }
}
