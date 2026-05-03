<script setup lang="ts">
import { onMounted, ref } from 'vue'
import apiClient from '@/api/axios'
import type { PaginatedResponse, ProjectCommunication } from '@/types'

const communications = ref<ProjectCommunication[]>([])
const isLoading = ref(false)

async function fetchCommunications() {
  isLoading.value = true

  try {
    const response = await apiClient.get<PaginatedResponse<ProjectCommunication>>('/project-communications')
    communications.value = response.data.data
  } catch (error) {
    console.error(error)
    communications.value = []
  } finally {
    isLoading.value = false
  }
}

function formatDate(date: string): string {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const statusLabel: Record<string, string> = {
  planning: 'Planificación',
  active: 'Activo',
  completed: 'Completado',
  cancelled: 'Cancelado',
}

const priorityLabel: Record<string, string> = {
  low: 'Baja',
  medium: 'Media',
  high: 'Alta',
}

onMounted(fetchCommunications)
</script>

<template>
  <section class="communications-panel">
    <div class="panel-header">
      <div>
        <p class="eyebrow">Solicitudes</p>
        <h2>Revisiones pendientes</h2>
      </div>
      <button class="refresh-btn" @click="fetchCommunications">Actualizar</button>
    </div>

    <div v-if="isLoading" class="state-box">Cargando solicitudes...</div>

    <div v-else-if="communications.length === 0" class="state-box">
      No hay solicitudes registradas.
    </div>

    <div v-else class="request-list">
      <article v-for="item in communications" :key="item.id" class="request-card">
        <div class="request-top">
          <div>
            <h3>{{ item.project.name }}</h3>
            <p class="meta">
              {{ item.sender.name }} · {{ formatDate(item.created_at) }}
            </p>
          </div>
          <div class="badges">
            <span class="badge badge-status">{{ statusLabel[item.project.status] }}</span>
            <span class="badge badge-priority">{{ priorityLabel[item.project.priority] }}</span>
          </div>
        </div>

        <p class="message">{{ item.message }}</p>
      </article>
    </div>
  </section>
</template>

<style scoped>
.communications-panel {
  display: grid;
  gap: 1rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.eyebrow {
  margin: 0 0 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  color: #7c3aed;
  font-weight: 700;
}

.panel-header h2 {
  margin: 0;
  font-size: 1.2rem;
}

.refresh-btn {
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  padding: 0.5rem 0.85rem;
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

.request-list {
  display: grid;
  gap: 0.9rem;
}

.request-card {
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
  background: #ffffff;
}

.request-top {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.request-top h3 {
  margin: 0;
  font-size: 1rem;
}

.meta {
  margin: 0.25rem 0 0;
  color: #64748b;
  font-size: 0.88rem;
}

.badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.28rem 0.65rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
}

.badge-status {
  background: #eff6ff;
  color: #1d4ed8;
}

.badge-priority {
  background: #fef3c7;
  color: #92400e;
}

.message {
  margin: 0.85rem 0 0;
  color: #334155;
  line-height: 1.6;
}

@media (max-width: 720px) {
  .panel-header,
  .request-top {
    flex-direction: column;
  }

  .badges {
    justify-content: flex-start;
  }
}
</style>