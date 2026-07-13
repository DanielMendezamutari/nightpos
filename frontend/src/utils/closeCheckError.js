export function classifyCloseCheckError(status, message = '') {
  const normalized = String(message || '').toLowerCase()

  if (status === 403)
    return { type: 'permission_denied', title: 'Permiso denegado' }

  if (status === 422 && normalized.includes('contexto operativo incompleto'))
    return { type: 'invalid_context', title: 'Contexto operativo invalido' }

  if (status === 422 && (normalized.includes('suscrip') || normalized.includes('empresa no est')))
    return { type: 'tenant_inactive', title: 'Empresa inactiva o suscripcion vencida' }

  if (status >= 500)
    return { type: 'server_error', title: 'Error interno del servidor' }

  if (!status)
    return { type: 'network_error', title: 'Fallo de red o servicio no disponible' }

  return { type: 'unknown_error', title: 'Error al verificar cierre de turno' }
}
