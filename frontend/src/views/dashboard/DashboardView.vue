<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { usePermissions } from '@/composables/usePermissions'
import apiClient from '@/api/axios'

const authStore = useAuthStore()
const { isAdmin } = usePermissions()

const stats = ref<Record<string, number>>({
  total_users: 0,
  active_users: 0,
  total_projects: 0,
  active_projects: 0,
  completed_projects: 0,
  total_tasks: 0,
  pending_tasks: 0,
  in_progress_tasks: 0,
  completed_tasks: 0,
  pending_communications: 0,
  my_projects: 0,
  my_tasks: 0,
})
const isLoadingStats = ref(true)

async function fetchStats() {
  try {
    const res = await apiClient.get('/dashboard/stats')
    stats.value = res.data.data
  } catch {
    // Silently fail
  } finally {
    isLoadingStats.value = false
  }
}

const permissionLabels: Record<string, string> = {
  'users.view': 'Ver usuarios',
  'users.create': 'Crear usuarios',
  'users.update': 'Editar usuarios',
  'users.delete': 'Eliminar usuarios',
  'roles.view': 'Ver roles',
  'roles.create': 'Crear roles',
  'roles.update': 'Editar roles',
  'roles.delete': 'Eliminar roles',
  'permissions.view': 'Ver permisos',
  'permissions.assign': 'Asignar permisos',
  'projects.view': 'Ver proyectos',
  'projects.create': 'Crear proyectos',
  'projects.update': 'Editar proyectos',
  'projects.delete': 'Eliminar proyectos',
  'tasks.view': 'Ver tareas',
  'tasks.create': 'Crear tareas',
  'tasks.update': 'Editar tareas',
  'tasks.delete': 'Eliminar tareas',
}

const statCards = computed(() => [
  { icon: '👥', value: stats.value.total_users, label: 'Usuarios', color: 'bg-blue-50 text-blue-600' },
  { icon: '📁', value: stats.value.total_projects, label: 'Proyectos', color: 'bg-purple-50 text-purple-600' },
  { icon: '✅', value: stats.value.total_tasks, label: 'Tareas', color: 'bg-green-50 text-green-600' },
  { icon: '📋', value: stats.value.pending_communications, label: 'Solicitudes pendientes', color: 'bg-amber-50 text-amber-600' },
  { icon: '📌', value: stats.value.my_projects, label: 'Mis proyectos', color: 'bg-indigo-50 text-indigo-600' },
  { icon: '📝', value: stats.value.my_tasks, label: 'Mis tareas', color: 'bg-teal-50 text-teal-600' },
])

const roleViews = [
  {
    role: 'administrador',
    title: 'Administración',
    description: 'Control total de usuarios, permisos, proyectos y configuración.',
    route: { name: 'dashboard-admin' },
  },
  {
    role: 'coordinador',
    title: 'Coordinación',
    description: 'Seguimiento de equipos, proyectos y apoyo operativo.',
    route: { name: 'dashboard-coordinator' },
  },
  {
    role: 'evaluador',
    title: 'Evaluación',
    description: 'Revisión de entregas, observaciones y avance académico.',
    route: { name: 'dashboard-evaluator' },
  },
  {
    role: 'director',
    title: 'Dirección',
    description: 'Visión general, métricas y decisiones estratégicas.',
    route: { name: 'dashboard-director' },
  },
  {
    role: 'estudiante',
    title: 'Estudiante',
    description: 'Acceso a proyectos, tareas asignadas y entregas.',
    route: { name: 'dashboard-student' },
  },
]

const visibleRoleViews = computed(() => {
  const userRoles = new Set(authStore.roles)
  return roleViews.filter(view => userRoles.has(view.role) || userRoles.has('administrador'))
})

function formatRoleLabel(role: string): string {
  return role.charAt(0).toUpperCase() + role.slice(1)
}

function formatPermissionLabel(permission: string): string {
  return permissionLabels[permission] || permission.replace(/\./g, ' ')
}

onMounted(fetchStats)
</script>

<template>
  <DashboardLayout>
    <template #header>
      <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    </template>

    <div class="space-y-6">
      <!-- Welcome card -->
      <div class="bg-gradient-to-r from-primary-500 to-primary-700 text-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-2">¡Hola de nuevo, {{ authStore.user?.name }}!</h2>
        <p class="opacity-90">
          Bienvenido a tu panel de control. Tu rol actual es
          <strong class="font-semibold">{{ authStore.roles.map(formatRoleLabel).join(', ') || 'Sin rol asignado' }}</strong>
        </p>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="stat in statCards" :key="stat.label"
          class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 transition-shadow hover:shadow-md">
          <div class="text-2xl w-14 h-14 rounded-xl flex items-center justify-center shrink-0" :class="stat.color">
            {{ stat.icon }}
          </div>
          <div>
            <div v-if="isLoadingStats" class="h-7 w-16 bg-gray-200 rounded animate-pulse mb-1"></div>
            <span v-else class="text-2xl font-bold text-gray-800">{{ stat.value }}</span>
            <span class="block text-sm text-gray-500">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <!-- Info cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <!-- Permissions -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <h3 class="text-base font-semibold text-gray-800 mb-1">Tus permisos</h3>
          <p class="text-sm text-gray-500 mb-3">Acciones que puedes realizar dentro del sistema.</p>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="permission in authStore.permissions"
              :key="permission"
              class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium"
            >
              {{ formatPermissionLabel(permission) }}
            </span>
            <span v-if="isAdmin" class="bg-gradient-to-r from-primary-500 to-primary-700 text-white px-3 py-1 rounded-full text-xs font-medium">
              Acceso completo
            </span>
          </div>
        </div>

        <!-- Role spaces -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" v-if="visibleRoleViews.length > 0">
          <h3 class="text-base font-semibold text-gray-800 mb-1">Módulos del sistema</h3>
          <p class="text-sm text-gray-500 mb-3">Accede a las secciones habilitadas para tu perfil.</p>
          <div class="space-y-2">
            <RouterLink
              v-for="view in visibleRoleViews"
              :key="view.role"
              :to="view.route"
              class="block p-3 rounded-xl bg-gray-50 border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors no-underline"
            >
              <strong class="text-sm text-gray-800">{{ view.title }}</strong>
              <p class="text-xs text-gray-500 mt-0.5">{{ view.description }}</p>
            </RouterLink>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" v-if="isAdmin">
          <h3 class="text-base font-semibold text-gray-800 mb-1">Acceso directo</h3>
          <p class="text-sm text-gray-500 mb-3">Navegación rápida a las secciones principales.</p>
          <div class="flex flex-wrap gap-2">
            <RouterLink to="/users" class="inline-block bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors no-underline">
              Gestionar usuarios
            </RouterLink>
            <RouterLink to="/projects" class="inline-block bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors no-underline">
              Ver proyectos
            </RouterLink>
            <RouterLink to="/tasks" class="inline-block bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors no-underline">
              Ver tareas
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
