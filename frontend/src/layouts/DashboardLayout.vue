<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import type { RouteLocationRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePermissions } from '@/composables/usePermissions'
import { useNotifications } from '@/composables/useNotifications'
import ToastContainer from '@/components/ToastContainer.vue'

const router = useRouter()
const authStore = useAuthStore()
const { can, isAdmin } = usePermissions()
const { notifications, unreadCount, markAllRead, markRead } = useNotifications()

const isSidebarOpen    = ref(true)
const showNotifications = ref(false)

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value
}

function formatRoleLabel(role: string): string {
  return role.charAt(0).toUpperCase() + role.slice(1)
}

type NavItem = {
  key: string
  label: string
  icon: string
  to: RouteLocationRaw
}

/**
 * Sidebar dinámico:
 * - Perfil -> vista del rol
 * - Seguimiento -> vista del rol (por ahora reutiliza el workspace del rol existente)
 * - Proyectos/Usuarios -> rutas globales con permisos (aún no hay pantallas específicas por sección/rol)
 */
const roleNavItems = computed<NavItem[]>(() => {
  const roles = new Set(authStore.roles)

  const isAdminRole = roles.has('administrador')
  const has = (r: string) => roles.has(r)

  const items: NavItem[] = []

  const add = (key: string, label: string, icon: string, to: NavItem['to']) => {
    if (items.some(i => i.key === key)) return
    items.push({ key, label, icon, to })
  }

  if (isAdminRole) {
    add('perfil-administrador', 'Perfil', '🧭', { name: 'dashboard-admin' })
    add('seguimiento-administrador', 'Seguimiento', '📌', { name: 'dashboard-admin' })
  } else {
    if (has('estudiante')) {
      add('perfil-estudiante', 'Perfil', '🎓', { name: 'dashboard-student' })
      add('seguimiento-estudiante', 'Seguimiento', '📌', { name: 'dashboard-student' })
    }
    if (has('coordinador')) {
      add('perfil-coordinador', 'Perfil', '🧩', { name: 'dashboard-coordinator' })
      add('seguimiento-coordinador', 'Seguimiento', '📌', { name: 'dashboard-coordinator' })
    }
    if (has('evaluador')) {
      add('perfil-evaluador', 'Perfil', '🧪', { name: 'dashboard-evaluator' })
      add('seguimiento-evaluador', 'Seguimiento', '📌', { name: 'dashboard-evaluator' })
    }
    if (has('director')) {
      add('perfil-director', 'Perfil', '📈', { name: 'dashboard-director' })
      add('seguimiento-director', 'Seguimiento', '📌', { name: 'dashboard-director' })
    }
  }

  return items
})

function handleNotificationClick(id: number) {
  markRead(id)
}

const notifIcons: Record<string, string> = {
  info:    'ℹ',
  success: '✓',
  warning: '⚠',
  error:   '✕',
}
</script>

<template>
  <div class="dashboard-layout" :class="{ 'sidebar-collapsed': !isSidebarOpen }">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2 v-if="isSidebarOpen">PM System</h2>
        <button class="toggle-btn" @click="toggleSidebar">
          <span v-if="isSidebarOpen">&larr;</span>
          <span v-else>&rarr;</span>
        </button>
      </div>

      <nav class="sidebar-nav">
        <!-- Dashboard general -->
        <RouterLink :to="{ name: 'dashboard' }" class="nav-item">
          <span class="nav-icon">&#9776;</span>
          <span v-if="isSidebarOpen" class="nav-text">Dashboard</span>
        </RouterLink>

        <!-- Sección por roles (Perfil / Seguimiento) -->
        <template v-for="item in roleNavItems" :key="item.key">
          <RouterLink :to="item.to" class="nav-item">
            <span class="nav-icon">{{ item.icon }}</span>
            <span v-if="isSidebarOpen" class="nav-text">{{ item.label }}</span>
          </RouterLink>
        </template>

        <!-- Sección global (Proyectos / Usuarios) -->
        <RouterLink v-if="can('projects.view')" :to="{ name: 'projects' }" class="nav-item">
          <span class="nav-icon">&#128193;</span>
          <span v-if="isSidebarOpen" class="nav-text">Proyectos</span>
        </RouterLink>

        <RouterLink v-if="can('users.view')" :to="{ name: 'users' }" class="nav-item">
          <span class="nav-icon">&#128100;</span>
          <span v-if="isSidebarOpen" class="nav-text">Usuarios</span>
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info" v-if="isSidebarOpen">
          <div class="user-name-row">
            <span class="user-name">{{ authStore.user?.name }}</span>

            <!-- Campana de notificaciones — solo administrador -->
            <div v-if="isAdmin" class="bell-wrap">
              <button class="bell-btn" @click="toggleNotifications" title="Notificaciones">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                  <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span v-if="unreadCount > 0" class="bell-badge">{{ unreadCount }}</span>
              </button>

              <!-- Dropdown de notificaciones -->
              <Transition name="dropdown">
                <div v-if="showNotifications" class="notif-panel">
                  <div class="notif-header">
                    <span class="notif-title">Notificaciones</span>
                    <button v-if="unreadCount > 0" class="notif-mark-read" @click="markAllRead">
                      Marcar todas como leídas
                    </button>
                  </div>

                  <div class="notif-list">
                    <div
                      v-for="n in notifications"
                      :key="n.id"
                      :class="['notif-item', `notif-${n.type}`, { 'notif-unread': !n.read }]"
                      @click="handleNotificationClick(n.id)"
                    >
                      <span class="notif-icon">{{ notifIcons[n.type] }}</span>
                      <div class="notif-body">
                        <p class="notif-item-title">{{ n.title }}</p>
                        <p class="notif-item-msg">{{ n.message }}</p>
                        <span class="notif-time">{{ n.time }}</span>
                      </div>
                      <span v-if="!n.read" class="notif-dot"></span>
                    </div>

                    <div v-if="notifications.length === 0" class="notif-empty">
                      Sin notificaciones
                    </div>
                  </div>
                </div>
              </Transition>

              <!-- Overlay para cerrar al hacer clic fuera -->
              <div v-if="showNotifications" class="notif-overlay" @click="showNotifications = false"></div>
            </div>
          </div>
          <span class="user-role">
            {{ authStore.roles.map(formatRoleLabel).join(', ') || 'Sin rol' }}
          </span>
        </div>
        <button class="logout-btn" @click="handleLogout">
          <span class="nav-icon">&#128682;</span>
          <span v-if="isSidebarOpen">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="content-header">
        <slot name="header">
          <h1>Dashboard</h1>
        </slot>
      </header>

      <div class="content-body">
        <slot></slot>
      </div>
    </main>
  </div>

  <!-- Toast global -->
  <ToastContainer />
