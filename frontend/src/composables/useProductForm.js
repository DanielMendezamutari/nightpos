import { activePriceByMode } from '@/composables/useProductSaleModeLabels'

export function createDefaultProductForm() {
  return {
    name: '',
    category_id: null,
    sku: '',
    status: 'active',
    product_type: 'beverage',
    unit: 'unit',
    solo_price: null,
    companion_price: null,
    girl_amount: null,
    house_amount: null,
    is_combo: false,
    bracelet_units_per_line: 6,
  }
}

export function productToForm(product, activePrices = []) {
  const solo = activePriceByMode(activePrices, 'SOLO_CLIENTE')
  const companion = activePriceByMode(activePrices, 'CON_ACOMPANANTE')
  const isCombo = product?.settlement_behavior === 'GIRL_BRACELET_ALLOCATION'
    || product?.requires_allocation === true

  return {
    name: product?.name ?? '',
    category_id: product?.category_id ?? null,
    sku: product?.sku ?? '',
    status: product?.status ?? 'active',
    product_type: product?.product_type ?? 'beverage',
    unit: product?.unit ?? 'unit',
    solo_price: solo?.price != null ? Number(solo.price) : null,
    companion_price: companion?.price != null ? Number(companion.price) : null,
    girl_amount: companion?.girl_amount != null ? Number(companion.girl_amount) : null,
    house_amount: companion?.house_amount != null ? Number(companion.house_amount) : null,
    is_combo: isCombo,
    bracelet_units_per_line: Number(product?.bracelet_units_per_line ?? 6) || 6,
  }
}

export function productPreviewLabel(form) {
  const name = form.name?.trim() || 'Sin nombre'
  if (form.is_combo)
    return `${name} — Combo ${form.bracelet_units_per_line || 6} manillas`

  if (Number(form.companion_price) > 0)
    return `${name} — Con acompañante`

  return `${name} — Producto normal`
}

export function formToQuickCreatePayload(form) {
  const payload = {
    name: form.name.trim(),
    category_id: form.category_id,
    solo_price: Number(form.solo_price),
    product_type: form.product_type,
    unit: form.unit?.trim() || 'unit',
    status: form.status,
  }

  if (form.sku?.trim())
    payload.sku = form.sku.trim()

  if (form.is_combo) {
    payload.settlement_behavior = 'GIRL_BRACELET_ALLOCATION'
    payload.bracelet_units_per_line = Number(form.bracelet_units_per_line) || 6
    payload.unit = form.unit?.trim() || 'combo'
  }

  const companion = Number(form.companion_price)
  if (companion > 0) {
    payload.companion_price = companion
    payload.girl_amount = Number(form.girl_amount ?? 0)
    payload.house_amount = Number(form.house_amount ?? 0)
  }

  return payload
}

export function formToUpdatePayload(form) {
  const payload = {
    name: form.name.trim(),
    category_id: form.category_id || null,
    sku: form.sku?.trim() || null,
    product_type: form.product_type,
    unit: form.is_combo ? (form.unit?.trim() || 'combo') : (form.unit?.trim() || 'unit'),
    status: form.status,
    settlement_behavior: form.is_combo ? 'GIRL_BRACELET_ALLOCATION' : 'GIRL_LINE',
    bracelet_units_per_line: form.is_combo ? Number(form.bracelet_units_per_line) || 6 : 1,
  }

  return payload
}

export function formPricePayloads(form) {
  const payloads = []

  if (Number(form.solo_price) > 0) {
    payloads.push({
      sale_mode: 'SOLO_CLIENTE',
      price: Number(form.solo_price),
    })
  }

  const companion = Number(form.companion_price)
  if (companion > 0) {
    payloads.push({
      sale_mode: 'CON_ACOMPANANTE',
      price: companion,
      girl_amount: Number(form.girl_amount ?? 0),
      house_amount: Number(form.house_amount ?? 0),
    })
  }

  return payloads
}
