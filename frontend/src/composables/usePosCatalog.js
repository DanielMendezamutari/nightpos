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

  const fetchAllSellableProducts = async () => {
    loading.value = true

    try {
      const metaData = await fetchPosCatalog({
        sellable_only: sellableOnly.value ? 1 : 0,
        unpriced_only: unpricedOnly.value ? 1 : 0,
        limit: Math.min(limit.value, 50),
      })

      categories.value = metaData.categories ?? []
      meta.value = metaData.meta ?? {}

      const categoryTargets = categories.value
        .filter(c => (c.sellable_count ?? 0) > 0 || (c.product_count ?? 0) > 0)
        .map(c => c.id)
        .filter(id => id != null)

      categoryTargets.push(0)

      const batchLimit = 50
      const batches = await Promise.all(
        categoryTargets.map(async catId => {
          try {
            const data = await fetchPosCatalog({
              sellable_only: sellableOnly.value ? 1 : 0,
              unpriced_only: unpricedOnly.value ? 1 : 0,
              category_id: catId,
              limit: batchLimit,
            })

            return data.products ?? []
          }
          catch {
            return []
          }
        }),
      )

      const merged = new Map()

      for (const batch of batches) {
        for (const product of batch)
          merged.set(product.id, product)
      }

      products.value = [...merged.values()].sort((a, b) =>
        String(a.name ?? '').localeCompare(String(b.name ?? ''), 'es'),
      )

      meta.value = {
        ...meta.value,
        result_count: products.value.length,
        matched_count: products.value.length,
        has_more: false,
      }
    }
    catch {
      products.value = []
    }
    finally {
      loading.value = false
    }
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
    fetchAllSellableProducts,
  }
}
