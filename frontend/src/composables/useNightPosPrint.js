export function useNightPosPrint() {
  const router = useRouter()

  const openPrintRoute = (routeLocation, { autoPrint = true } = {}) => {
    const resolved = router.resolve({
      ...routeLocation,
      query: {
        ...(routeLocation.query ?? {}),
        ...(autoPrint ? { print: '1' } : {}),
      },
    })

    const win = window.open(resolved.href, '_blank', 'noopener,noreferrer')

    if (!win) {
      return false
    }

    return true
  }

  const triggerAutoPrint = () => {
    const route = useRoute()

    if (route.query.print === '1') {
      nextTick(() => {
        setTimeout(() => window.print(), 400)
      })
    }
  }

  return { openPrintRoute, triggerAutoPrint }
}
