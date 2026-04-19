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

  const isSuperAdmin = computed(() => authStore.hasRole('super-admin'))
  const isAdmin = computed(() => authStore.hasAnyRole(['super-admin', 'admin']))

  return {
    can,
    canAny,
    canAll,
    hasRole,
    hasAnyRole,
    isSuperAdmin,
    isAdmin,
  }
}
