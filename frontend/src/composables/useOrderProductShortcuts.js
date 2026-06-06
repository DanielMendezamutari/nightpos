const STORAGE_FAVORITES = 'nightpos_order_favorite_products'
const STORAGE_RECENTS = 'nightpos_order_recent_products'
const MAX_RECENTS = 8

function repairStorage(key) {
  try {
    const raw = localStorage.getItem(key)
    if (raw === null)
      return

    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed))
      localStorage.removeItem(key)
  }
  catch {
    localStorage.removeItem(key)
  }
}

function readIds(key) {
  repairStorage(key)

  try {
    const raw = localStorage.getItem(key)
    const parsed = JSON.parse(raw ?? '[]')

    return Array.isArray(parsed) ? parsed : []
  }
  catch {
    localStorage.removeItem(key)

    return []
  }
}

function safeIdList(refValue) {
  return Array.isArray(refValue) ? refValue : []
}

function writeIds(key, ids) {
  localStorage.setItem(key, JSON.stringify(ids))
}

export function useOrderProductShortcuts() {
  const favorites = ref(readIds(STORAGE_FAVORITES))
  const recents = ref(readIds(STORAGE_RECENTS))

  const toggleFavorite = productId => {
    const id = Number(productId)
    const set = new Set((Array.isArray(favorites.value) ? favorites.value : []).map(Number))

    if (set.has(id))
      set.delete(id)
    else
      set.add(id)

    favorites.value = [...set]
    writeIds(STORAGE_FAVORITES, favorites.value)
  }

  const isFavorite = productId => (Array.isArray(favorites.value) ? favorites.value : []).map(Number).includes(Number(productId))

  const recordRecent = productId => {
    const id = Number(productId)
    const recentIds = Array.isArray(recents.value) ? recents.value : []
    const next = [id, ...recentIds.map(Number).filter(x => x !== id)].slice(0, MAX_RECENTS)

    recents.value = next
    writeIds(STORAGE_RECENTS, next)
  }

  const sortProductsForPicker = products => {
    const list = Array.isArray(products) ? products : []
    const favIds = Array.isArray(favorites.value) ? favorites.value : []
    const recentIds = Array.isArray(recents.value) ? recents.value : []
    const favSet = new Set(favIds.map(Number))
    const recentOrder = recentIds.map(Number)

    const byId = id => list.find(p => Number(p.id) === Number(id))

    const picked = new Set()
    const ordered = []

    for (const id of recentOrder) {
      const p = byId(id)
      if (p && !picked.has(p.id)) {
        ordered.push(p)
        picked.add(p.id)
      }
    }

    const favs = list.filter(p => favSet.has(Number(p.id)) && !picked.has(p.id))
    for (const p of favs) {
      ordered.push(p)
      picked.add(p.id)
    }

    for (const p of list) {
      if (!picked.has(p.id))
        ordered.push(p)
    }

    return ordered
  }

  const favoriteProducts = products => {
    const list = Array.isArray(products) ? products : []
    const favSet = new Set(safeIdList(favorites.value).map(Number))

    return list.filter(p => favSet.has(Number(p.id)))
  }

  const recentProducts = products => {
    const list = Array.isArray(products) ? products : []
    const recentOrder = safeIdList(recents.value).map(Number)

    return recentOrder
      .map(id => list.find(p => Number(p.id) === id))
      .filter(Boolean)
  }

  return {
    favorites,
    recents,
    toggleFavorite,
    isFavorite,
    recordRecent,
    sortProductsForPicker,
    favoriteProducts,
    recentProducts,
  }
}
