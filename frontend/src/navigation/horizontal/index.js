export default [
  {
    title: 'Salón & Mesas',
    to: { name: 'root' },
    icon: { icon: 'ri-layout-grid-line' },
    roles: ['admin', 'cajero', 'mesero', 'garzon'],
  },
  {
    title: 'Comandas',
    to: { name: 'comandas' },
    icon: { icon: 'ri-restaurant-2-line' },
    roles: ['admin', 'cajero', 'mesero', 'garzon'],
  },
  {
    title: 'Cocina (KDS)',
    to: { name: 'kds' },
    icon: { icon: 'ri-restaurant-line' },
    roles: ['admin', 'cocina', 'barman'],
  },
  {
    title: 'Caja',
    to: { name: 'caja' },
    icon: { icon: 'ri-safe-2-line' },
    roles: ['admin', 'cajero'],
  },
  {
    title: 'Clientes',
    to: { name: 'clientes' },
    icon: { icon: 'ri-user-star-line' },
    roles: ['admin', 'cajero'],
  },
  {
    title: 'Administración',
    to: { name: 'admin' },
    icon: { icon: 'ri-settings-4-line' },
    roles: ['admin'],
  },
  {
    title: 'Inventario',
    to: { name: 'inventario' },
    icon: { icon: 'ri-archive-line' },
    roles: ['admin'],
  },
  {
    title: 'Facturación',
    to: { name: 'facturas' },
    icon: { icon: 'ri-file-shield-line' },
    roles: ['admin', 'cajero'],
  },
  {
    title: 'Reportes',
    to: { name: 'reportes' },
    icon: { icon: 'ri-bar-chart-grouped-line' },
    roles: ['admin'],
  },
]