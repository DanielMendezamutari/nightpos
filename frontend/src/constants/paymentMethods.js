export const PAYMENT_METHOD_OPTIONS = [
  { title: 'Efectivo', value: 'CASH' },
  { title: 'QR', value: 'QR' },
  { title: 'Tarjeta', value: 'CARD' },
]

export function paymentMethodLabel(method) {
  const found = PAYMENT_METHOD_OPTIONS.find(o => o.value === method)

  return found?.title ?? method ?? '—'
}
