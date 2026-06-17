import { onMounted } from 'vue'
import { useOperationalEvents } from '@/composables/useOperationalEvents'

/**
 * Mantiene la conexión SSE activa mientras el layout operativo está montado.
 * Las páginas hijas registran handlers sin depender de su propio ciclo de vida.
 */
export function useOperationalSseHost() {
  const { start } = useOperationalEvents()

  onMounted(() => start())
}
