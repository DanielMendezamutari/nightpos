/**
 * Enter = confirm, Esc = cancel for operational dialogs.
 * Skips Enter when focus is on textarea or when confirm is disabled/loading.
 */
export function useDialogKeyboardShortcuts(options) {
  const {
    active,
    onConfirm,
    onCancel,
    canConfirm = () => true,
    loading,
  } = options

  const onKeydown = event => {
    if (!active.value)
      return

    if (event.key === 'Escape') {
      event.preventDefault()
      onCancel?.()

      return
    }

    if (event.key !== 'Enter' || event.shiftKey)
      return

    if (event.target?.tagName === 'TEXTAREA')
      return

    if (loading?.value || !canConfirm())
      return

    event.preventDefault()
    onConfirm?.()
  }

  onMounted(() => window.addEventListener('keydown', onKeydown))
  onUnmounted(() => window.removeEventListener('keydown', onKeydown))
}
