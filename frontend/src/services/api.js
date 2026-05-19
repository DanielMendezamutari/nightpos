/**
 * Cliente HTTP hacia la API Laravel (autenticación JWT: header Authorization Bearer).
 */
const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://nightpos.test/api'

/** Navegación SPA al login por 401 (registrado desde main.js para evitar recarga completa). */
let unauthorizedNavigator = null

export function setUnauthorizedNavigator(fn) {
  unauthorizedNavigator = typeof fn === 'function' ? fn : null
}

export async function apiRequest(path, options = {}, token = '') {
  try {
    const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData
    const response = await fetch(`${API_BASE}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(!isFormData && options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(options.headers || {}),
      },
    })

    let payload = {}
    try {
      payload = await response.json()
    } catch {
      payload = {}
    }

    if (response.status === 401 && token && path !== '/auth/login') {
      const { useAuthStore } = await import('../stores/authStore.js')
      useAuthStore().clearSessionLocal()
      if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
        if (unauthorizedNavigator) {
          unauthorizedNavigator()
        } else {
          window.location.assign('/login')
        }
      }
    }

    if (!response.ok) {
      let msg = payload.message || ''
      if (payload.errors && typeof payload.errors === 'object') {
        const firstKey = Object.keys(payload.errors)[0]
        const arr = firstKey ? payload.errors[firstKey] : null
        const first = Array.isArray(arr) ? arr[0] : arr
        if (first && typeof first === 'string') {
          msg = first
        }
      }
      if (!msg) {
        msg = `No se pudo completar la solicitud (${response.status}).`
      }
      throw new Error(msg)
    }

    return payload
  } catch (error) {
    if (error instanceof TypeError) {
      throw new Error('No se pudo conectar al backend. Verifica que Laravel este corriendo en http://nightpos.test/.')
    }
    throw error
  }
}

/** POST multipart (logo, etc.); no JSON Content-Type. */
export async function apiFormPost(path, formData, token) {
  return apiRequest(path, { method: 'POST', body: formData }, token)
}

/**
 * GET binario (PDF, etc.); dispara descarga en el navegador.
 * @param {string} path ruta bajo API (ej. /maintenance/purchases/1/document?site_id=2)
 * @param {string} [fallbackName] si el servidor no manda Content-Disposition
 */
export async function apiDownloadFile(path, token, fallbackName = 'comprobante') {
  const response = await fetch(`${API_BASE}${path}`, {
    method: 'GET',
    headers: {
      Accept: '*/*',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  })

  if (response.status === 401 && token && path !== '/auth/login') {
    const { useAuthStore } = await import('../stores/authStore.js')
    useAuthStore().clearSessionLocal()
    if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
      if (unauthorizedNavigator) {
        unauthorizedNavigator()
      } else {
        window.location.assign('/login')
      }
    }
  }

  if (!response.ok) {
    let msg = ''
    try {
      const j = await response.json()
      msg = j.message || ''
    } catch {
      /* vacío */
    }
    throw new Error(msg || `No se pudo descargar (${response.status}).`)
  }

  let name = fallbackName
  const cd = response.headers.get('Content-Disposition')
  if (cd) {
    const utf = /filename\*=UTF-8''([^;\n]+)/i.exec(cd)
    const plain = /filename="([^"]+)"/i.exec(cd)
    if (utf) {
      try {
        name = decodeURIComponent(utf[1].trim())
      } catch {
        name = utf[1].trim()
      }
    } else if (plain) {
      name = plain[1]
    }
  }

  const blob = await response.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = name || fallbackName
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

/**
 * GET binario; devuelve Blob (p. ej. vista previa en iframe dentro de la SPA).
 */
export async function apiFetchBlob(path, token) {
  const response = await fetch(`${API_BASE}${path}`, {
    method: 'GET',
    headers: {
      Accept: '*/*',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  })

  if (response.status === 401 && token && path !== '/auth/login') {
    const { useAuthStore } = await import('../stores/authStore.js')
    useAuthStore().clearSessionLocal()
    if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
      if (unauthorizedNavigator) {
        unauthorizedNavigator()
      } else {
        window.location.assign('/login')
      }
    }
  }

  if (!response.ok) {
    let msg = ''
    try {
      const j = await response.json()
      msg = j.message || ''
    } catch {
      /* vacío */
    }
    throw new Error(msg || `No se pudo obtener el archivo (${response.status}).`)
  }

  return response.blob()
}

export { API_BASE }
