import { fetchStaffGirls } from '@/api/staff'
import { fetchWaiterGirls } from '@/api/waiter'
import { fetchAdminUsers } from '@/api/users'

function mapGirlsToSelect(items) {
  return (items ?? []).map(u => ({ title: u.name, value: u.id }))
}

/** @param {{ waiterMode?: boolean }} options */
export async function loadOperationalGirlsForSelect(options = {}) {
  if (options.waiterMode) {
    try {
      return mapGirlsToSelect(await fetchWaiterGirls())
    }
    catch {
      return []
    }
  }

  try {
    return mapGirlsToSelect(await fetchStaffGirls())
  }
  catch {
    const users = await fetchAdminUsers().catch(() => [])

    return users
      .filter(u => u.staff_role === 'GIRL' && u.status === 'active')
      .map(u => ({ title: u.name, value: u.id }))
  }
}

export function appendGirlToSelectList(list, girl) {
  if (!girl?.id)
    return list

  const entry = { title: girl.name, value: girl.id }
  if (list.some(item => item.value === entry.value))
    return list

  return [...list, entry].sort((a, b) => a.title.localeCompare(b.title))
}
