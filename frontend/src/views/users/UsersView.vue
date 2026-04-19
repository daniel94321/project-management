<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import apiClient from '@/api/axios'
import { usePermissions } from '@/composables/usePermissions'
import { useToast } from '@/composables/useToast'
import type { User, Role, PaginatedResponse, UserFilters, CreateUserPayload, UpdateUserPayload } from '@/types'

const { can } = usePermissions()
const toast = useToast()

// ─── Lista de usuarios ───────────────────────────────────────────────────────
const users      = ref<User[]>([])
const isLoading  = ref(false)
const currentPage  = ref(1)
const totalPages   = ref(1)
const totalUsers   = ref(0)

const filters = ref<UserFilters>({
  page: 1,
  per_page: 10,
  search: '',
  status: '',
  sort_by: 'created_at',
  sort_direction: 'desc',
})

// ─── Roles disponibles (para el formulario) ───────────────────────────────────
const availableRoles = ref<Role[]>([])

async function fetchRoles() {
  const response = await apiClient.get<{ data: Role[] }>('/roles')
  availableRoles.value = response.data.data
}

// ─── Fetch usuarios ───────────────────────────────────────────────────────────
async function fetchUsers() {
  isLoading.value = true
  try {
    const params: Record<string, string | number> = {}
    if (filters.value.page)           params.page           = filters.value.page
    if (filters.value.per_page)       params.per_page       = filters.value.per_page
    if (filters.value.search)         params.search         = filters.value.search
    if (filters.value.status)         params.status         = filters.value.status
    if (filters.value.sort_by)        params.sort_by        = filters.value.sort_by
    if (filters.value.sort_direction) params.sort_direction = filters.value.sort_direction

    const response = await apiClient.get<PaginatedResponse<User>>('/users', { params })
    users.value      = response.data.data
    currentPage.value  = response.data.meta.current_page
    totalPages.value   = response.data.meta.last_page
    totalUsers.value   = response.data.meta.total
  } catch (error) {
    console.error('Error fetching users:', error)
  } finally {
    isLoading.value = false
  }
}

// Búsqueda con debounce al cambiar el texto
let searchTimer: ReturnType<typeof setTimeout>
watch(() => filters.value.search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    filters.value.page = 1
    fetchUsers()
  }, 400)
})

watch(() => filters.value.status, () => {
  filters.value.page = 1
  fetchUsers()
})

function handlePageChange(page: number) {
  filters.value.page = page
  fetchUsers()
}

// ─── Modal ────────────────────────────────────────────────────────────────────
const showModal   = ref(false)
const isEditing   = ref(false)
const editingUser = ref<User | null>(null)
const formErrors  = ref<Record<string, string[]>>({})
const isSaving    = ref(false)
const showPassword        = ref(false)
const showPasswordConfirm = ref(false)

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  status: 'active' as 'active' | 'inactive' | 'suspended',
  roles: [] as string[],
})

function openCreateModal() {
  isEditing.value   = false
  editingUser.value = null
  formErrors.value  = {}
  showPassword.value        = false
  showPasswordConfirm.value = false
  form.value = { name: '', email: '', password: '', password_confirmation: '', status: 'active', roles: [] }
  showModal.value   = true
}

