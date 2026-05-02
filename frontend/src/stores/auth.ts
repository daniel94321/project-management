import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient, { getCsrfCookie } from '@/api/axios'
import type { User, LoginCredentials, LoginResponse } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const isInitialized = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!user.value)

  const permissions = computed(() => user.value?.permissions || [])

  const roles = computed(() => user.value?.roles?.map(r => r.name) || [])

  // Actions
  async function initAuth(): Promise<void> {
    if (isInitialized.value) return

    try {
      isLoading.value = true
      await fetchUser()
    } catch {
      // User not authenticated - this is expected
      user.value = null
    } finally {
      isLoading.value = false
      isInitialized.value = true
    }
  }

  async function login(credentials: LoginCredentials): Promise<void> {
    isLoading.value = true

    try {
      // Get CSRF cookie first
      await getCsrfCookie()

      // Perform login
      const response = await apiClient.post<LoginResponse>('/auth/login', credentials)
      user.value = response.data.user
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    isLoading.value = true

    try {
      await apiClient.post('/auth/logout')
    } finally {
      user.value = null
      isLoading.value = false
    }
  }

  async function fetchUser(): Promise<void> {
    const response = await apiClient.get<{ user: User }>('/auth/me')
    user.value = response.data.user
  }

  function clearAuth(): void {
    user.value = null
    isInitialized.value = false
  }

  // Permission checking
  function hasPermission(permission: string): boolean {
    return permissions.value.includes(permission)
  }

  function hasAnyPermission(permissionList: string[]): boolean {
    return permissionList.some(p => hasPermission(p))
  }

  function hasAllPermissions(permissionList: string[]): boolean {
    return permissionList.every(p => hasPermission(p))
  }

  function hasRole(role: string): boolean {
    return roles.value.includes(role)
  }

  function hasAnyRole(roleList: string[]): boolean {
    return roleList.some(r => hasRole(r))
  }

  return {
    // State
    user,
    isLoading,
    isInitialized,
    // Getters
    isAuthenticated,
    permissions,
    roles,
    // Actions
    initAuth,
    login,
    logout,
    fetchUser,
    clearAuth,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole,
  }
})
