// Ported from [Nuxt](https://github.com/nuxt/nuxt/blob/main/packages/nuxt/src/app/composables/cookie.ts)
import { parse, serialize } from 'cookie-es'
import { destr } from 'destr'

const CookieDefaults = {
  path: '/',
  watch: true,
  decode: val => destr(decodeURIComponent(val)),
  encode: val => encodeURIComponent(typeof val === 'string' ? val : JSON.stringify(val)),
}

/** Una ref por nombre — evita refs huérfanas al llamar useCookie varias veces. */
const cookieRefCache = new Map()

export const useCookie = (name, _opts) => {
  if (cookieRefCache.has(name))
    return cookieRefCache.get(name)

  const opts = { ...CookieDefaults, ..._opts || {} }
  const cookies = typeof document !== 'undefined' ? parse(document.cookie, opts) : {}
  const cookie = ref(cookies[name] ?? opts.default?.())

  watch(cookie, () => {
    if (typeof document === 'undefined')
      return

    document.cookie = serializeCookie(name, cookie.value, opts)
  })

  cookieRefCache.set(name, cookie)

  return cookie
}
function serializeCookie(name, value, opts = {}) {
  if (value === null || value === undefined)
    return serialize(name, value, { ...opts, maxAge: -1 })
  
  return serialize(name, value, opts)
}
