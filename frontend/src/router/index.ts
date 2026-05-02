import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'landing',
    component: () => import('@/views/public/LandingView.vue'),
    meta: {
      title: 'Inicio',
    },
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
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: {
      guest: true,
      title: 'Crear cuenta',
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
    path: '/dashboard/administrador',
    name: 'dashboard-admin',
    component: () => import('@/views/roles/AdministratorView.vue'),
    meta: {
      requiresAuth: true,
      role: ['administrador'],
      title: 'Administración',
    },
  },
  {
    path: '/dashboard/coordinador',
    name: 'dashboard-coordinator',
    component: () => import('@/views/roles/CoordinatorView.vue'),
    meta: {
      requiresAuth: true,
      role: ['administrador', 'coordinador'],
      title: 'Coordinación',
    },
  },
  {
    path: '/dashboard/evaluador',
    name: 'dashboard-evaluator',
    component: () => import('@/views/roles/EvaluatorView.vue'),
    meta: {
      requiresAuth: true,
      role: ['administrador', 'evaluador'],
      title: 'Evaluación',
    },
  },
  {
    path: '/dashboard/director',
    name: 'dashboard-director',
    component: () => import('@/views/roles/DirectorView.vue'),
    meta: {
      requiresAuth: true,
      role: ['administrador', 'director'],
      title: 'Dirección',
    },
  },
  {
    path: '/dashboard/estudiante',
    name: 'dashboard-student',
    component: () => import('@/views/roles/StudentView.vue'),
    meta: {
      requiresAuth: true,
      role: ['administrador', 'estudiante'],
      title: 'Estudiante',
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

  if (to.meta.role) {
    const allowedRoles = Array.isArray(to.meta.role)
      ? to.meta.role
      : [to.meta.role]

    if (!authStore.hasAnyRole(allowedRoles as string[])) {
      return { name: 'dashboard' }
    }
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
    role?: string | string[]
    title?: string
  }
}