function openEditModal(user: User) {
  isEditing.value   = true
  editingUser.value = user
  formErrors.value  = {}
  showPassword.value        = false
  showPasswordConfirm.value = false
  form.value = {
    name:                  user.name,
    email:                 user.email,
    password:              '',
    password_confirmation: '',
    status:                user.status,
    roles:                 user.roles.map(r => r.name),
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function toggleRole(roleName: string) {
  const idx = form.value.roles.indexOf(roleName)
  if (idx === -1) {
    form.value.roles.push(roleName)
  } else {
    form.value.roles.splice(idx, 1)
  }
}

async function submitForm() {
  isSaving.value   = true
  formErrors.value = {}

  try {
    if (isEditing.value && editingUser.value) {
      // Actualizar usuario
      const payload: UpdateUserPayload = {
        name:   form.value.name,
        email:  form.value.email,
        status: form.value.status,
        roles:  form.value.roles,
      }
      if (form.value.password) {
        payload.password              = form.value.password
        payload.password_confirmation = form.value.password_confirmation
      }
      await apiClient.put(`/users/${editingUser.value.id}`, payload)
    } else {
      // Crear usuario
      const payload: CreateUserPayload = {
        name:                  form.value.name,
        email:                 form.value.email,
        password:              form.value.password,
        password_confirmation: form.value.password_confirmation,
        status:                form.value.status,
        roles:                 form.value.roles,
      }
      await apiClient.post('/users', payload)
    }

    const msg = isEditing.value ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.'
    closeModal()
    fetchUsers()
    toast.success(msg)
  } catch (err: any) {
    if (err.response?.status === 422) {
      formErrors.value = err.response.data.errors ?? {}
    } else {
      toast.error('Ocurrió un error inesperado. Intenta de nuevo.')
    }
  } finally {
    isSaving.value = false
  }
}

// ─── Eliminar usuario ─────────────────────────────────────────────────────────
const showDeleteConfirm  = ref(false)
const deletingUser       = ref<User | null>(null)
const isDeleting         = ref(false)

function openDeleteConfirm(user: User) {
  deletingUser.value      = user
  showDeleteConfirm.value = true
}

function cancelDelete() {
  showDeleteConfirm.value = false
  deletingUser.value      = null
}

async function confirmDelete() {
  if (!deletingUser.value) return
  isDeleting.value = true
  try {
    await apiClient.delete(`/users/${deletingUser.value.id}`)
    showDeleteConfirm.value = false
    deletingUser.value      = null
    fetchUsers()
    toast.success('Usuario eliminado correctamente.')
  } catch (error) {
    console.error('Error deleting user:', error)
    toast.error('No se pudo eliminar el usuario.')
  } finally {
    isDeleting.value = false
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatDate(dateString: string | null): string {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric', month: 'short', day: 'numeric',
  })
}

function getStatusClass(status: string): string {
  return { active: 'status-active', inactive: 'status-inactive', suspended: 'status-suspended' }[status] ?? ''
}

onMounted(() => {
  fetchUsers()
  fetchRoles()
})
</script>

<template>
  <DashboardLayout>
    <template #header>
      <div class="header-content">
        <h1>Usuarios</h1>
        <button v-if="can('users.create')" class="btn-primary" @click="openCreateModal">
          + Nuevo Usuario
        </button>
      </div>
    </template>

    <div class="users-content">

      <!-- Filtros -->
      <div class="filters-bar">
        <!-- Búsqueda en tiempo real -->
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Buscar por nombre o email..."
            class="search-input"
          />
          <button
            v-if="filters.search"
            class="clear-btn"
            @click="filters.search = ''"
          >✕</button>
        </div>

        <!-- Filtro de estado -->
        <select v-model="filters.status" class="status-select">
          <option value="">Todos los estados</option>
          <option value="active">Activo</option>
          <option value="inactive">Inactivo</option>
          <option value="suspended">Suspendido</option>
        </select>
      </div>

      <!-- Tabla -->
      <div class="table-container">
        <div v-if="isLoading" class="loading">
          <span class="spinner"></span> Cargando usuarios...
        </div>

        <table v-else-if="users.length > 0" class="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Roles</th>
              <th>Estado</th>
              <th>Último acceso</th>
              <th>Creado</th>
              <th v-if="can('users.update') || can('users.delete')">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>
                <div class="user-name">
                  <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                  {{ user.name }}
                </div>
              </td>
              <td class="email-cell">{{ user.email }}</td>
              <td>
                <span v-for="role in user.roles" :key="role.id" class="role-badge">
                  {{ role.name }}
                </span>
                <span v-if="!user.roles.length" class="no-role">Sin rol</span>
              </td>
              <td>
                <span :class="['status-badge', getStatusClass(user.status)]">
                  {{ user.status }}
                </span>
              </td>
              <td class="date-cell">{{ formatDate(user.last_login_at) }}</td>
              <td class="date-cell">{{ formatDate(user.created_at) }}</td>
              <td v-if="can('users.update') || can('users.delete')">
                <div class="actions">
                  <button
                    v-if="can('users.update')"
                    class="btn-edit"
                    title="Editar usuario"
                    @click="openEditModal(user)"
                  >
                    ✏️ Editar
                  </button>
                  <button
                    v-if="can('users.delete')"
                    class="btn-delete"
                    title="Eliminar usuario"
                    @click="openDeleteConfirm(user)"
                  >
                    🗑️ Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty">
          <p>No se encontraron usuarios.</p>
          <p v-if="filters.search" class="empty-hint">Prueba con otro término de búsqueda.</p>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="totalPages > 1" class="pagination">
        <button :disabled="currentPage === 1" @click="handlePageChange(currentPage - 1)">
          ← Anterior
        </button>
        <span class="page-info">
          Página {{ currentPage }} de {{ totalPages }} ({{ totalUsers }} usuarios)
        </span>
        <button :disabled="currentPage === totalPages" @click="handlePageChange(currentPage + 1)">
          Siguiente →
        </button>
      </div>
    </div>

    <!-- ─── Modal Crear / Editar ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
            <button class="modal-close" @click="closeModal">✕</button>
          </div>

          <form class="modal-body" @submit.prevent="submitForm">

            <!-- Nombre -->
            <div class="form-group">
              <label>Nombre completo <span class="required">*</span></label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Ej: Juan Pérez"
                :class="{ 'input-error': formErrors.name }"
              />
              <p v-if="formErrors.name" class="error-msg">{{ formErrors.name[0] }}</p>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label>Correo electrónico <span class="required">*</span></label>
              <input
                v-model="form.email"
                type="email"
                placeholder="Ej: juan@example.com"
                :class="{ 'input-error': formErrors.email }"
              />
              <p v-if="formErrors.email" class="error-msg">{{ formErrors.email[0] }}</p>
            </div>

            <!-- Contraseña -->
            <div class="form-group">
              <label>
                Contraseña
                <span v-if="!isEditing" class="required">*</span>
                <span v-else class="optional">(dejar vacío para no cambiar)</span>
              </label>
              <div class="input-eye-wrap" :class="{ 'input-error': formErrors.password }">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Mínimo 8 caracteres"
                />
                <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
              <p v-if="formErrors.password" class="error-msg">{{ formErrors.password[0] }}</p>
            </div>

            <!-- Confirmar contraseña -->
            <div class="form-group" v-if="!isEditing || form.password">
              <label>Confirmar contraseña <span class="required">*</span></label>
              <div class="input-eye-wrap">
                <input
                  v-model="form.password_confirmation"
                  :type="showPasswordConfirm ? 'text' : 'password'"
                  placeholder="Repite la contraseña"
                />
                <button type="button" class="eye-btn" @click="showPasswordConfirm = !showPasswordConfirm" tabindex="-1">
                  <svg v-if="showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Estado -->
            <div class="form-group">
              <label>Estado <span class="required">*</span></label>
              <select v-model="form.status" :class="{ 'input-error': formErrors.status }">
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
                <option value="suspended">Suspendido</option>
              </select>
              <p v-if="formErrors.status" class="error-msg">{{ formErrors.status[0] }}</p>
            </div>

            <!-- Roles -->
            <div class="form-group">
              <label>Roles</label>
              <div class="roles-grid">
                <label
                  v-for="role in availableRoles"
                  :key="role.id"
                  class="role-checkbox"
                  :class="{ 'role-checked': form.roles.includes(role.name) }"
                >
                  <input
                    type="checkbox"
                    :value="role.name"
                    :checked="form.roles.includes(role.name)"
                    @change="toggleRole(role.name)"
                  />
                  {{ role.name }}
                </label>
              </div>
              <p v-if="formErrors.roles" class="error-msg">{{ formErrors.roles[0] }}</p>
            </div>

            <!-- Botones -->
            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeModal">
                Cancelar
              </button>
              <button type="submit" class="btn-save" :disabled="isSaving">
                {{ isSaving ? 'Guardando...' : (isEditing ? 'Guardar cambios' : 'Crear usuario') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ─── Modal Confirmar Eliminación ────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="cancelDelete">
        <div class="modal modal-sm">
          <div class="modal-header modal-header-danger">
            <h2>Eliminar usuario</h2>
            <button class="modal-close" @click="cancelDelete">✕</button>
          </div>
          <div class="modal-body">
            <p class="confirm-msg">
              ¿Estás seguro de eliminar a <strong>{{ deletingUser?.name }}</strong>?
            </p>
            <p class="confirm-sub">Esta acción no se puede deshacer.</p>

            <div class="modal-footer">
              <button class="btn-cancel" @click="cancelDelete">Cancelar</button>
              <button class="btn-danger" :disabled="isDeleting" @click="confirmDelete">
                {{ isDeleting ? 'Eliminando...' : 'Sí, eliminar' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </DashboardLayout>
</template>

<style scoped>
/* ─── Header ─────────────────────────────────────────────────────────────── */
.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.header-content h1 { margin: 0; }

.btn-primary {
  background: #667eea;
  color: white;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
}
.btn-primary:hover { background: #5a67d8; }

/* ─── Layout ─────────────────────────────────────────────────────────────── */
.users-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ─── Filtros ────────────────────────────────────────────────────────────── */
.filters-bar {
  display: flex;
  gap: 1rem;
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,.1);
  align-items: center;
}

.search-box {
  flex: 1;
  display: flex;
  align-items: center;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 0 0.75rem;
  background: #f8fafc;
  gap: 0.5rem;
}
.search-icon { font-size: 0.9rem; color: #94a3b8; }
.search-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 0.55rem 0;
  outline: none;
  font-size: 0.95rem;
}
.clear-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  font-size: 0.85rem;
  padding: 0;
}
.clear-btn:hover { color: #4a5568; }

.status-select {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  min-width: 160px;
  background: white;
  font-size: 0.9rem;
}

/* ─── Tabla ──────────────────────────────────────────────────────────────── */
.table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,.1);
  overflow: hidden;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}
.users-table th,
.users-table td {
  padding: 0.9rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f0f4f8;
}
.users-table th {
  background: #f7fafc;
  font-weight: 600;
  color: #4a5568;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: .05em;
}
.users-table tbody tr:hover { background: #f8fafc; }

.user-name {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-weight: 500;
}
.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.email-cell { color: #718096; font-size: 0.9rem; }
.date-cell  { color: #718096; font-size: 0.875rem; white-space: nowrap; }

.role-badge {
  display: inline-block;
  background: #edf2f7;
  color: #4a5568;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
  font-size: 0.75rem;
  margin-right: 0.25rem;
}
.no-role { color: #cbd5e0; font-size: 0.8rem; }

.status-badge {
  display: inline-block;
  padding: 0.2rem 0.7rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}
.status-active   { background: #c6f6d5; color: #22543d; }
.status-inactive { background: #fed7d7; color: #822727; }
.status-suspended{ background: #feebc8; color: #744210; }

.actions { display: flex; gap: 0.4rem; }
.btn-edit {
  background: #ebf4ff;
  color: #3182ce;
  border: 1px solid #bee3f8;
  padding: 0.3rem 0.7rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
}
.btn-edit:hover { background: #bee3f8; }
.btn-delete {
  background: #fff5f5;
  color: #e53e3e;
  border: 1px solid #fed7d7;
  padding: 0.3rem 0.7rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
}
.btn-delete:hover { background: #fed7d7; }

.loading {
  padding: 3rem;
  text-align: center;
  color: #718096;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}
.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty { padding: 3rem; text-align: center; color: #718096; }
.empty-hint { font-size: 0.85rem; color: #a0aec0; }

/* ─── Paginación ─────────────────────────────────────────────────────────── */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,.1);
}
.pagination button {
  background: #667eea;
  color: white;
  border: none;
  padding: 0.45rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
}
.pagination button:disabled { background: #cbd5e0; cursor: not-allowed; }
.page-info { color: #718096; font-size: 0.875rem; }

/* ─── Modal overlay ──────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0,0,0,.2);
  overflow: hidden;
}
.modal-sm { max-width: 400px; }

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f7fafc;
}
.modal-header h2 { margin: 0; font-size: 1.1rem; color: #2d3748; }
.modal-header-danger { background: #fff5f5; }
.modal-header-danger h2 { color: #c53030; }

.modal-close {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: #718096;
  padding: 0.25rem;
  border-radius: 4px;
}
.modal-close:hover { background: #e2e8f0; }

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

/* ─── Formulario ─────────────────────────────────────────────────────────── */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4a5568;
}
.form-group input,
.form-group select {
  padding: 0.55rem 0.85rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.15s;
}
.form-group input:focus,
.form-group select:focus { border-color: #667eea; box-shadow: 0 0 0 2px rgba(102,126,234,.15); }
/* Input con ojo para contraseña */
.input-eye-wrap {
  display: flex;
  align-items: center;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
  transition: border-color 0.15s;
}
.input-eye-wrap:focus-within {
  border-color: #667eea;
  box-shadow: 0 0 0 2px rgba(102,126,234,.15);
}
.input-eye-wrap.input-error { border-color: #e53e3e; }
.input-eye-wrap input {
  flex: 1;
  border: none !important;
  box-shadow: none !important;
  border-radius: 0;
  outline: none;
}
.input-eye-wrap input:focus { box-shadow: none !important; }
.eye-btn {
  background: none;
  border: none;
  padding: 0 0.75rem;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.eye-btn:hover { color: #4a5568; }
.eye-btn svg { width: 18px; height: 18px; }

.input-error { border-color: #e53e3e !important; }
.error-msg { color: #e53e3e; font-size: 0.8rem; margin: 0; }
.required { color: #e53e3e; margin-left: 2px; }
.optional { color: #a0aec0; font-weight: 400; font-size: 0.8rem; margin-left: 4px; }

/* Roles checkboxes */
.roles-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}
.role-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  color: #4a5568;
  transition: all 0.15s;
  user-select: none;
}
.role-checkbox input { margin: 0; accent-color: #667eea; }
.role-checked {
  background: #ebf4ff;
  border-color: #667eea;
  color: #3730a3;
  font-weight: 600;
}

/* ─── Footer modal ───────────────────────────────────────────────────────── */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 0.5rem;
}
.btn-cancel {
  background: white;
  color: #4a5568;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}
.btn-cancel:hover { background: #f7fafc; }
.btn-save {
  background: #667eea;
  color: white;
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-save:not(:disabled):hover { background: #5a67d8; }

.btn-danger {
  background: #e53e3e;
  color: white;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-danger:not(:disabled):hover { background: #c53030; }

.confirm-msg { margin: 0; font-size: 1rem; color: #2d3748; }
.confirm-sub { margin: 0.25rem 0 0; font-size: 0.85rem; color: #718096; }
</style>
