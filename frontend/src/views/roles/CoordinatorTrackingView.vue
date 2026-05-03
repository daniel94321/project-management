<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'
import ProjectCommunicationsPanel from '@/components/ProjectCommunicationsPanel.vue'

const cards = [
  { value: 'C1', title: 'Proyectos', text: 'Supervisa los proyectos asignados y su progreso.' },
  { value: 'C2', title: 'Equipos', text: 'Gestiona los equipos de trabajo y sus integrantes.' },
  { value: 'C3', title: 'Entregas', text: 'Revisa entregas, hitos y fechas importantes.' },
  { value: 'C4', title: 'Reportes', text: 'Consulta datos de desempeño y productividad.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Seguimiento - Coordinación"
    subtitle="Monitoreo de proyectos y equipos"
    eyebrow="Coordinador"
    intro="Desde aquí coordinas el trabajo de los equipos, supervisa entregas y monitoreas el progreso."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  >
    <div class="tracking-panel-wrap">
      <ProjectCommunicationsPanel />
    </div>
  </RoleWorkspaceShell>
</template>

<style scoped>
.tracking-panel-wrap {
  margin-top: 1rem;
}
</style>
