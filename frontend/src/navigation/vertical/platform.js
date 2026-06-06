export default [
  { heading: 'Plataforma SaaS' },
  {
    title: 'Empresas',
    to: 'nightpos-platform-tenants',
    icon: { icon: 'ri-building-4-line' },
    action: 'access',
    subject: 'admin.tenants.list',
  },
  {
    title: 'Sucursales',
    to: 'nightpos-platform-branches',
    icon: { icon: 'ri-store-3-line' },
    action: 'access',
    subject: 'admin.branches.list',
  },
]
