export default [
  {
    title: 'Panel de Control',
    to: { name: 'root' },
    icon: { icon: 'ri-home-smile-2-line' },
  },
  {
    heading: 'PUNTO DE VENTA (POS)',
  },
  {
    title: 'SalÃƒÂ³n & Mesas',
    to: { name: 'root' },
    icon: { icon: 'ri-layout-grid-line' },
  },
  {
    title: 'Comandas & Pedidos',
    to: { name: 'comandas' },
    icon: { icon: 'ri-restaurant-2-line' },
  },
  {
    title: 'Monitor Cocina (KDS)',
    to: { name: 'kds' },
    icon: { icon: 'ri-restaurant-line' },
    badgeContent: 'En Vivo',
    badgeClass: 'bg-warning',
  },
  {
    title: 'Caja & Turnos',
    to: { name: 'caja' },
    icon: { icon: 'ri-safe-2-line' },
  },
  {
    title: 'Clientes & CrÃƒÂ©dito',
    to: { name: 'clientes' },
    icon: { icon: 'ri-user-star-line' },
  },
  {
    heading: 'INVENTARIO & PRODUCCIÃƒâ€œN',
  },
  {
    title: 'Inventario & Compras',
    to: { name: 'inventario' },
    icon: { icon: 'ri-archive-line' },
    badgeContent: 'Stock',
    badgeClass: 'bg-primary',
  },
  {
    heading: 'FACTURACIÃƒâ€œN & VENTAS',
  },
  {
    title: 'FacturaciÃƒÂ³n Bolivia',
    to: { name: 'facturas' },
    icon: { icon: 'ri-file-shield-line' },
    badgeContent: 'SIAT',
    badgeClass: 'bg-success',
  },
  {
    title: 'Reportes & Analítica',
    to: { name: 'reportes' },
    icon: { icon: 'ri-bar-chart-grouped-line' },
    badgeContent: 'BI',
    badgeClass: 'bg-info',
  },
  {
    heading: 'SOPORTE RIBERSOFT',
  },
  {
    title: 'WhatsApp: 67369293',
    href: 'https://wa.me/59167369293',
    target: '_blank',
    icon: { icon: 'ri-whatsapp-line' },
  },
]
