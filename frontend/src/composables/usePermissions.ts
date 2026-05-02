import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function usePermissions() {
  const authStore = useAuthStore()

  const can = (permission: string): boolean => {
    return authStore.hasPermission(permission)
  }

  const canAny = (permissions: string[]): boolean => {
    return authStore.hasAnyPermission(permissions)
  }

  const canAll = (permissions: string[]): boolean => {
    return authStore.hasAllPermissions(permissions)
  }

  const hasRole = (role: string): boolean => {
    return authStore.hasRole(role)
  }

  const hasAnyRole = (roles: string[]): boolean => {
    return authStore.hasAnyRole(roles)
  }

  const isAdmin = computed(() => authStore.hasRole('administrador'))
  const isCoordinator = computed(() => authStore.hasRole('coordinador'))
  const isEvaluator = computed(() => authStore.hasRole('evaluador'))
  const isDirector = computed(() => authStore.hasRole('director'))

  return {
    can,
    canAny,
    canAll,
    hasRole,
    hasAnyRole,
    isAdmin,
    isCoordinator,
    isEvaluator,
    isDirector,
  }
}
