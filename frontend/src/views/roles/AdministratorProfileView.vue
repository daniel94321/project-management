<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

const authStore = useAuthStore()

const user = authStore.user
const roles = authStore.roles
const permissions = authStore.permissions

// Mapeo de permisos técnicos a nombres interpretables
const permissionLabels: Record<string, string> = {
  // Usuarios
  'users.view': 'Ver usuarios',
  'usuarios.ver': 'Ver usuarios',
  'users.create': 'Crear usuarios',
  'usuarios.crear': 'Crear usuarios',
  'users.edit': 'Editar usuarios',
  'usuarios.editar': 'Editar usuarios',
  'usuarios.actualizar': 'Editar usuarios',
  'users.delete': 'Eliminar usuarios',
  'usuarios.eliminar': 'Eliminar usuarios',
  
  // Proyectos
  'projects.view': 'Ver proyectos',
  'proyectos.ver': 'Ver proyectos',
  'projects.read': 'Ver proyectos',
  'proyectos.leer': 'Ver proyectos',
  'projects.create': 'Crear proyectos',
  'proyectos.crear': 'Crear proyectos',
  'projects.edit': 'Editar proyectos',
  'proyectos.editar': 'Editar proyectos',
  'proyectos.actualizar': 'Editar proyectos',
  'proyectos.actualización': 'Editar proyectos',
  'projects.update': 'Editar proyectos',
  'proyectos.update': 'Editar proyectos',
  'projects.delete': 'Eliminar proyectos',
  'proyectos.eliminar': 'Eliminar proyectos',
  'projects.destroy': 'Eliminar proyectos',
  'projects.edit.status': 'Cambiar estado de proyecto',
  'projects.edit.priority': 'Cambiar prioridad de proyecto',
  
  // Tareas
  'tasks.view': 'Ver tareas',
  'tareas.ver': 'Ver tareas',
  'tasks.read': 'Ver tareas',
  'tareas.leer': 'Ver tareas',
  'tasks.create': 'Crear tareas',
  'tareas.crear': 'Crear tareas',
  'tasks.edit': 'Editar tareas',
  'tareas.editar': 'Editar tareas',
  'tareas.actualizar': 'Editar tareas',
  'tasks.update': 'Editar tareas',
  'tareas.update': 'Editar tareas',
  'tasks.delete': 'Eliminar tareas',
  'tareas.eliminar': 'Eliminar tareas',
  'tareas.destroy': 'Eliminar tareas',
  
  // Roles
  'roles.view': 'Ver roles',
  'roles.ver': 'Ver roles',
  'roles.create': 'Crear roles',
  'roles.crear': 'Crear roles',
  'roles.read': 'Ver roles',
  'roles.edit': 'Editar roles',
  'roles.editar': 'Editar roles',
  'roles.actualizar': 'Editar roles',
  'roles.delete': 'Eliminar roles',
  'roles.eliminar': 'Eliminar roles',
  'roles.manage': 'Gestionar roles',
  
  // Permisos
  'permissions.view': 'Ver permisos',
  'permisos.ver': 'Ver permisos',
  'permissions.read': 'Ver permisos',
  'permissions.create': 'Crear permisos',
  'permisos.crear': 'Crear permisos',
  'permissions.assign': 'Asignar permisos',
  'permisos.asignar': 'Asignar permisos',
  'permissions.edit': 'Editar permisos',
  'permisos.editar': 'Editar permisos',
  'permisos.actualizar': 'Editar permisos',
  'permissions.manage': 'Gestionar permisos',
  'permisos.gestionar': 'Gestionar permisos',
  
  // Reportes
  'reports.view': 'Ver reportes',
  'report.view': 'Ver reportes',
  'reportes.ver': 'Ver reportes',
  'reports.create': 'Crear reportes',
  'reportes.crear': 'Crear reportes',
  
  // Evaluaciones
  'evaluations.view': 'Ver evaluaciones',
  'evaluaciones.ver': 'Ver evaluaciones',
  'evaluations.create': 'Crear evaluaciones',
  'evaluaciones.crear': 'Crear evaluaciones',
  'evaluations.edit': 'Editar evaluaciones',
  'evaluaciones.editar': 'Editar evaluaciones',
  'evaluaciones.actualizar': 'Editar evaluaciones',
  
  // Entregas
  'submissions.view': 'Ver entregas',
  'entregas.ver': 'Ver entregas',
  'submissions.review': 'Revisar entregas',
  'entregas.revisar': 'Revisar entregas',
  'submissions.create': 'Crear entregas',
  'entregas.crear': 'Crear entregas',
  
  // Archivos
  'files.upload': 'Subir archivos',
  'archivos.subir': 'Subir archivos',
  'files.download': 'Descargar archivos',
  'archivos.descargar': 'Descargar archivos',
  'files.delete': 'Eliminar archivos',
  'archivos.eliminar': 'Eliminar archivos',
  'files.view': 'Ver archivos',
  'archivos.ver': 'Ver archivos',
  
  // Configuración
  'configuration.manage': 'Gestionar configuración',
  'configuración.gestionar': 'Gestionar configuración',
  'config.manage': 'Gestionar configuración',
  'configuración.manage': 'Gestionar configuración',
}

