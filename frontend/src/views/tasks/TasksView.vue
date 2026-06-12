<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import apiClient from '@/api/axios'
import { usePermissions } from '@/composables/usePermissions'
import { useToast } from '@/composables/useToast'
import type { PaginatedResponse } from '@/types'

interface Task {
  id: string
  title: string
  description: string | null
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled'
  priority: 'low' | 'medium' | 'high'
  due_date: string | null
  project_id: string
  project: { id: string; name: string } | null
  assigned_to: string | null
  assigned_user: { id: string; name: string; email: string } | null
  created_at: string
  updated_at: string
}

interface TaskPayload {
  title: string
  description?: string
  status?: Task['status']
  priority?: Task['priority']
  due_date?: string
  project_id: string
  assigned_to?: string
}

interface Project {
  id: string
  name: string
}

const { can } = usePermissions()
const toast = useToast()

const tasks = ref<Task[]>([])
const projects = ref<Project[]>([])
const users = ref<{ id: string; name: string }[]>([])
const isLoading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const totalTasks = ref(0)

const filters = ref({
  page: 1,
  per_page: 10,
  search: '',
  status: '',
  priority: '',
  project_id: '',
})

async function fetchTasks() {
  isLoading.value = true
  try {
    const params: Record<string, string | number> = {}
    if (filters.value.page) params.page = filters.value.page
    if (filters.value.per_page) params.per_page = filters.value.per_page
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.priority) params.priority = filters.value.priority
    if (filters.value.project_id) params.project_id = filters.value.project_id

    const res = await apiClient.get<PaginatedResponse<Task>>('/tasks', { params })
    tasks.value = res.data.data
    currentPage.value = res.data.meta.current_page
    totalPages.value = res.data.meta.last_page
    totalTasks.value = res.data.meta.total
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

async function fetchProjects() {
  try {
    const res = await apiClient.get<PaginatedResponse<Project>>('/projects', { params: { per_page: 100 } })
    projects.value = res.data.data
  } catch { /* ignore */ }
}

async function fetchUsers() {
  try {
    const res = await apiClient.get<PaginatedResponse<{ id: string; name: string }>>('/users', { params: { per_page: 100 } })
    users.value = res.data.data
  } catch { /* ignore */ }
}

let searchTimer: ReturnType<typeof setTimeout>
watch(() => filters.value.search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { filters.value.page = 1; fetchTasks() }, 400)
})
watch(() => [filters.value.status, filters.value.priority, filters.value.project_id], () => {
  filters.value.page = 1; fetchTasks()
})

function handlePageChange(page: number) { filters.value.page = page; fetchTasks() }

// ─── Modal ────────────────────────────────────────────────────────────────────
const showModal = ref(false)
const isEditing = ref(false)
const editingTask = ref<Task | null>(null)
const formErrors = ref<Record<string, string[]>>({})
const isSaving = ref(false)

const form = ref<TaskPayload>({
  title: '',
  description: '',
  status: 'pending',
  priority: 'medium',
  due_date: '',
  project_id: '',
  assigned_to: '',
})

function openCreateModal() {
  isEditing.value = false
  editingTask.value = null
  formErrors.value = {}
  form.value = { title: '', description: '', status: 'pending', priority: 'medium', due_date: '', project_id: '', assigned_to: '' }
  showModal.value = true
}

function openEditModal(task: Task) {
  isEditing.value = true
  editingTask.value = task
  formErrors.value = {}
  form.value = {
    title: task.title,
    description: task.description || '',
    status: task.status,
    priority: task.priority,
    due_date: task.due_date || '',
    project_id: task.project_id,
    assigned_to: task.assigned_to || '',
  }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  isSaving.value = true
  formErrors.value = {}
  try {
    const payload: Record<string, string> = { title: form.value.title, project_id: form.value.project_id }
    if (form.value.description) payload.description = form.value.description
    if (form.value.status) payload.status = form.value.status
    if (form.value.priority) payload.priority = form.value.priority
    if (form.value.due_date) payload.due_date = form.value.due_date
    if (form.value.assigned_to) payload.assigned_to = form.value.assigned_to

    if (isEditing.value && editingTask.value) {
      await apiClient.put(`/tasks/${editingTask.value.id}`, payload)
      toast.success('Tarea actualizada correctamente.')
    } else {
      await apiClient.post('/tasks', payload)
      toast.success('Tarea creada correctamente.')
    }
    closeModal()
    fetchTasks()
  } catch (err: any) {
    if (err.response?.status === 422) formErrors.value = err.response.data.errors ?? {}
    else toast.error('Ocurrió un error inesperado.')
  } finally {
    isSaving.value = false
  }
}