</template>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 250px;
  background: #1a202c;
  color: white;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
}

.sidebar-collapsed .sidebar {
  width: 60px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #2d3748;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.25rem;
  white-space: nowrap;
}

.toggle-btn {
  background: #2d3748;
  border: none;
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-btn:hover {
  background: #4a5568;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #a0aec0;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
}

.nav-item:hover,
.nav-item.router-link-active {
  background: #2d3748;
  color: white;
}

.nav-icon {
  font-size: 1.25rem;
  width: 28px;
  text-align: center;
}

.nav-text {
  white-space: nowrap;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid #2d3748;
}

.user-info {
  margin-bottom: 0.75rem;
}

.user-name-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.user-name {
  font-weight: 600;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.75rem;
  color: #a0aec0;
  text-transform: capitalize;
  display: block;
  margin-top: 0.2rem;
}

/* ─── Campana ─────────────────────────────────────────────────────────────── */
.bell-wrap {
  position: relative;
  flex-shrink: 0;
}

.bell-btn {
  position: relative;
  background: #2d3748;
  border: none;
  color: #a0aec0;
  width: 30px;
  height: 30px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, color 0.2s;
}
.bell-btn:hover {
  background: #4a5568;
  color: white;
}
.bell-btn svg {
  width: 16px;
  height: 16px;
}

.bell-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #e53e3e;
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

/* ─── Panel de notificaciones ─────────────────────────────────────────────── */
.notif-overlay {
  position: fixed;
  inset: 0;
  z-index: 99;
}

.notif-panel {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 0;
  width: 300px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
  z-index: 100;
  overflow: hidden;
  color: #2d3748;
}

.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #e2e8f0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.notif-title {
  font-weight: 700;
  font-size: 0.9rem;
  color: white;
}

.notif-mark-read {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  font-size: 0.7rem;
  cursor: pointer;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  white-space: nowrap;
}
.notif-mark-read:hover {
  background: rgba(255,255,255,0.35);
}

.notif-list {
  max-height: 280px;
  overflow-y: auto;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f0f4f8;
  cursor: pointer;
  transition: background 0.15s;
  position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f8fafc; }
.notif-unread { background: #f7f8ff; }
.notif-unread:hover { background: #eef0ff; }

.notif-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
  margin-top: 0.1rem;
}
.notif-info    .notif-icon { background: #ebf4ff; color: #3730a3; }
.notif-success .notif-icon { background: #f0fff4; color: #22543d; }
.notif-warning .notif-icon { background: #fffbeb; color: #744210; }
.notif-error   .notif-icon { background: #fff5f5; color: #822727; }

.notif-body { flex: 1; min-width: 0; }

.notif-item-title {
  margin: 0;
  font-size: 0.82rem;
  font-weight: 600;
  color: #2d3748;
}
.notif-item-msg {
  margin: 0.15rem 0 0;
  font-size: 0.78rem;
  color: #718096;
  line-height: 1.35;
}
.notif-time {
  font-size: 0.7rem;
  color: #a0aec0;
  display: block;
  margin-top: 0.25rem;
}

.notif-dot {
  width: 7px;
  height: 7px;
  background: #667eea;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 0.4rem;
}

.notif-empty {
  padding: 1.5rem;
  text-align: center;
  color: #a0aec0;
  font-size: 0.875rem;
}

/* Animacion del dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

/* ─── Main content ────────────────────────────────────────────────────────── */
.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem;
  background: #e53e3e;
  border: none;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: #c53030;
}

.main-content {
  flex: 1;
  background: #f7fafc;
  display: flex;
  flex-direction: column;
}

.content-header {
  background: white;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.content-header h1 {
  margin: 0;
  font-size: 1.5rem;
  color: #1a202c;
}

.content-body {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}
</style>
