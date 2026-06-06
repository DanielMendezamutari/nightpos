import { computed, ref, watch } from 'vue'

/**
 * Pagos mixtos (efectivo / QR / tarjeta) para cobro de comanda y venta directa.
 *
 * @param {import('vue').Ref<number>|import('vue').ComputedRef<number>} totalRef Total a cobrar
 * @param {{ variant?: 'selector' | 'inline' }} options
 */
export function useMixedPayments(totalRef, options = {}) {
  const variant = options.variant ?? 'selector'

  const form = ref({
    method: 'CASH',
    cashAmount: null,
    qrAmount: null,
    cardAmount: null,
    receivedAmount: null,
  })

  const total = computed(() => Number(totalRef.value ?? 0))

  const payments = computed(() => {
    const t = total.value

    if (variant === 'inline') {
      const rows = []

      if (Number(form.value.cashAmount) > 0)
        rows.push({ method: 'CASH', amount: Number(form.value.cashAmount) })
      if (Number(form.value.qrAmount) > 0)
        rows.push({ method: 'QR', amount: Number(form.value.qrAmount) })
      if (Number(form.value.cardAmount) > 0)
        rows.push({ method: 'CARD', amount: Number(form.value.cardAmount) })

      return rows
    }

    const method = form.value.method

    if (method === 'MIXED') {
      const rows = []

      if (Number(form.value.cashAmount) > 0)
        rows.push({ method: 'CASH', amount: Number(form.value.cashAmount) })
      if (Number(form.value.qrAmount) > 0)
        rows.push({ method: 'QR', amount: Number(form.value.qrAmount) })
      if (Number(form.value.cardAmount) > 0)
        rows.push({ method: 'CARD', amount: Number(form.value.cardAmount) })

      return rows
    }

    if (t > 0)
      return [{ method, amount: t }]

    return []
  })

  const paymentsSum = computed(() =>
    payments.value.reduce((sum, row) => sum + row.amount, 0),
  )

  const cashPortion = computed(() => {
    if (variant === 'inline')
      return Number(form.value.cashAmount) || 0

    if (form.value.method === 'CASH')
      return total.value
    if (form.value.method === 'MIXED')
      return Number(form.value.cashAmount) || 0

    return 0
  })

  const changeAmount = computed(() => {
    const received = Number(form.value.receivedAmount)

    if (!received || cashPortion.value <= 0)
      return 0

    return Math.max(0, received - cashPortion.value)
  })

  const remaining = computed(() =>
    Math.max(0, total.value - paymentsSum.value),
  )

  const overpaid = computed(() =>
    paymentsSum.value - total.value > 0.01,
  )

  const isBalanced = computed(() =>
    total.value > 0 && Math.abs(paymentsSum.value - total.value) <= 0.01,
  )

  const canSubmit = computed(() => {
    if (total.value <= 0 || payments.value.length === 0)
      return false

    if (!isBalanced.value)
      return false

    const received = Number(form.value.receivedAmount)

    if (cashPortion.value > 0 && received > 0 && received < cashPortion.value)
      return false

    return true
  })

  watch(
    () => form.value.method,
    method => {
      if (variant !== 'selector')
        return

      if (method !== 'MIXED') {
        form.value.cashAmount = null
        form.value.qrAmount = null
        form.value.cardAmount = null
      }
      form.value.receivedAmount = null
    },
  )

  function reset() {
    form.value = {
      method: 'CASH',
      cashAmount: null,
      qrAmount: null,
      cardAmount: null,
      receivedAmount: null,
    }
  }

  function setAllCash() {
    form.value.cashAmount = total.value || null
    form.value.qrAmount = null
    form.value.cardAmount = null
    form.value.receivedAmount = null
  }

  function setAllQr() {
    form.value.cashAmount = null
    form.value.qrAmount = total.value || null
    form.value.cardAmount = null
    form.value.receivedAmount = null
  }

  function setAllCard() {
    form.value.cashAmount = null
    form.value.qrAmount = null
    form.value.cardAmount = total.value || null
    form.value.receivedAmount = null
  }

  function clearAmounts() {
    form.value.cashAmount = null
    form.value.qrAmount = null
    form.value.cardAmount = null
    form.value.receivedAmount = null
  }

  /**
   * @returns {{ valid: boolean, message?: string }}
   */
  function validate() {
    if (total.value <= 0)
      return { valid: false, message: 'El total debe ser mayor a cero.' }

    if (payments.value.length === 0)
      return { valid: false, message: 'Indique al menos un monto de pago.' }

    if (paymentsSum.value < total.value - 0.01)
      return { valid: false, message: `Faltan ${remaining.value.toFixed(2)} BOB para completar el pago.` }

    if (overpaid.value)
      return { valid: false, message: 'La suma de pagos supera el total. Ajuste los montos.' }

    const received = Number(form.value.receivedAmount)

    if (cashPortion.value > 0 && received > 0 && received < cashPortion.value)
      return { valid: false, message: 'El efectivo recibido debe cubrir la parte en efectivo.' }

    return { valid: true }
  }

  function toPayload() {
    return {
      payments: payments.value,
      paymentsSum: paymentsSum.value,
      total: total.value,
      cashPortion: cashPortion.value,
      receivedAmount: Number(form.value.receivedAmount) || null,
      changeAmount: changeAmount.value,
    }
  }

  return {
    form,
    total,
    payments,
    paymentsSum,
    cashPortion,
    changeAmount,
    remaining,
    overpaid,
    isBalanced,
    canSubmit,
    reset,
    setAllCash,
    setAllQr,
    setAllCard,
    clearAmounts,
    validate,
    toPayload,
  }
}
