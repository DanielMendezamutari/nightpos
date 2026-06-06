import { ref } from 'vue'

/**
 * Estado de loading por acción (evita doble clic en botones críticos).
 *
 * @example
 * const { isLoading, run, keyFor } = useActionLoading()
 * await run(keyFor(item.id, 'finish'), async () => { ... })
 */
export function useActionLoading() {
  const loadingKeys = ref(new Set())

  const isLoading = key => loadingKeys.value.has(key)

  const keyFor = (id, action) => `${action}:${id}`

  const run = async (key, fn) => {
    const next = new Set(loadingKeys.value)
    next.add(key)
    loadingKeys.value = next

    try {
      return await fn()
    }
    finally {
      const done = new Set(loadingKeys.value)
      done.delete(key)
      loadingKeys.value = done
    }
  }

  return { isLoading, run, keyFor }
}
