import { markSettlementPaid, printSettlement } from '@/api/settlements'
import { paymentMethodLabel } from '@/constants/paymentMethods'
import { formatBob } from '@/constants/settlements'
import { useServiceCashSession } from '@/composables/useServiceCashSession'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { getApiErrorMessage } from '@/services/http'

/**
 * Pago de liquidaciones alineado con GET /cash/session/current.
 */
export function useSettlementPayment(options = {}) {
  const { onPaid } = options
  const { notify } = useNightPosNotify()
  const { openPrintRoute } = useNightPosPrint()
  const {
    cashSession,
    cashSessionOpen,
    loadingCash,
    loadCashSession,
    showOpenCash,
  } = useServiceCashSession()

  async function refreshCashSession() {
    await loadCashSession()
  }

  function openReceipt(settlementId) {
    openPrintRoute({ name: 'nightpos-print-settlement-id', params: { id: settlementId } })
  }

  async function reprintReceipt(settlementId) {
    try {
      const result = await printSettlement(settlementId, { reprint: true })

      notify(
        result?.print_warning ?? 'Comprobante reenviado a impresora.',
        result?.print_warning ? 'warning' : 'success',
      )

      return { ok: true, data: result }
    }
    catch (error) {
      notify('No se pudo reimprimir. Puede abrir la vista imprimible.', 'error')
      openReceipt(settlementId)

      return { ok: false, error }
    }
  }

  async function paySettlement(id, payload = {}) {
    const paymentMethod = payload.payment_method ?? payload.paymentMethod
    const notes = payload.notes ?? null
    const appliedFineIds = payload.applied_fine_ids ?? payload.appliedFineIds ?? []

    if (!paymentMethod) {
      notify('Debe seleccionar un método de pago.', 'warning')

      return { ok: false, reason: 'no_method' }
    }

    await loadCashSession()

    if (!cashSessionOpen.value) {
      notify('Debe abrir caja para pagar esta liquidación.', 'warning')
      showOpenCash.value = true

      return { ok: false, reason: 'no_cash' }
    }

    try {
      const data = await markSettlementPaid(id, {
        payment_method: paymentMethod,
        notes,
        applied_fine_ids: appliedFineIds,
      })
      const sessionId = data.cash_session_id ?? cashSession.value?.id
      const methodLabel = paymentMethodLabel(paymentMethod)
      const ticketNumber = data.ticket_number ?? data.settlement?.ticket_number

      notify(
        ticketNumber
          ? `Liquidación pagada — Ticket ${ticketNumber} (${methodLabel}).`
          : sessionId
            ? `Liquidación pagada (${methodLabel}) y egreso registrado en Caja #${sessionId}.`
            : `Liquidación pagada (${methodLabel}) y egreso registrado en caja.`,
        'success',
      )

      if (data.print_warning)
        notify(data.print_warning, 'warning')

      await loadCashSession()
      await onPaid?.(data)

      return { ok: true, data, openReceipt: () => openReceipt(id) }
    }
    catch (error) {
      const message = getApiErrorMessage(error)

      notify(message, 'error')

      if (message.includes('abrir caja'))
        showOpenCash.value = true

      return { ok: false, error, message }
    }
  }

  return {
    cashSession,
    cashSessionOpen,
    loadingCash,
    showOpenCash,
    refreshCashSession,
    paySettlement,
    openReceipt,
    reprintReceipt,
  }
}
