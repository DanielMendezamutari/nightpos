export const SALE_MODE_LABELS = {
  SOLO_CLIENTE: 'Precio cliente',
  CON_ACOMPANANTE: 'Con acompañante',
}

export function saleModeLabel(mode) {
  return SALE_MODE_LABELS[mode] ?? mode
}

export function activePriceByMode(activePrices, mode) {
  if (!Array.isArray(activePrices))
    return null

  return activePrices.find(p => p.status === 'active' && p.sale_mode === mode) ?? null
}

export function productHasActivePricing(product) {
  if (product?.has_active_pricing != null)
    return product.has_active_pricing

  const prices = product?.active_prices

  return Array.isArray(prices) && prices.some(p => p.status === 'active')
}
