import api from '@/services/http'

export async function fetchFirstNightChecklist() {
  const response = await api.get('/settings/first-night-checklist')
  return response.data?.checklist ?? { complete: false, items: [] }
}
