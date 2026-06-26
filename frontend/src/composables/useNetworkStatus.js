/**
 * Detects online/offline status.
 *
 * Uses @vueuse/core `useOnline` (reliable cross-browser event listener) and
 * exposes `isOnline`, `isOffline`, and a snackbar-friendly `offlineMessage`.
 *
 * Usage:
 *   const { isOnline, isOffline } = useNetworkStatus()
 *
 * No API calls are made — purely navigator / browser event based.
 * Real connectivity validation (e.g. backend ping) is left to each use case.
 */
export function useNetworkStatus() {
  const isOnline = useOnline()
  const isOffline = computed(() => !isOnline.value)

  const offlineMessage
    = 'Sin conexión — NightPOS necesita internet para comandar y cobrar.'

  return {
    isOnline,
    isOffline,
    offlineMessage,
  }
}