const formattedPermissions = computed(() => {
  return permissions.map(permission => 
    permissionLabels[permission] || permission
  )
})
</script>

<template>
  <DashboardLayout>
    <template #header>
      <div class="profile-header">
        <div>
          <p class="eyebrow">ADMINISTRADOR</p>
          <h1>Mi Perfil</h1>
          <p class="subtitle">Información personal y permisos de acceso</p>
        </div>
      </div>
    </template>

    <section class="profile-container">
      <!-- Información personal -->
      <div class="profile-card">
        <h2>Información Personal</h2>
        <div class="info-grid">
          <div class="info-item">
            <label>Nombre</label>
            <p>{{ user?.name || 'No disponible' }}</p>
          </div>
          <div class="info-item">
            <label>Email</label>
            <p>{{ user?.email || 'No disponible' }}</p>
          </div>
          <div class="info-item">
            <label>Teléfono</label>
            <p>{{ user?.phone || 'No disponible' }}</p>
          </div>
          <div class="info-item">
            <label>Fecha de creación</label>
            <p>{{ user?.created_at || 'No disponible' }}</p>
          </div>
        </div>
      </div>

      <!-- Roles asignados -->
      <div class="profile-card">
        <h2>Roles Asignados</h2>
        <div class="role-list">
          <div v-if="roles.length > 0" class="roles">
            <span v-for="role in roles" :key="role" class="role-badge">
              {{ role.charAt(0).toUpperCase() + role.slice(1) }}
            </span>
          </div>
          <div v-else class="empty-state">
            Sin roles asignados
          </div>
        </div>
      </div>

      <!-- Permisos -->
      <div class="profile-card">
        <h2>Permisos</h2>
        <div v-if="formattedPermissions.length > 0" class="permissions-list">
          <div v-for="(permission, index) in formattedPermissions" :key="index" class="permission-item">
            <span class="permission-icon">✓</span>
            <span class="permission-name">{{ permission }}</span>
          </div>
        </div>
        <div v-else class="empty-state">
          Sin permisos asignados
        </div>
      </div>
    </section>
  </DashboardLayout>
</template>

<style scoped>
.profile-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  color: #7c3aed;
  font-weight: 700;
  margin-bottom: 0.4rem;
}

.profile-header h1 {
  font-size: clamp(1.8rem, 2.6vw, 2.4rem);
  margin: 0;
}

.subtitle {
  margin-top: 0.4rem;
  color: #64748b;
}

.profile-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.profile-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.profile-card:last-child {
  grid-column: 1 / -1;
}

.profile-card h2 {
  margin: 0 0 1rem;
  font-size: 1.25rem;
  color: #1e293b;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
}

.info-item label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-item p {
  margin: 0;
  color: #1e293b;
  font-size: 1rem;
}

.role-list {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  min-height: 40px;
  align-items: center;
}

.roles {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.role-badge {
  display: inline-flex;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.permissions-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.permission-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.permission-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #10b981;
  color: white;
  font-weight: bold;
  font-size: 0.875rem;
}

.permission-name {
  color: #475569;
  font-size: 0.95rem;
}

.empty-state {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
  font-style: italic;
}

@media (max-width: 720px) {
  .profile-header {
    flex-direction: column;
  }

  .profile-container {
    grid-template-columns: 1fr;
  }

  .profile-card:last-child {
    grid-column: 1;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .permissions-list {
    grid-template-columns: 1fr;
  }
}
</style>
