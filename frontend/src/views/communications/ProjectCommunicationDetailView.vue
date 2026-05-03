<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import apiClient from '@/api/axios'
import { useToast } from '@/composables/useToast'
import { usePermissions } from '@/composables/usePermissions'
import type { ProjectCommunication, ProjectPayload } from '@/types'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { hasRole, can } = usePermissions()

const communication = ref<ProjectCommunication | null>(null)
const isLoading = ref(false)
const isSavingProject = ref(false)
const isResolving = ref(false)
const projectErrors = ref<Record<string, string[]>>({})
const resolutionErrors = ref<Record<string, string[]>>({})
const resolutionNote = ref('')

const form = ref<ProjectPayload>({
  name: '',
  description: '',
  status: 'planning',
  priority: 'medium',
  start_date: '',
  end_date: '',
})

const canEditProject = computed(() => can('projects.update') || hasRole('director') || hasRole('evaluador') || hasRole('administrador') || hasRole('coordinador'))

const communicationStatusLabel: Record<string, string> = {
  pending: 'Pendiente',
  approved: 'Aprobada',
  changes_requested: 'Cambios solicitados',
}

const requestTypeLabel: Record<string, string> = {
  modify_project: 'Modificar proyecto',
  postpone_project: 'Aplazar proyecto',
  change_scope: 'Ajustar alcance',
  other: 'Otro',
}

async function fetchCommunication() {
  isLoading.value = true

  try {
    const response = await apiClient.get<{ communication: ProjectCommunication }>(`/project-communications/${route.params.id}`)
    communication.value = response.data.communication
    form.value = {
      name: response.data.communication.project.name,
      description: response.data.communication.project.description ?? '',
      status: response.data.communication.project.status,
      priority: response.data.communication.project.priority,
      start_date: response.data.communication.project.start_date ?? '',
      end_date: response.data.communication.project.end_date ?? '',
    }
  } catch (error) {
    console.error(error)
    toast.error('No se pudo cargar la solicitud.')
  } finally {
    isLoading.value = false
  }
}

async function saveProjectChanges() {
  if (!communication.value) return

  isSavingProject.value = true
  projectErrors.value = {}

  try {
    await apiClient.put(`/projects/${communication.value.project.id}`, form.value)
    toast.success('Proyecto actualizado correctamente.')
    await fetchCommunication()
  } catch (err: any) {
    if (err.response?.status === 422) {
      projectErrors.value = err.response.data.errors ?? {}
    } else {
      toast.error('No se pudo actualizar el proyecto.')
    }
  } finally {
    isSavingProject.value = false
  }
}

async function resolveCommunication(status: 'approved' | 'changes_requested') {
  if (!communication.value) return

  isResolving.value = true
  resolutionErrors.value = {}

  try {
    await apiClient.patch(`/project-communications/${communication.value.id}`, {
      status,
      response: resolutionNote.value,
    })

    toast.success(status === 'approved' ? 'Solicitud aprobada.' : 'Cambios solicitados.')
    await fetchCommunication()
  } catch (err: any) {
    if (err.response?.status === 422) {
      resolutionErrors.value = err.response.data.errors ?? {}
    } else {
      toast.error('No se pudo resolver la solicitud.')
    }
  } finally {
    isResolving.value = false
  }
}

