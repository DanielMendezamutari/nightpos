export function useCajaFormatters() {
  function formatMoney(n) {
    const v = Number(n) || 0
    return v.toLocaleString('es-AR', { maximumFractionDigits: 0 })
  }

  function formatWhen(iso) {
    if (!iso) return '—'
    try {
      return new Date(iso).toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' })
    } catch {
      return String(iso)
    }
  }

  function formatPct(v) {
    if (v === null || v === undefined || Number.isNaN(Number(v))) return '—'
    return `${Number(v)} %`
  }

  return { formatMoney, formatWhen, formatPct }
}
