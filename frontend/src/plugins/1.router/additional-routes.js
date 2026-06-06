import { readAuthSessionFromCookies, readContextFromCookies } from '@/utils/authSession'
import { resolveHomeRoute } from '@/utils/resolveHomeRoute'

const emailRouteComponent = () => import('@/pages/apps/email/index.vue')

export const redirects = [
  {
    path: '/',
    name: 'index',
    redirect: () => {
      const { isLoggedIn, user, corrupt } = readAuthSessionFromCookies()

      if (corrupt || !isLoggedIn)
        return '/login'

      return resolveHomeRoute(user, readContextFromCookies())
    },
  },
  {
    path: '/pages/user-profile',
    name: 'pages-user-profile',
    redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
  },
  {
    path: '/pages/account-settings',
    name: 'pages-account-settings',
    redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
  },
]

export const routes = [
  {
    path: '/apps/email/filter/:filter',
    name: 'apps-email-filter',
    component: emailRouteComponent,
    meta: {
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/apps/email/label/:label',
    name: 'apps-email-label',
    component: emailRouteComponent,
    meta: {
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
]
