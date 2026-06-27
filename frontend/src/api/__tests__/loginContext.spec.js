import { beforeEach, describe, expect, it, vi } from 'vitest'

const apiGet = vi.fn()

vi.mock('@/services/http', () => ({
  default: {
    get: (...args) => apiGet(...args),
  },
  unwrapNightPosResponse: response => response.data?.data ?? response.data,
}))

import { fetchLoginContextTenants } from '@/api/loginContext'

describe('fetchLoginContextTenants', () => {
  beforeEach(() => {
    apiGet.mockReset()
  })

  it('reintentar vuelve a cargar tenants tras fallo', async () => {
    apiGet
      .mockRejectedValueOnce({ code: 'ECONNABORTED', message: 'timeout' })
      .mockResolvedValueOnce({
        data: { data: { tenants: [{ id: 1, name: 'Demo', slug: 'casa-demo' }] } },
      })

    await expect(fetchLoginContextTenants()).rejects.toBeTruthy()

    const tenants = await fetchLoginContextTenants()

    expect(tenants).toEqual([{ id: 1, name: 'Demo', slug: 'casa-demo' }])
    expect(apiGet).toHaveBeenCalledTimes(2)
    expect(apiGet).toHaveBeenCalledWith('/auth/login-context/tenants')
  })
})
