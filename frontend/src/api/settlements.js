import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentShiftSettlements() {
  const response = await api.get('/settlements/current-shift')

  return unwrapNightPosResponse(response)
}

export async function fetchSettlementPendingSources() {
  const response = await api.get('/settlements/current-shift/pending-sources')

  return unwrapNightPosResponse(response)
}

export async function generateCurrentShiftSettlements() {
  const response = await api.post('/settlements/generate-current-shift')

  return unwrapNightPosResponse(response)
}

export async function fetchSettlement(id) {
  const response = await api.get(`/settlements/${id}`)

  return unwrapNightPosResponse(response)
}

export async function fetchSettlementPayPreview(id, appliedFineIds = []) {
  const response = await api.get(`/settlements/${id}/pay-preview`, {
    params: {
      applied_fine_ids: appliedFineIds.length ? appliedFineIds : undefined,
    },
  })

  return unwrapNightPosResponse(response)
}

export async function markSettlementPaid(id, payload = {}) {
  const response = await api.post(`/settlements/${id}/mark-paid`, {
    payment_method: payload.payment_method,
    notes: payload.notes ?? null,
    applied_fine_ids: payload.applied_fine_ids ?? [],
  })

  return unwrapNightPosResponse(response)
}

export async function applySettlementManualDiscount(id, payload = {}) {
  const response = await api.post(`/settlements/${id}/manual-discount`, {
    discount_mode: payload.discount_mode,
    discount_value: payload.discount_value,
    reason: payload.reason,
    notes: payload.notes ?? null,
  })

  return unwrapNightPosResponse(response)
}

export async function previewSettlementManualDiscount(id, payload = {}) {
  const response = await api.post(`/settlements/${id}/manual-discount/preview`, {
    discount_mode: payload.discount_mode,
    discount_value: payload.discount_value,
  })

  return unwrapNightPosResponse(response)
}

export async function cancelSettlementManualDiscount(id) {
  const response = await api.delete(`/settlements/${id}/manual-discount`)

  return unwrapNightPosResponse(response)
}

export async function printSettlement(id, { reprint = false } = {}) {
  const response = await api.post(`/settlements/${id}/print`, reprint ? { reprint: true } : {})

  return unwrapNightPosResponse(response)
}

export async function fetchSettlementHistory(params = {}) {
  const response = await api.get('/settlements/history', {
    params: {
      limit: params.limit ?? 50,
      official_shift_id: params.official_shift_id || undefined,
      staff_user_id: params.staff_user_id || undefined,
      settlement_type: params.settlement_type || undefined,
      status: params.status || undefined,
      date_from: params.date_from || undefined,
      date_to: params.date_to || undefined,
    },
  })

  return unwrapNightPosResponse(response)
}
