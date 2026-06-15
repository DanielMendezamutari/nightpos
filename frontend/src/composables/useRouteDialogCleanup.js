import { onBeforeUnmount } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'

/**
 * Cierra diálogos al navegar o desmontar (evita scrims huérfanos de VDialog).
 * @param {...import('vue').Ref<boolean>} dialogRefs
 */
export function useRouteDialogCleanup(...dialogRefs) {
  const closeAll = () => {
    for (const dialogRef of dialogRefs) {
      if (dialogRef && typeof dialogRef === 'object' && 'value' in dialogRef)
        dialogRef.value = false
    }
  }

  onBeforeRouteLeave(closeAll)
  onBeforeUnmount(closeAll)
}