// ─── Eliminar ─────────────────────────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const deletingTask = ref<Task | null>(null)
const isDeleting = ref(false)

function openDeleteConfirm(task: Task) { deletingTask.value = task; showDeleteConfirm.value = true }
function cancelDelete() { showDeleteConfirm.value = false; deletingTask.value = null }

async function confirmDelete() {
  if (!deletingTask.value) return
  isDeleting.value = true
  try {
    await apiClient.delete(`/tasks/${deletingTask.value.id}`)
    showDeleteConfirm.value = false
    deletingTask.value = null
    fetchTasks()
    toast.success('Tarea eliminada correctamente.')
  } catch {
    toast.error('No se pudo eliminar la tarea.')
  } finally { isDeleting.value = false }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatDate(d: string | null) {
  if (!d) return '-'
  return new Date(d + 'T00:00:00').toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' })
}

const statusLabel: Record<string, string> = {
  pending: 'Pendiente', in_progress: 'En progreso', completed: 'Completada', cancelled: 'Cancelada',
}
const priorityLabel: Record<string, string> = { low: 'Baja', medium: 'Media', high: 'Alta' }

function statusClass(s: string) {
  return { pending: 'st-pending', in_progress: 'st-in-progress', completed: 'st-completed', cancelled: 'st-cancelled' }[s] ?? ''
}
function priorityClass(p: string) {
  return { low: 'pr-low', medium: 'pr-medium', high: 'pr-high' }[p] ?? ''
}

onMounted(() => { fetchTasks(); fetchProjects(); fetchUsers() })
</script>

<template>
  <DashboardLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Tareas</h1>
        <button v-if="can('tasks.create')" class="btn-primary" @click="openCreateModal">
          + Nueva Tarea
        </button>
      </div>
    </template>

    <div class="page-content">
      <!-- Filters -->
      <div class="filters-bar">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input v-model="filters.search" type="text" placeholder="Buscar tareas..." class="search-input" />
          <button v-if="filters.search" class="clear-btn" @click="filters.search = ''">✕</button>
        </div>

        <select v-model="filters.project_id" class="filter-select">
          <option value="">Todos los proyectos</option>
          <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>

        <select v-model="filters.status" class="filter-select">
          <option value="">Todos los estados</option>
          <option value="pending">Pendiente</option>
          <option value="in_progress">En progreso</option>
          <option value="completed">Completada</option>
          <option value="cancelled">Cancelada</option>
        </select>

        <select v-model="filters.priority" class="filter-select">
          <option value="">Todas las prioridades</option>
          <option value="high">Alta</option>
          <option value="medium">Media</option>
          <option value="low">Baja</option>
        </select>
      </div>

      <!-- Table -->
      <div class="table-container">
        <div v-if="isLoading" class="loading">
          <span class="spinner"></span> Cargando tareas...
        </div>

        <table v-else-if="tasks.length > 0" class="data-table">
          <thead>
            <tr>
              <th>Tarea</th>
              <th>Proyecto</th>
              <th>Estado</th>
              <th>Prioridad</th>
              <th>Asignado a</th>
              <th>Vencimiento</th>
              <th v-if="can('tasks.update') || can('tasks.delete')">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in tasks" :key="task.id">
              <td>
                <div class="flex items-start gap-3">
                  <div class="task-icon">📋</div>
                  <div>
                    <div class="font-medium text-gray-800">{{ task.title }}</div>
                    <div v-if="task.description" class="desc-text">{{ task.description }}</div>
                  </div>
                </div>
              </td>
              <td class="muted">{{ task.project?.name ?? '-' }}</td>
              <td><span :class="['badge', statusClass(task.status)]">{{ statusLabel[task.status] }}</span></td>
              <td><span :class="['badge', priorityClass(task.priority)]">{{ priorityLabel[task.priority] }}</span></td>
              <td class="muted">{{ task.assigned_user?.name ?? '-' }}</td>
              <td class="muted">{{ formatDate(task.due_date) }}</td>
              <td v-if="can('tasks.update') || can('tasks.delete')">
                <div class="actions">
                  <button v-if="can('tasks.update')" class="btn-edit" @click="openEditModal(task)">✏️ Editar</button>
                  <button v-if="can('tasks.delete')" class="btn-delete" @click="openDeleteConfirm(task)">🗑️ Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty">
          <p>No se encontraron tareas.</p>
          <p v-if="can('tasks.create')" class="empty-hint">
            Haz clic en <strong>+ Nueva Tarea</strong> para comenzar.
          </p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button :disabled="currentPage === 1" @click="handlePageChange(currentPage - 1)">← Anterior</button>
        <span class="page-info">Página {{ currentPage }} de {{ totalPages }} ({{ totalTasks }} tareas)</span>
        <button :disabled="currentPage === totalPages" @click="handlePageChange(currentPage + 1)">Siguiente →</button>
      </div>
    </div>

    <!-- ─── Modal Crear / Editar ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Editar Tarea' : 'Nueva Tarea' }}</h2>
            <button class="modal-close" @click="closeModal">✕</button>
          </div>

          <form class="modal-body" @submit.prevent="submitForm">
            <div class="form-group">
              <label>Título <span class="required">*</span></label>
              <input v-model="form.title" type="text" placeholder="Título de la tarea"
                :class="{ 'input-error': formErrors.title }" />
              <p v-if="formErrors.title" class="error-msg">{{ formErrors.title[0] }}</p>
            </div>

            <div class="form-group">
              <label>Descripción</label>
              <textarea v-model="form.description" rows="3" placeholder="Describe la tarea..."></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Proyecto <span class="required">*</span></label>
                <select v-model="form.project_id" :class="{ 'input-error': formErrors.project_id }">
                  <option value="" disabled>Seleccionar proyecto</option>
                  <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="formErrors.project_id" class="error-msg">{{ formErrors.project_id[0] }}</p>
              </div>
              <div class="form-group">
                <label>Asignar a</label>
                <select v-model="form.assigned_to">
                  <option value="">Sin asignar</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Estado</label>
                <select v-model="form.status">
                  <option value="pending">Pendiente</option>
                  <option value="in_progress">En progreso</option>
                  <option value="completed">Completada</option>
                  <option value="cancelled">Cancelada</option>
                </select>
              </div>
              <div class="form-group">
                <label>Prioridad</label>
                <select v-model="form.priority">
                  <option value="low">Baja</option>
                  <option value="medium">Media</option>
                  <option value="high">Alta</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Fecha de vencimiento</label>
              <input v-model="form.due_date" type="date" :class="{ 'input-error': formErrors.due_date }" />
              <p v-if="formErrors.due_date" class="error-msg">{{ formErrors.due_date[0] }}</p>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeModal">Cancelar</button>
              <button type="submit" class="btn-save" :disabled="isSaving">
                {{ isSaving ? 'Guardando...' : (isEditing ? 'Guardar cambios' : 'Crear tarea') }}
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
            <h2>Eliminar tarea</h2>
            <button class="modal-close" @click="cancelDelete">✕</button>
          </div>
          <div class="modal-body">
            <p class="confirm-msg">¿Eliminar <strong>{{ deletingTask?.title }}</strong>?</p>
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
.btn-primary {
  background: #667eea;
  color: white;
  border: none;
  padding: .5rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
}
.btn-primary:hover { background: #5a67d8; }

.page-content { display: flex; flex-direction: column; gap: 1rem; }

/* Filtros */
.filters-bar { display: flex; gap: 1rem; background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); align-items: center; flex-wrap: wrap; }
.search-box { flex: 1; display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0 .75rem; background: #f8fafc; gap: .5rem; min-width: 200px; }
.search-icon { color: #94a3b8; }
.search-input { flex: 1; border: none; background: transparent; padding: .55rem 0; outline: none; font-size: .95rem; }
.clear-btn { background: none; border: none; cursor: pointer; color: #94a3b8; }
.clear-btn:hover { color: #4a5568; }
.filter-select { padding: .5rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; min-width: 150px; background: white; font-size: .9rem; }

/* Tabla */
.table-container { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: .9rem 1rem; text-align: left; border-bottom: 1px solid #f0f4f8; }
.data-table th { background: #f7fafc; font-weight: 600; color: #4a5568; font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; }
.data-table tbody tr:hover { background: #f8fafc; }

.task-icon { font-size: 1.4rem; line-height: 1; margin-top: .1rem; }
.desc-text { font-size: .8rem; color: #94a3b8; margin-top: .15rem; max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.muted { color: #718096; font-size: .875rem; white-space: nowrap; }

/* Badges */
.badge { display: inline-block; padding: .2rem .7rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
.st-pending    { background: #fefcbf; color: #744210; }
.st-in-progress { background: #bee3f8; color: #2a4365; }
.st-completed  { background: #c6f6d5; color: #22543d; }
.st-cancelled  { background: #fed7d7; color: #822727; }
.pr-low    { background: #f0fff4; color: #276749; border: 1px solid #c6f6d5; }
.pr-medium { background: #fffff0; color: #744210; border: 1px solid #fefcbf; }
.pr-high   { background: #fff5f5; color: #822727; border: 1px solid #fed7d7; }

.actions { display: flex; gap: .4rem; }
.btn-edit { background: #ebf4ff; color: #3182ce; border: 1px solid #bee3f8; padding: .3rem .7rem; border-radius: 4px; cursor: pointer; font-size: .8rem; font-weight: 500; }
.btn-edit:hover { background: #bee3f8; }
.btn-delete { background: #fff5f5; color: #e53e3e; border: 1px solid #fed7d7; padding: .3rem .7rem; border-radius: 4px; cursor: pointer; font-size: .8rem; font-weight: 500; }
.btn-delete:hover { background: #fed7d7; }

.loading { padding: 3rem; text-align: center; color: #718096; display: flex; align-items: center; justify-content: center; gap: .75rem; }
.spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty { padding: 3rem; text-align: center; color: #718096; }
.empty-hint { font-size: .875rem; color: #a0aec0; margin-top: .5rem; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.pagination button { background: #667eea; color: white; border: none; padding: .45rem 1rem; border-radius: 6px; cursor: pointer; font-size: .875rem; }
.pagination button:disabled { background: #cbd5e0; cursor: not-allowed; }
.page-info { color: #718096; font-size: .875rem; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal { background: white; border-radius: 12px; width: 100%; max-width: 560px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,.2); overflow: hidden; }
.modal-sm { max-width: 400px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
.modal-header h2 { margin: 0; font-size: 1.1rem; color: #2d3748; }
.modal-header-danger { background: #fff5f5; }
.modal-header-danger h2 { color: #c53030; }
.modal-close { background: none; border: none; font-size: 1rem; cursor: pointer; color: #718096; padding: .25rem; border-radius: 4px; }
.modal-close:hover { background: #e2e8f0; }

.modal-body { padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.1rem; }

.form-group { display: flex; flex-direction: column; gap: .4rem; }
.form-group label { font-size: .875rem; font-weight: 600; color: #4a5568; }
.form-group input, .form-group select, .form-group textarea {
  padding: .55rem .85rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: .95rem; outline: none; transition: border-color .15s; font-family: inherit;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #667eea; box-shadow: 0 0 0 2px rgba(102,126,234,.15); }
.form-group textarea { resize: vertical; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.input-error { border-color: #e53e3e !important; }
.error-msg { color: #e53e3e; font-size: .8rem; margin: 0; }
.required { color: #e53e3e; margin-left: 2px; }

.modal-footer { display: flex; justify-content: flex-end; gap: .75rem; padding-top: .5rem; }
.btn-cancel { background: white; color: #4a5568; border: 1px solid #e2e8f0; padding: .5rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-cancel:hover { background: #f7fafc; }
.btn-save { background: #667eea; color: white; border: none; padding: .5rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-save:disabled { opacity: .6; cursor: not-allowed; }
.btn-save:not(:disabled):hover { background: #5a67d8; }
.btn-danger { background: #e53e3e; color: white; border: none; padding: .5rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-danger:disabled { opacity: .6; cursor: not-allowed; }
.btn-danger:not(:disabled):hover { background: #c53030; }
.confirm-msg { margin: 0; font-size: 1rem; color: #2d3748; }
.confirm-sub { margin: .25rem 0 0; font-size: .85rem; color: #718096; }
</style>