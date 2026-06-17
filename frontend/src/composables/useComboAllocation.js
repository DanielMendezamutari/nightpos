export function normalizeOperationalGirls(girls = []) {
  return girls.map(g => ({
    id: Number(g.id ?? g.value),
    name: g.name ?? g.title ?? `Chica #${g.id ?? g.value}`,
  })).filter(g => g.id > 0)
}

export function comboRequiredUnits(quantity, unitsPerCombo) {
  return Math.max(1, Number(quantity) || 1) * Math.max(1, Number(unitsPerCombo) || 1)
}

export function buildAllocationPayload(unitMap, requiredUnits) {
  const payload = Object.entries(unitMap)
    .map(([girlUserId, units]) => ({
      girl_user_id: Number(girlUserId),
      units: Number(units) || 0,
    }))
    .filter(r => r.girl_user_id > 0 && r.units > 0)

  const total = payload.reduce((sum, r) => sum + r.units, 0)

  if (total !== requiredUnits)
    return null

  return payload
}

export function initialUnitsMap(initialRows = [], girls = []) {
  const map = Object.fromEntries(normalizeOperationalGirls(girls).map(g => [String(g.id), 0]))

  for (const row of initialRows ?? []) {
    const id = String(row.girl_user_id)
    if (id in map)
      map[id] = Number(row.units) || 0
    else
      map[id] = Number(row.units) || 0
  }

  return map
}

export function unitsMapToRows(unitMap) {
  return Object.entries(unitMap)
    .map(([girlUserId, units]) => ({ girl_user_id: Number(girlUserId), units: Number(units) || 0 }))
    .filter(r => r.units > 0)
}

export function assignedUnitsFromMap(unitMap) {
  return Object.values(unitMap).reduce((sum, u) => sum + (Number(u) || 0), 0)
}
