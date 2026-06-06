const PRODUCT_TYPE_LABELS = {
  beverage: 'Bebidas',
  food: 'Comida',
  service: 'Servicios',
  general: 'General',
  other: 'Otros',
}

const SALE_MODE_SHORT = {
  SOLO_CLIENTE: 'Solo',
  CON_ACOMPANANTE: 'Con acompañante',
}

const SALE_MODE_LONG = {
  SOLO_CLIENTE: 'Solo cliente',
  CON_ACOMPANANTE: 'Con acompañante',
}

const STATUS_LABELS = {
  active: 'Activo',
  inactive: 'Inactivo',
  OPEN: 'Abierta',
  CLOSED: 'Cerrada',
}

export function formatProductType(type) {
  if (!type)
    return 'Otros'

  const key = String(type).toLowerCase()

  return PRODUCT_TYPE_LABELS[key] || 'Otros'
}

export function formatSaleMode(mode, short = false) {
  if (!mode)
    return '—'

  const map = short ? SALE_MODE_SHORT : SALE_MODE_LONG

  return map[mode] || mode
}

export function formatStatus(status) {
  if (!status)
    return '—'

  const key = String(status).toLowerCase()

  return STATUS_LABELS[key] || STATUS_LABELS[status] || status
}

export function productCategoryLabel(product, categoryMap = {}) {
  if (product?.category_id && categoryMap[product.category_id])
    return categoryMap[product.category_id]

  if (product?.category?.name)
    return product.category.name

  return formatProductType(product?.product_type)
}

export function productActivePrice(product, saleMode) {
  const prices = product?.active_prices ?? []

  return prices.find(p => p.sale_mode === saleMode && p.status === 'active') ?? null
}
