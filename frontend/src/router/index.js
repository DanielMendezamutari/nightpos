import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import LoginView from '../views/LoginView.vue'
import ChooseActiveSiteView from '../views/ChooseActiveSiteView.vue'
import DashboardView from '../views/DashboardView.vue'
import ProductsView from '../views/ProductsView.vue'
import BranchesView from '../views/BranchesView.vue'
import SystemLockView from '../views/SystemLockView.vue'
import PosLayout from '../layouts/PosLayout.vue'
import PosMeseroView from '../views/pos/PosMeseroView.vue'
import PosCajeroView from '../views/pos/PosCajeroView.vue'
import PiezasLayout from '../layouts/PiezasLayout.vue'
import PiezasControlView from '../views/piezas/PiezasControlView.vue'
import CajaLayout from '../layouts/CajaLayout.vue'
import CajaWorkspaceView from '../views/caja/CajaWorkspaceView.vue'
import ReportsLayout from '../layouts/ReportsLayout.vue'
import ReportsProductsSoldView from '../views/reports/ReportsProductsSoldView.vue'
import ReportsSalesView from '../views/reports/ReportsSalesView.vue'
import ReportsPersonalView from '../views/reports/ReportsPersonalView.vue'
import SaasOwnerView from '../views/SaasOwnerView.vue'
import BranchSettingsView from '../views/BranchSettingsView.vue'
import AdministracionLayout from '../layouts/AdministracionLayout.vue'
import MantenimientoLayout from '../layouts/MantenimientoLayout.vue'
import MantenimientoProductosView from '../views/maintenance/MantenimientoProductosView.vue'
import MantenimientoComprasView from '../views/maintenance/MantenimientoComprasView.vue'
import MantenimientoTraspasosView from '../views/maintenance/MantenimientoTraspasosView.vue'
import AdminHorariosView from '../views/admin/AdminHorariosView.vue'
import AdminCategoriasView from '../views/admin/AdminCategoriasView.vue'
import AdminSalasView from '../views/admin/AdminSalasView.vue'
import AdminMesasView from '../views/admin/AdminMesasView.vue'
import AdminPersonalView from '../views/admin/AdminPersonalView.vue'
import AdminUsuariosView from '../views/admin/AdminUsuariosView.vue'

const adminRoles = ['admin', 'super_admin', 'manager', 'owner']
const maintenanceRoles = ['manager', 'admin', 'super_admin', 'owner']
const cajaRoles = ['cashier', 'admin', 'super_admin']
const posWaiterRoles = ['waiter']
const posCashierRoles = ['cashier', 'admin', 'super_admin']
const reportesRoles = ['cashier', 'manager', 'admin', 'super_admin', 'owner']

