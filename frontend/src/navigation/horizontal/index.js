import { normalizeRole } from '@/navigation/vertical'

export const horizontalNavItems = [
  {
    title: 'Dashboard',
    to: { path: '/' },
    icon: { icon: 'ri-dashboard-line' },
    roles: ['admin', 'cashier', 'waiter', 'bartender'],
  },
  {
    title: 'Salón',
    icon: { icon: 'ri-layout-grid-line' },
    roles: ['admin', 'cashier', 'waiter'],
    children: [
      {
        title: 'Mesas y Salones',
        to: { path: '/waiter/tables' },
        icon: { icon: 'ri-layout-grid-line' },
        roles: ['admin', 'cashier', 'waiter'],
      },
    ],
  },
  {
    title: 'Caja & Facturación',
    icon: { icon: 'ri-bank-card-line' },
    roles: ['admin', 'cashier'],
    children: [
      {
        title: 'Comandas y Cobro',
        to: { path: '/cashier/orders' },
        icon: { icon: 'ri-file-list-3-line' },
        roles: ['admin', 'cashier'],
      },
      {
        title: 'Arqueo de Caja',
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
    ],
  },
  {
    title: 'Cocina & Barra',
    icon: { icon: 'ri-restaurant-line' },
    roles: ['admin', 'bartender'],
    children: [
      {
        title: 'Despacho y Producción',
        to: { path: '/bar/production' },
        icon: { icon: 'ri-restaurant-line' },
        roles: ['admin', 'bartender'],
      },
    ],
  },
  {
    title: 'Administración',
    icon: { icon: 'ri-settings-4-line' },
    roles: ['admin'],
    children: [
      {
        title: 'Menú y Platos',
        to: { path: '/admin/products' },
        icon: { icon: 'ri-book-open-line' },
        roles: ['admin'],
      },
    ],
  },
]

export function filterHorizontalNavByRole(items, role = 'waiter') {
  const normRole = normalizeRole(role)
  const isSuper = normRole === 'admin'

  const result = []

  for (const item of items) {
    const itemRoles = (item.roles || []).map(normalizeRole)
    const allowed = isSuper || itemRoles.length === 0 || itemRoles.includes(normRole)

    if (!allowed) continue

    if (item.children?.length) {
      const filteredChildren = item.children.filter(child => {
        const childRoles = (child.roles || []).map(normalizeRole)
        return isSuper || childRoles.length === 0 || childRoles.includes(normRole)
      })

      if (filteredChildren.length > 0) {
        result.push({ ...item, children: filteredChildren })
      }
    } else {
      result.push(item)
    }
  }

  return result
}

export default horizontalNavItems
