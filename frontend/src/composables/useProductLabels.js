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
  if (product?.category_name)
    return product.category_name

  if (product?.category_id && categoryMap[product.category_id])
    return categoryMap[product.category_id]

  if (product?.category?.name)
    return product.category.name

  return formatProductType(product?.product_type)
}

export function normalizeActivePrices(raw) {
  if (!raw)
    return []

  if (Array.isArray(raw))
    return raw.map(normalizePriceRow).filter(Boolean)

  if (typeof raw === 'object')
    return Object.values(raw).map(normalizePriceRow).filter(Boolean)

  return []
}

function normalizePriceRow(row) {
  if (!row || typeof row !== 'object')
    return null

  const saleMode = String(row.sale_mode ?? row.saleMode ?? '').trim().toUpperCase()

  if (!saleMode)
    return null

  return {
    ...row,
    sale_mode: saleMode,
    status: String(row.status ?? 'active').trim().toLowerCase(),
    price: row.price ?? row.amount ?? null,
    currency: row.currency ?? 'BOB',
  }
}

export function isActivePriceRow(row) {
  if (!row)
    return false

  const status = String(row.status ?? 'active').trim().toLowerCase()

  if (status !== 'active')
    return false

  const price = row.price ?? row.amount

  return price !== null && price !== '' && !Number.isNaN(Number(price))
}

export function isComboCatalogProduct(product) {
  if (!product)
    return false

  if (product.requires_allocation === true)
    return true

  return String(product.settlement_behavior ?? '').toUpperCase() === 'GIRL_BRACELET_ALLOCATION'
}

export function isSellableCatalogProduct(product) {
  if (product?.has_active_pricing === true)
    return true

  return normalizeActivePrices(product?.active_prices).some(isActivePriceRow)
}

export function productActivePrice(product, saleMode) {
  const normalizedMode = String(saleMode ?? '').trim().toUpperCase()
  const prices = normalizeActivePrices(product?.active_prices)

  return prices.find(p => p.sale_mode === normalizedMode && isActivePriceRow(p)) ?? null
}

export function productHasActivePrice(product, saleMode) {
  return productActivePrice(product, saleMode) !== null
}
