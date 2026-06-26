/**
 * Switches the PWA manifest link dynamically based on the current route context.
 *
 * - Waiter routes  (/nightpos/waiter*) → /manifest-waiter.webmanifest
 *   name: "NightPOS Garzón", start_url: /nightpos/waiter, orientation: portrait
 *
 * - All other routes → /manifest.webmanifest
 *   name: "NightPOS Caja", start_url: /login, orientation: any
 *
 * Must be called once in App.vue (or a top-level component) so the <link> tag
 * is swapped before the browser evaluates install eligibility.
 *
 * Note: changing the manifest after the page loads is honoured by Chrome for
 * the install prompt purpose; the installed app retains whatever start_url was
 * active when the user accepted the install.
 *
 * No-op when VITE_PWA_ENABLED=false.
 */
import { isPwaEnabled } from '@/utils/pwaEnabled'

export function usePwaManifest() {
  if (!isPwaEnabled())
    return { isWaiterContext: ref(false), manifestHref: ref('') }

  const route = useRoute()

  const isWaiterContext = computed(() =>
    route.path.startsWith('/nightpos/waiter'),
  )

  const manifestHref = computed(() =>
    isWaiterContext.value
      ? '/manifest-waiter.webmanifest'
      : '/manifest.webmanifest',
  )

  watch(
    manifestHref,
    href => {
      if (typeof document === 'undefined')
        return

      const link = document.getElementById('pwa-manifest')

      if (link)
        link.setAttribute('href', href)
    },
    { immediate: true },
  )

  return { isWaiterContext, manifestHref }
}
