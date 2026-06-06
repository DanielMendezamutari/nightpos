import { fetchCurrentCashSession } from '@/api/cash'

import { fetchCurrentShift } from '@/api/shifts'

import { fetchOrders, fetchOrdersByScope } from '@/api/orders'

import { fetchSales } from '@/api/sales'

import { getApiErrorMessage } from '@/services/http'



/**

 * KPIs operativos desde APIs existentes. Sin inventar totales de «día» si no hay endpoint.

 */

export async function loadDashboardOperationalStats() {

  const result = {

    cashOpen: false,

    cashSessionLabel: 'Sin sesión',

    cashSessionId: null,

    openOrdersCount: null,

    activeOrdersCount: null,

    sentToBarOrdersCount: null,

    openOrdersError: null,

    sessionSalesTotal: null,

    salesByMethod: { cash: '0.00', qr: '0.00', card: '0.00' },

    sessionSalesCount: null,

    salesDayAvailable: false,

    shiftAvailable: false,

    shiftOpen: false,

    currentShiftLabel: 'Sin turno',

    shiftTypeLabel: null,

    shiftError: null,

    cashError: null,

    salesError: null,

  }



  try {

    const session = await fetchCurrentCashSession()



    if (session?.status === 'OPEN') {

      result.cashOpen = true

      result.cashSessionId = session.id

      result.cashSessionLabel = `Sesión #${session.id} abierta`

      result.salesByMethod = session.sales_by_method ?? result.salesByMethod



      const cash = Number(result.salesByMethod.cash ?? 0)

      const qr = Number(result.salesByMethod.qr ?? 0)

      const card = Number(result.salesByMethod.card ?? 0)



      result.sessionSalesTotal = (cash + qr + card).toFixed(2)



      try {

        const sales = await fetchSales(true)



        result.sessionSalesCount = sales.length

      }

      catch (error) {

        result.salesError = getApiErrorMessage(error)

      }

    }

    else {

      result.cashSessionLabel = 'Caja cerrada'

    }

  }

  catch (error) {

    result.cashError = getApiErrorMessage(error)

    result.cashSessionLabel = 'No disponible'

  }



  try {

    const shift = await fetchCurrentShift()



    if (shift?.status === 'OPEN') {

      result.shiftAvailable = true

      result.shiftOpen = true

      result.shiftTypeLabel = shift.shift_type_label

      result.currentShiftLabel = `${shift.name} · ${shift.business_date}`

    }

    else {

      result.shiftAvailable = true

      result.currentShiftLabel = 'Sin turno abierto'

    }

  }

  catch (error) {

    result.shiftError = getApiErrorMessage(error)

    result.currentShiftLabel = 'No disponible'

  }



  try {

    const [activeOrders, openOrders, barOrders] = await Promise.all([

      fetchOrdersByScope('operational_active'),

      fetchOrders('OPEN'),

      fetchOrdersByScope('sent_to_bar'),

    ])



    result.activeOrdersCount = activeOrders.length

    result.openOrdersCount = openOrders.length

    result.sentToBarOrdersCount = barOrders.length

  }

  catch (error) {

    result.openOrdersError = getApiErrorMessage(error)

    result.activeOrdersCount = null

    result.openOrdersCount = null

    result.sentToBarOrdersCount = null

  }



  return result

}

