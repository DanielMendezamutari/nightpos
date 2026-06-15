import { fetchAdminBranches, fetchAvailableBranches } from '@/api/branches'
import { useAuthStore } from '@/stores/auth'

export const STAFF_ROLES = [
  { title: 'Cajera', value: 'CASHIER' },
  { title: 'Garzón', value: 'WAITER' },
  { title: 'Chica', value: 'GIRL' },
  { title: 'Limpieza', value: 'CLEANING' },
  { title: 'Administrador', value: 'MANAGER' },
]

export const STAFF_LABELS = {
  CASHIER: 'Cajera',
  WAITER: 'Garzón',
  GIRL: 'Chica',
  CLEANING: 'Limpieza',
  MANAGER: 'Administrador',
  INVENTORY: 'Inventario',
  REPORTS: 'Reportes',
}

export const STAFF_CHIP_COLOR = {
  CASHIER: 'primary',
  WAITER: 'info',
  GIRL: 'warning',
  CLEANING: 'success',
  MANAGER: 'secondary',
}

export function emptyUserForm() {
  return {
    name: '',
    username: '',
    email: '',
    pin: '',
    password: '',
    branch_id: null,
    status: 'active',
    staff_role: 'CASHIER',
    waiter_commission_percent: null,
    can_receive_girl_commissions: true,
    cleaning_base_amount: 30,
    cleaning_room_amount: 10,
    accessible_branch_ids: [],
  }
}

export function userToForm(user) {
  return {
    name: user.name,
    username: user.username,
    email: user.email || '',
    pin: '',
    password: '',
    branch_id: user.branch_id,
    status: user.status,
    staff_role: user.staff_role || 'CASHIER',
    waiter_commission_percent: user.waiter_commission_percent != null
      ? Number(user.waiter_commission_percent)
      : null,
    can_receive_girl_commissions: Boolean(user.can_receive_girl_commissions),
    cleaning_base_amount: user.cleaning_base_amount != null
      ? Number(user.cleaning_base_amount)
      : 30,
    cleaning_room_amount: user.cleaning_room_amount != null
      ? Number(user.cleaning_room_amount)
      : 10,
    accessible_branch_ids: [...(user.accessible_branch_ids || [])],
  }
}

export function buildUserPayload(form, { isCreate = false } = {}) {
  const payload = {
    name: form.name?.trim(),
    username: form.username?.trim(),
    email: form.email?.trim() || null,
    branch_id: form.branch_id,
    status: form.status,
    staff_role: form.staff_role,
    accessible_branch_ids: form.accessible_branch_ids,
  }

  if (form.staff_role === 'WAITER')
    payload.waiter_commission_percent = form.waiter_commission_percent

  if (form.staff_role === 'GIRL')
    payload.can_receive_girl_commissions = form.can_receive_girl_commissions

  if (form.staff_role === 'CLEANING') {
    payload.cleaning_base_amount = form.cleaning_base_amount
    payload.cleaning_room_amount = form.cleaning_room_amount
  }

  if (isCreate) {
    if (form.pin)
      payload.pin = form.pin
    if (form.password)
      payload.password = form.password
  }

  return payload
}

export function useUserAdminForm() {
  const form = ref(emptyUserForm())
  const branches = ref([])

  const showCommissionField = computed(() => form.value.staff_role === 'WAITER')
  const showGirlCommissionField = computed(() => form.value.staff_role === 'GIRL')
  const showCleaningPayField = computed(() => form.value.staff_role === 'CLEANING')

  const loadBranches = async () => {
    const auth = useAuthStore()

    try {
      if (auth.hasPermission('admin.branches.list'))
        branches.value = await fetchAdminBranches()
      else
        branches.value = await fetchAvailableBranches()
    }
    catch {
      try {
        branches.value = await fetchAvailableBranches()
      }
      catch {
        branches.value = []
      }
    }
  }

  const applyDefaultBranches = () => {
    if (branches.value.length === 1) {
      form.value.branch_id = branches.value[0].id
      form.value.accessible_branch_ids = [branches.value[0].id]
    }
  }

  return {
    form,
    branches,
    showCommissionField,
    showGirlCommissionField,
    showCleaningPayField,
    loadBranches,
    applyDefaultBranches,
  }
}
