/**
 * Arma el body de POST /orders para garzón (solo ambiente o solo texto libre).
 * @returns {object|null} null si falta mesa/ambiente
 */
export function buildWaiterCreateOrderPayload(form) {
  const notes = form.notes?.trim()
  const label = form.table_label?.trim()
  const areaId = form.service_area_id ? Number(form.service_area_id) : null

  if (areaId > 0) {
    const payload = { service_area_id: areaId }

    if (notes)
      payload.notes = notes

    return payload
  }

  if (label) {
    const payload = { table_label: label }

    if (notes)
      payload.notes = notes

    return payload
  }

  return null
}

export function hasWaiterTableReference(form) {
  const payload = buildWaiterCreateOrderPayload(form)

  return payload !== null
}
