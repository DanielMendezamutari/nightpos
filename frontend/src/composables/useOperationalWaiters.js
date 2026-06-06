import { fetchStaffWaiters } from '@/api/staff'
import { fetchAdminUsers } from '@/api/users'

export async function loadOperationalWaitersForSelect() {
  try {
    const items = await fetchStaffWaiters()

    return items.map(u => ({ title: u.name, value: u.id }))
  }
  catch {
    const users = await fetchAdminUsers().catch(() => [])

    return users
      .filter(u => u.staff_role === 'WAITER' && u.status === 'active')
      .map(u => ({ title: u.name, value: u.id }))
  }
}

export function appendWaiterToSelectList(list, waiter) {
  if (!waiter?.id)
    return list

  const entry = { title: waiter.name, value: waiter.id }
  if (list.some(item => item.value === entry.value))
    return list

  return [...list, entry].sort((a, b) => a.title.localeCompare(b.title))
}
