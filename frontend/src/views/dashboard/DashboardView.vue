<script setup lang="ts">
import { computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { usePermissions } from '@/composables/usePermissions'

const authStore = useAuthStore()
const { isAdmin } = usePermissions()

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
</script>

<template>
  <DashboardLayout>
    <template #header>
      <h1>Panel principal</h1>
    </template>

    <div class="dashboard-content">
      <div class="welcome-card">
        <h2>Bienvenido, {{ authStore.user?.name }}.</h2>
        <p>
          Tu acceso actual es
          <strong>{{ authStore.roles.map(formatRoleLabel).join(', ') || 'Sin rol asignado' }}</strong>
        </p>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">&#128100;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Usuarios</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">&#128193;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Proyectos</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">&#9745;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Tareas</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">&#128101;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Equipos</span>
          </div>
        </div>
      </div>

      <div class="info-cards">
        <div class="info-card">
          <h3>Lo que puedes hacer</h3>
          <p class="card-note">Te mostramos las acciones disponibles con nombres claros.</p>
          <div class="permissions-list">
            <span
              v-for="permission in authStore.permissions"
              :key="permission"
              class="permission-badge"
            >
              {{ formatPermissionLabel(permission) }}
            </span>
            <span v-if="isAdmin" class="permission-badge super">
              Acceso completo
            </span>
          </div>
        </div>

        <div class="info-card" v-if="visibleRoleViews.length > 0">
          <h3>Espacios por rol</h3>
          <p class="card-note">Abre una vista distinta según el perfil con el que trabajes.</p>
          <div class="role-links">
            <RouterLink
              v-for="view in visibleRoleViews"
              :key="view.role"
              :to="view.route"
              class="role-link"
            >
              <strong>{{ view.title }}</strong>
              <span>{{ view.description }}</span>
            </RouterLink>
          </div>
        </div>

        <div class="info-card" v-if="isAdmin">
          <h3>Acciones rápidas</h3>
          <div class="quick-actions">
            <RouterLink to="/users" class="action-btn">
              Gestionar usuarios
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.welcome-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 12px;
}

.welcome-card h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
}

.welcome-card p {
  margin: 0;
  opacity: 0.9;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 2rem;
  width: 60px;
  height: 60px;
  background: #edf2f7;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
}

.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-card h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: #1a202c;
}

.card-note {
  margin: -0.5rem 0 0.75rem;
  color: #64748b;
  font-size: 0.875rem;
}

.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.permission-badge {
  background: #edf2f7;
  color: #4a5568;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.permission-badge.super {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.role-links {
  display: grid;
  gap: 0.75rem;
}

.role-link {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 0.9rem 1rem;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  text-decoration: none;
  color: #1a202c;
}

.role-link strong {
  font-size: 0.95rem;
}

.role-link span {
  color: #64748b;
  font-size: 0.85rem;
}

.quick-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: #667eea;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.2s;
}

.action-btn:hover {
  background: #5a67d8;
}
</style>
