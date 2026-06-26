/**
 * PWA master switch — set VITE_PWA_ENABLED=false in .env.production to disable
 * service worker, install banner, and manifest swap (SPA-only mode).
 */
export function isPwaEnabled() {
  return import.meta.env.VITE_PWA_ENABLED !== 'false'
}

/**
 * Unregister any service workers left from a previous PWA build.
 * Safe to call when PWA is disabled; no-op when enabled.
 */
export async function unregisterServiceWorkersIfDisabled() {
  if (isPwaEnabled())
    return

  if (!('serviceWorker' in navigator))
    return

  const registrations = await navigator.serviceWorker.getRegistrations()

  await Promise.all(registrations.map(r => r.unregister()))
}
