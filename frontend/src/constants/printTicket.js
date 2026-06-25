/** Pie de ticket térmico / navegador — configurable vía .env */
export const PRINT_TICKET_FOOTER = import.meta.env.VITE_PRINT_TICKET_FOOTER
  || 'Powered by Ribersoft · WhatsApp 67369293'

export const CASH_CLOSE_BANNER = {
  NORMAL: 'CIERRE NORMAL',
  WITH_NOTES: 'CIERRE CON OBSERVACIONES',
  ADMIN: 'CIERRE ADMINISTRATIVO',
}

export function buildShiftLabel(shift) {
  if (!shift)
    return ''

  if (shift.name)
    return shift.name

  const type = shift.shift_type_label
    || (shift.shift_type === 'DAY' ? 'Día' : shift.shift_type === 'NIGHT' ? 'Noche' : shift.shift_type)

  return [type, shift.business_date].filter(Boolean).join(' · ')
}
