import { markSettlementPaid } from '@/api/settlements'

import { paymentMethodLabel } from '@/constants/paymentMethods'

import { useServiceCashSession } from '@/composables/useServiceCashSession'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { getApiErrorMessage } from '@/services/http'



/**

 * Pago de liquidaciones alineado con GET /cash/session/current.

 */

export function useSettlementPayment(options = {}) {

  const { onPaid } = options

  const { notify } = useNightPosNotify()

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



  async function paySettlement(id, payload = {}) {

    const paymentMethod = payload.payment_method ?? payload.paymentMethod

    const notes = payload.notes ?? null



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

      })

      const sessionId = data.cash_session_id ?? cashSession.value?.id

      const methodLabel = paymentMethodLabel(paymentMethod)



      notify(

        sessionId

          ? `Liquidación pagada (${methodLabel}) y egreso registrado en Caja #${sessionId}.`

          : `Liquidación pagada (${methodLabel}) y egreso registrado en caja.`,

        'success',

      )



      await loadCashSession()

      await onPaid?.(data)



      return { ok: true, data }

    }

    catch (error) {

      const message = getApiErrorMessage(error)



      notify(message, 'error')



      if (message.includes('abrir caja')) {

        showOpenCash.value = true

      }



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

  }

}


