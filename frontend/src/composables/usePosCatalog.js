import { fetchPosCatalog } from '@/api/products'

const MIN_SEARCH = 2

export function usePosCatalog(options = {}) {
  const sellableOnly = ref(options.sellableOnly ?? true)
  const unpricedOnly = ref(options.unpricedOnly ?? false)
  const limit = ref(options.limit ?? 20)

  const categories = ref([])
  const products = ref([])
  const meta = ref({})
  const loading = ref(false)
  const search = ref('')
  const categoryId = ref(null)
  const productIds = ref([])
  const viewMode = ref(null)

  const categoryMap = computed(() => {
    const map = {}

    categories.value.forEach(c => {
      if (c.id != null)
        map[c.id] = c.name
    })

    return map
  })

  const showResults = computed(() => {
    if (unpricedOnly.value)
      return true

    if (productIds.value.length > 0)
      return true

    if (categoryId.value != null)
      return true

    return search.value.trim().length >= MIN_SEARCH
  })

  const buildParams = () => {
    const params = {
      sellable_only: sellableOnly.value ? 1 : 0,
      unpriced_only: unpricedOnly.value ? 1 : 0,
      limit: limit.value,
    }

    const q = search.value.trim()

    if (q.length >= MIN_SEARCH)
      params.search = q

    if (categoryId.value != null)
      params.category_id = categoryId.value

    if (productIds.value.length > 0)
      params.product_ids = productIds.value.join(',')

    return params
  }

  const loadMeta = async () => {
    loading.value = true

    try {
      const data = await fetchPosCatalog({
        sellable_only: sellableOnly.value ? 1 : 0,
        unpriced_only: unpricedOnly.value ? 1 : 0,
        limit: limit.value,
      })

      categories.value = data.categories ?? []
      meta.value = data.meta ?? {}

      if (!showResults.value)
        products.value = []
    }
    catch {
      categories.value = []
      meta.value = {}
      products.value = []
    }
    finally {
      loading.value = false
    }
  }

  const fetchResults = async () => {
    if (!showResults.value) {
      products.value = []

      return
    }

    loading.value = true

    try {
      const data = await fetchPosCatalog(buildParams())

      categories.value = data.categories ?? categories.value
      products.value = data.products ?? []
      meta.value = data.meta ?? {}
    }
    catch {
      products.value = []
    }
    finally {
      loading.value = false
    }
  }

  const fetchByIds = async ids => {
    const normalized = [...new Set((Array.isArray(ids) ? ids : []).map(Number).filter(Boolean))]

    if (!normalized.length)
      return []

    try {
      const data = await fetchPosCatalog({
        sellable_only: sellableOnly.value ? 1 : 0,
        unpriced_only: unpricedOnly.value ? 1 : 0,
        product_ids: normalized.join(','),
        limit: Math.max(normalized.length, limit.value),
      })

      return data.products ?? []
    }
    catch {
      return []
    }
  }

  const selectCategory = id => {
    viewMode.value = null
    productIds.value = []
    categoryId.value = id
  }

  const clearCategory = () => {
    categoryId.value = null
  }

  const showFavorites = ids => {
    viewMode.value = 'favorites'
    categoryId.value = null
    search.value = ''
    productIds.value = [...new Set((Array.isArray(ids) ? ids : []).map(Number).filter(Boolean))]
  }

  const showRecents = ids => {
    viewMode.value = 'recents'
    categoryId.value = null
    search.value = ''
    productIds.value = [...new Set((Array.isArray(ids) ? ids : []).map(Number).filter(Boolean))]
  }

  const resetBrowse = () => {
    search.value = ''
    categoryId.value = null
    productIds.value = []
    viewMode.value = null
    products.value = []
  }

  const debouncedFetch = useDebounceFn(fetchResults, 300)

  watch([search, categoryId, productIds, sellableOnly, unpricedOnly], () => {
    debouncedFetch()
  })

  return {
    sellableOnly,
    unpricedOnly,
    limit,
    categories,
    products,
    meta,
    loading,
    search,
    categoryId,
    productIds,
    viewMode,
    categoryMap,
    showResults,
    loadMeta,
    fetchResults,
    fetchByIds,
    selectCategory,
    clearCategory,
    showFavorites,
    showRecents,
    resetBrowse,
  }
}
