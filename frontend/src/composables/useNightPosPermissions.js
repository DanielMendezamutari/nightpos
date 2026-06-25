import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useNightPosPermissions() {
  const auth = useAuthStore()

  const can = permission => auth.hasPermission(permission)

  return {
    can,
    canListProducts: computed(() => can('products.list')),
    canCreateProduct: computed(() => can('products.create')),
    canUpdateProduct: computed(() => can('products.update')),
    canCreateCategory: computed(() => can('products.create')),
    canAccessCash: computed(() => can('cash.access')),
    canAccessOrders: computed(() => can('orders.access')),
    canUpdateOrderItems: computed(() => can('orders.update_items')),
    canCancelOrderItem: computed(() => can('orders.cancel_item')),
    canUpdateOrderHeader: computed(() => can('orders.update_header')),
    canCancelOrder: computed(() => can('orders.cancel')),
    canListSales: computed(() => can('sales.list')),
    canChargeOrders: computed(() => can('sales.charge')),
    canDirectSale: computed(() => can('sales.direct_create')),
    canListAdminUsers: computed(() => can('admin.users.list')),
    canCreateAdminUser: computed(() => can('admin.users.create')),
    canUpdateAdminUser: computed(() => can('admin.users.update')),
    canListAdminBranches: computed(() => can('admin.branches.list')),
    canCreateAdminBranch: computed(() => can('admin.branches.create')),
    canListAdminTenants: computed(() => can('admin.tenants.list')),
    canCreateAdminTenant: computed(() => can('admin.tenants.create')),
    canAccessSettlements: computed(() => can('settlements.access')),
    canGenerateSettlements: computed(() => can('settlements.generate')),
    canPaySettlements: computed(() => can('settlements.pay')),
    canManageSettlementFines: computed(() => can('settlements.fines.manage')),
    canListSettlementHistory: computed(() => can('settlements.history')),
    canAccessRoles: computed(() => can('roles.access')),
    canCreateRole: computed(() => can('roles.create')),
    canUpdateRole: computed(() => can('roles.update')),
    canDeleteRole: computed(() => can('roles.delete')),
    canUpdateRolePermissions: computed(() => can('roles.permissions.update')),
    canAccessPermissionsCatalog: computed(() => can('permissions.access')),
  }
}
