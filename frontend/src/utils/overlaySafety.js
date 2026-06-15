/**
 * Limpia overlays residuales que bloquean clicks (layout scrim + Vuetify).
 */
export function countBlockingOverlays() {
  if (typeof document === 'undefined')
    return 0

  const layout = document.querySelectorAll('.layout-overlay.visible').length
  const vuetify = document.querySelectorAll('.v-overlay--active').length

  return layout + vuetify
}

export function dismissStrayOverlays() {
  if (typeof document === 'undefined')
    return

  document.querySelectorAll('.layout-overlay.visible').forEach((el) => {
    el.classList.remove('visible')
  })

  document.dispatchEvent(new KeyboardEvent('keydown', {
    key: 'Escape',
    code: 'Escape',
    bubbles: true,
  }))
}

export function setupOverlaySafety(router) {
  router.afterEach(() => {
    nextTick(() => {
      dismissStrayOverlays()
    })
  })
}
