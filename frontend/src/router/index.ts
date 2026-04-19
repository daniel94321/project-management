import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: {
      guest: true,
      title: 'Login',
    },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/dashboard/DashboardView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Dashboard',
    },
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('@/views/users/UsersView.vue'),
    meta: {
      requiresAuth: true,
      permission: 'users.view',
      title: 'Users',
    },
  },
  {
    path: '/projects',
    name: 'projects',
    component: () => import('@/views/projects/ProjectsView.vue'),
    meta: {
      requiresAuth: true,
      permission: 'projects.view',
      title: 'Proyectos',
    },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: {
      title: 'Not Found',
    },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Navigation guards
router.beforeEach(async (to, _from) => {
  const authStore = useAuthStore()

  // Initialize auth state on first navigation
  if (!authStore.isInitialized) {
    await authStore.initAuth()
  }

  // Update page title
  document.title = to.meta.title
    ? `${to.meta.title} - Project Management`
    : 'Project Management'

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }

  // Check if route is for guests only
  if (to.meta.guest && authStore.isAuthenticated) {
    return { name: 'dashboard' }
  }

  // Check permissions
  if (to.meta.permission && !authStore.hasPermission(to.meta.permission as string)) {
    // Could redirect to a 403 page instead
    return { name: 'dashboard' }
  }

  return true
})

export default router

// Type augmentation for route meta
declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    guest?: boolean
    permission?: string
    permissions?: string[]
    title?: string
  }
}
