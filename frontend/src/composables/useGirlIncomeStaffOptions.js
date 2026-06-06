import { loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { loadOperationalWaitersForSelect } from '@/composables/useOperationalWaiters'

export async function loadGirlIncomeStaffOptions() {
  const [girls, waiters] = await Promise.all([
    loadOperationalGirlsForSelect(),
    loadOperationalWaitersForSelect(),
  ])

  return { girls, waiters }
}