const routes = [
  { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
  {
    path: '/operacion/sucursal',
    name: 'operacion-sucursal',
    component: ChooseActiveSiteView,
    meta: { auth: true, chooseSite: true, roles: ['waiter', 'cashier'] },
  },
  { path: '/', redirect: '/dashboard' },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { auth: true } },
  {
    path: '/pos',
    component: PosLayout,
    meta: { auth: true, roles: ['waiter', 'cashier', 'admin', 'super_admin'] },
    children: [
      { path: '', redirect: { name: 'pos-mesero' } },
      { path: 'mesero', name: 'pos-mesero', component: PosMeseroView, meta: { roles: posWaiterRoles } },
      { path: 'cajero', name: 'pos-cajero', component: PosCajeroView, meta: { roles: posCashierRoles } },
    ],
  },
  {
    path: '/piezas',
    component: PiezasLayout,
    meta: { auth: true, roles: posCashierRoles },
    children: [
      { path: '', redirect: { name: 'piezas-control' } },
      { path: 'control', name: 'piezas-control', component: PiezasControlView, meta: { roles: posCashierRoles } },
    ],
  },
  {
    path: '/caja',
    component: CajaLayout,
    meta: { auth: true, roles: cajaRoles },
    children: [
      { path: '', name: 'caja-workspace', component: CajaWorkspaceView, meta: { roles: cajaRoles } },
      {
        path: 'control',
        redirect: (to) => ({ name: 'caja-workspace', query: { ...to.query, tab: 'apertura' } }),
      },
      {
        path: 'arqueo',
        redirect: (to) => ({ name: 'caja-workspace', query: { ...to.query, tab: 'arqueo' } }),
      },
      {
        path: 'movimientos',
        redirect: (to) => ({ name: 'caja-workspace', query: { ...to.query, tab: 'movimientos' } }),
      },
      {
        path: 'reporte-turno',
        redirect: (to) => ({ name: 'caja-workspace', query: { ...to.query, tab: 'liquidacion' } }),
      },
    ],
  },
  {
    path: '/reportes',
    component: ReportsLayout,
    meta: { auth: true, roles: reportesRoles },
    children: [
      { path: '', redirect: { name: 'reportes-productos-vendidos' } },
      {
        path: 'productos-vendidos',
        name: 'reportes-productos-vendidos',
        component: ReportsProductsSoldView,
        meta: { roles: reportesRoles },
      },
      {
        path: 'ventas',
        name: 'reportes-ventas',
        component: ReportsSalesView,
        meta: { roles: reportesRoles },
      },
      {
        path: 'personal',
        name: 'reportes-personal',
        component: ReportsPersonalView,
        meta: { roles: reportesRoles },
      },
    ],
  },
  {
    path: '/mantenimiento',
    component: MantenimientoLayout,
    meta: { auth: true, roles: maintenanceRoles },
    children: [
      { path: '', redirect: { name: 'mantenimiento-productos' } },
      { path: 'productos', name: 'mantenimiento-productos', component: MantenimientoProductosView, meta: { roles: maintenanceRoles } },
      { path: 'compras', name: 'mantenimiento-compras', component: MantenimientoComprasView, meta: { roles: maintenanceRoles } },
      { path: 'traspasos', name: 'mantenimiento-traspasos', component: MantenimientoTraspasosView, meta: { roles: maintenanceRoles } },
    ],
  },
  { path: '/saas', name: 'saas', component: SaasOwnerView, meta: { auth: true, roles: ['owner'] } },
  { path: '/productos', name: 'productos', component: ProductsView, meta: { auth: true, roles: ['waiter', 'cashier', 'manager', 'admin', 'super_admin'] } },
  { path: '/usuarios', redirect: { name: 'admin-usuarios' } },
  {
    path: '/administracion',
    component: AdministracionLayout,
    meta: { auth: true, roles: adminRoles },
    children: [
      { path: '', redirect: { name: 'admin-mi-sucursal' } },
      { path: 'mi-sucursal', name: 'admin-mi-sucursal', component: BranchSettingsView, meta: { roles: adminRoles } },
      { path: 'usuarios', name: 'admin-usuarios', component: AdminUsuariosView, meta: { roles: ['owner', 'super_admin', 'admin'] } },
      { path: 'personal', name: 'admin-personal', component: AdminPersonalView, meta: { roles: adminRoles } },
      { path: 'detalles/horarios', name: 'admin-horarios', component: AdminHorariosView, meta: { roles: adminRoles } },
      { path: 'detalles/categorias', name: 'admin-categorias', component: AdminCategoriasView, meta: { roles: adminRoles } },
      { path: 'detalles/salas', name: 'admin-salas', component: AdminSalasView, meta: { roles: adminRoles } },
      { path: 'detalles/mesas', name: 'admin-mesas', component: AdminMesasView, meta: { roles: adminRoles } },
    ],
  },
  { path: '/sucursal', redirect: { name: 'admin-mi-sucursal' } },
  { path: '/sucursales', name: 'sucursales', component: BranchesView, meta: { auth: true, roles: ['owner'] } },
  { path: '/sistema', name: 'sistema', component: SystemLockView, meta: { auth: true, roles: ['owner'] } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    }
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token.value && !auth.user.value) {
    try {
      await auth.refreshMe()
    } catch {
      await auth.logout()
    }
  }

  if (to.meta.auth && !auth.isAuthenticated.value) {
    return { name: 'login' }
  }

  if (
    auth.isAuthenticated.value &&
    auth.mustChooseSite.value &&
    !to.meta.chooseSite &&
    to.name !== 'login'
  ) {
    return { name: 'operacion-sucursal' }
  }

  if (to.meta.chooseSite && auth.isAuthenticated.value && !auth.mustChooseSite.value) {
    return { name: 'dashboard' }
  }

  if (to.meta.guest && auth.isAuthenticated.value) {
    return { name: 'dashboard' }
  }

  const requiredRoles = [...to.matched].reverse().find((r) => r.meta?.roles?.length)?.meta.roles
  if (requiredRoles?.length && !requiredRoles.includes(auth.user.value?.role)) {
    return { name: 'dashboard' }
  }

  if (auth.user.value?.role === 'cashier' && auth.requiresOpenShift.value && to.path !== '/caja') {
    return { name: 'caja-workspace', query: { tab: 'apertura' } }
  }

  return true
})

export default router
