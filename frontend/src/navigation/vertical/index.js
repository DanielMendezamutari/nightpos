export const navItems = [
  {
    title: 'Dashboard / Inicio',
    to: { path: '/' },
    icon: { icon: 'ri-dashboard-line' },
    roles: ['admin', 'cashier', 'waiter', 'bartender'],
  },
  {
    heading: 'Salón & Mesas',
    roles: ['admin', 'cashier', 'waiter'],
  },
  {
    title: 'Plano de Mesas',
    to: { path: '/waiter/tables' },
    icon: { icon: 'ri-layout-grid-line' },
    roles: ['admin', 'cashier', 'waiter'],
  },
  {
    heading: 'Caja & Facturación',
    roles: ['admin', 'cashier'],
  },
  {
    title: 'Comandas y Cobro',
    to: { path: '/cashier/orders' },
    icon: { icon: 'ri-file-list-3-line' },
    roles: ['admin', 'cashier'],
  },
  {
    title: 'Arqueo de Caja (Efectivo)',
    to: { path: '/cashier/cash-count' },
    icon: { icon: 'ri-money-dollar-box-line' },
    roles: ['admin', 'cashier'],
  },
  {
    title: 'Turnos de Caja',
    to: { path: '/cashier/shift-rotate' },
    icon: { icon: 'ri-repeat-line' },
    roles: ['admin', 'cashier'],
  },
  {
    heading: 'Barra & Cocina',
    roles: ['admin', 'bartender'],
  },
  {
    title: 'Despacho y Producción',
    to: { path: '/bar/production' },
    icon: { icon: 'ri-restaurant-line' },
    roles: ['admin', 'bartender'],
  },
  {
    heading: 'Administración',
    roles: ['admin'],
  },
  {
    title: 'Menú y Platos',
    to: { path: '/admin/products' },
    icon: { icon: 'ri-book-open-line' },
    roles: ['admin'],
  },
]

export function normalizeRole(role) {
  const r = String(role || '').toLowerCase().trim()
  if (['admin', 'owner', 'super_admin', 'superadmin', 'administrador', 'gerente'].includes(r)) {
    return 'admin'
  }
  if (['cashier', 'cajero', 'cajera', 'caja'].includes(r)) {
    return 'cashier'
  }
  if (['waiter', 'mesero', 'mesera', 'garzon', 'garzón', 'mozo'].includes(r)) {
    return 'waiter'
  }
  if (['bartender', 'barman', 'barra', 'cocina', 'chef'].includes(r)) {
    return 'bartender'
  }
  return r || 'waiter'
}

export function filterNavItemsByRole(items, role = 'waiter') {
  const normRole = normalizeRole(role)
  const isSuper = normRole === 'admin'

  const result = []
  let pendingHeading = null

  for (const item of items) {
    if (item.heading) {
      const headingRoles = (item.roles || []).map(normalizeRole)
      const headingAllowed = isSuper || headingRoles.length === 0 || headingRoles.includes(normRole)
      if (headingAllowed) {
        pendingHeading = item
      } else {
        pendingHeading = null
      }
      continue
    }

    const itemRoles = (item.roles || []).map(normalizeRole)
    const allowed = isSuper || itemRoles.length === 0 || itemRoles.includes(normRole)

    if (allowed) {
      if (pendingHeading) {
        result.push(pendingHeading)
        pendingHeading = null
      }
      result.push(item)
    }
  }

  return result
}

export default navItems
