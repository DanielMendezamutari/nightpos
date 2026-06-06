import { fetchAdminCashSessions, fetchAdminCashSessionsSummary } from '@/api/adminCashSessions'
import { fetchShifts } from '@/api/shifts'
import { fetchAdminUsers } from '@/api/users'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

export function useAdminCashSessionsList(defaultStatus = null) {
  const { notify } = useNightPosNotify()

  const loading = ref(true)
  const summaryLoading = ref(false)
  const sessions = ref([])
  const summary = ref(null)
  const shifts = ref([])
  const cashiers = ref([])

  const filters = ref({
    date_from: '',
    date_to: '',
    official_shift_id: null,
    cashier_user_id: null,
    status: defaultStatus,
  })

  const buildParams = () => ({
    date_from: filters.value.date_from || undefined,
    date_to: filters.value.date_to || undefined,
    official_shift_id: filters.value.official_shift_id || undefined,
    cashier_user_id: filters.value.cashier_user_id || undefined,
    status: filters.value.status || undefined,
  })

  const loadSessions = async () => {
    loading.value = true

    try {
      const data = await fetchAdminCashSessions(buildParams())

      sessions.value = data.cash_sessions ?? []
    }
    catch (error) {
      if (import.meta.env.DEV) {
        console.error('[admin/cash-sessions]', error?.response?.status, error?.response?.data?.message ?? error)
      }
      notify(getApiErrorMessage(error), 'error')
      sessions.value = []
    }
    finally {
      loading.value = false
    }
  }

  const loadSummary = async () => {
    summaryLoading.value = true

    try {
      const data = await fetchAdminCashSessionsSummary(buildParams())

      summary.value = data.summary ?? null
    }
    catch (error) {
      if (import.meta.env.DEV) {
        console.error('[admin/cash-sessions/summary]', error?.response?.status, error?.response?.data?.message ?? error)
      }
      notify(getApiErrorMessage(error), 'error')
      summary.value = null
    }
    finally {
      summaryLoading.value = false
    }
  }

  const loadFiltersMeta = async () => {
    try {
      const [shiftData, userData] = await Promise.all([
        fetchShifts(),
        fetchAdminUsers(),
      ])

      shifts.value = shiftData.shifts ?? []
      cashiers.value = userData.filter(u => ['CASHIER', 'cashier'].includes(u.staff_role) || u.role?.slug === 'cashier' || u.role?.slug === 'cashier_senior')
    }
    catch {
      shifts.value = []
      cashiers.value = []
    }
  }

  const reload = async () => {
    await Promise.all([loadSessions(), loadSummary(), loadFiltersMeta()])
  }

  useOnContextChange(reload)

  onMounted(reload)

  return {
    loading,
    summaryLoading,
    sessions,
    summary,
    shifts,
    cashiers,
    filters,
    loadSessions,
    loadSummary,
    reload,
  }
}
