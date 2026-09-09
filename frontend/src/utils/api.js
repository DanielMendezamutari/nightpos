import { ofetch } from 'ofetch'

// By default connect to nightpos.test or whatever VITE_API_BASE_URL is configured to
const apiBase = import.meta.env.VITE_API_BASE_URL || 'http://nightpos.test'

export const $api = ofetch.create({
  baseURL: apiBase,
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      options.headers = {
        ...options.headers,
        Authorization: `Bearer ${accessToken}`,
      }
    }
  },
})