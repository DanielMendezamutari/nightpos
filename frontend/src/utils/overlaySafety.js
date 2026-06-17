import { nextTick } from 'vue'

/**
 * Limpia overlays residuales que bloquean clicks (layout scrim + Vuetify).
 */
export function countBlockingOverlays() {
  if (typeof document === 'undefined')
    return 0

  const layout = document.querySelectorAll('.layout-overlay.visible').length
  const vuetify = document.querySelectorAll('.v-overlay--active').length
  const scrollBlocked = document.documentElement.classList.contains('v-overlay-scroll-blocked') ? 1 : 0

  return layout + vuetify + scrollBlocked
}

function clearVuetifyScrollLock() {
  const html = document.documentElement
  const body = document.body

  html.classList.remove('v-overlay-scroll-blocked')
  html.removeAttribute('inert')
  html.style.removeProperty('--v-body-scroll-y')
  html.style.removeProperty('--v-scrollbar-offset')
  body.style.removeProperty('pointer-events')
  body.style.removeProperty('overflow')
}

function deactivateVuetifyOverlays() {
  document.querySelectorAll('.v-overlay.v-overlay--active').forEach((el) => {
    el.classList.remove('v-overlay--active')
    el.style.setProperty('display', 'none', 'important')
  })
}

export function dismissStrayOverlays() {
  if (typeof document === 'undefined')
    return

  if (document.activeElement instanceof HTMLElement)
    document.activeElement.blur()

  document.querySelectorAll('.layout-overlay.visible').forEach((el) => {
    el.classList.remove('visible')
  })

  document.dispatchEvent(new KeyboardEvent('keydown', {
    key: 'Escape',
    code: 'Escape',
    bubbles: true,
  }))

  clearVuetifyScrollLock()
  deactivateVuetifyOverlays()
}

export function setupOverlaySafety(router) {
  router.afterEach(() => {
    nextTick(() => {
      dismissStrayOverlays()
    })
  })
}
