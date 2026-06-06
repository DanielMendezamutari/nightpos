/**
 * Distingue shell operativo NightPOS vs rutas demo Materialize (conservadas en repo).
 */
export function useNightPosShell() {
  const route = useRoute()

  const isMaterializeDemoRoute = computed(() => {
    const path = route.path

    return path.startsWith('/pages/')
      || path.startsWith('/apps/')
      || path.startsWith('/dashboards/')
      || path.startsWith('/charts/')
      || path.startsWith('/forms/')
      || path.startsWith('/wizard-examples/')
      || path.startsWith('/front-pages/')
  })

  const showNightPosChrome = computed(() => !isMaterializeDemoRoute.value)

  return {
    isMaterializeDemoRoute,
    showNightPosChrome,
  }
}