function formatDate(date?: string | null) {
  if (!date) return '-'
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

watch(() => route.params.id, fetchCommunication, { immediate: true })
</script>

<template>
  <DashboardLayout>
    <template #header>
      <div class="header-row">
        <div>
          <p class="eyebrow">Solicitud</p>
          <h1>Detalle de revisión</h1>
          <p class="subtitle">Autoriza el proyecto o solicita ajustes sobre la propuesta enviada.</p>
        </div>
        <button class="back-btn" @click="router.back()">Volver</button>
      </div>
    </template>

    <div v-if="isLoading" class="state-box">Cargando solicitud...</div>

    <section v-else-if="communication" class="detail-grid">
      <article class="panel">
        <h2>Solicitud</h2>
        <div class="field">
          <label>Proyecto</label>
          <p>{{ communication.project.name }}</p>
        </div>
        <div class="field">
          <label>Enviado por</label>
          <p>{{ communication.sender.name }} · {{ communication.sender.email }}</p>
        </div>
        <div class="field">
          <label>Fecha</label>
          <p>{{ formatDate(communication.created_at) }}</p>
        </div>
        <div class="field">
          <label>Tipo de solicitud</label>
          <p>{{ requestTypeLabel[communication.request_type] ?? communication.request_type }}</p>
        </div>
        <div class="field">
          <label>Mensaje</label>
          <p>{{ communication.message }}</p>
        </div>
        <div class="field">
          <label>Estado</label>
          <p>{{ communicationStatusLabel[communication.status] ?? communication.status }}</p>
        </div>
      </article>

      <article class="panel">
        <h2>Editar proyecto</h2>
        <div class="form-grid">
          <div class="form-group">
            <label>Nombre</label>
            <input v-model="form.name" type="text" :disabled="!canEditProject" />
            <p v-if="projectErrors.name" class="error-msg">{{ projectErrors.name[0] }}</p>
          </div>
          <div class="form-group form-span-2">
            <label>Descripción</label>
            <textarea v-model="form.description" rows="4" :disabled="!canEditProject"></textarea>
          </div>
          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.status" :disabled="!canEditProject">
              <option value="planning">Planificación</option>
              <option value="active">Activo</option>
              <option value="completed">Completado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
          <div class="form-group">
            <label>Prioridad</label>
            <select v-model="form.priority" :disabled="!canEditProject">
              <option value="low">Baja</option>
              <option value="medium">Media</option>
              <option value="high">Alta</option>
            </select>
          </div>
          <div class="form-group">
            <label>Fecha de inicio</label>
            <input v-model="form.start_date" type="date" :disabled="!canEditProject" />
          </div>
          <div class="form-group">
            <label>Fecha de fin</label>
            <input v-model="form.end_date" type="date" :disabled="!canEditProject" />
          </div>
        </div>

        <div class="action-row">
          <button class="btn-secondary" :disabled="!canEditProject || isSavingProject" @click="saveProjectChanges">
            {{ isSavingProject ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </article>

      <article class="panel panel-wide">
        <h2>Resolución</h2>
        <div class="form-group">
          <label>Observaciones</label>
          <textarea v-model="resolutionNote" rows="4" placeholder="Escribe la decisión o los cambios requeridos..."></textarea>
          <p v-if="resolutionErrors.response" class="error-msg">{{ resolutionErrors.response[0] }}</p>
        </div>

        <div class="action-row">
          <button class="btn-success" :disabled="isResolving" @click="resolveCommunication('approved')">
            {{ isResolving ? 'Procesando...' : 'Autorizar proyecto' }}
          </button>
          <button class="btn-warning" :disabled="isResolving" @click="resolveCommunication('changes_requested')">
            {{ isResolving ? 'Procesando...' : 'Solicitar cambios' }}
          </button>
        </div>
      </article>
    </section>

    <div v-else class="state-box">Solicitud no encontrada.</div>
  </DashboardLayout>
</template>

<style scoped>
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.eyebrow {
  margin: 0 0 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  color: #7c3aed;
  font-weight: 700;
}

h1 {
  margin: 0;
  font-size: clamp(1.8rem, 2.6vw, 2.4rem);
}

.subtitle {
  margin-top: 0.35rem;
  color: #64748b;
}

.back-btn {
  border: 1px solid #cbd5e1;
  background: white;
  padding: 0.55rem 0.9rem;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
}

.state-box {
  padding: 1rem;
  border-radius: 12px;
  background: #f8fafc;
  color: #64748b;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.panel {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.panel-wide {
  grid-column: 1 / -1;
}

.field {
  margin-top: 0.9rem;
}

.field label,
.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 700;
  color: #475569;
  margin-bottom: 0.35rem;
}

.field p {
  margin: 0;
  color: #1e293b;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-span-2 {
  grid-column: 1 / -1;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  font: inherit;
}

.form-group input:disabled,
.form-group select:disabled,
.form-group textarea:disabled {
  background: #f8fafc;
  color: #94a3b8;
}

.action-row {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.btn-secondary,
.btn-success,
.btn-warning {
  border: none;
  padding: 0.7rem 1rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.btn-secondary { background: #0f172a; color: white; }
.btn-success { background: #16a34a; color: white; }
.btn-warning { background: #d97706; color: white; }

.error-msg {
  color: #dc2626;
  margin: 0.35rem 0 0;
  font-size: 0.8rem;
}

@media (max-width: 900px) {
  .header-row,
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .header-row {
    display: flex;
    flex-direction: column;
  }
}
</style>