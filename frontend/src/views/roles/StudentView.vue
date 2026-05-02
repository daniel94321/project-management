<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: 'D1', title: 'Mis proyectos', text: 'Accede a los espacios asignados y sigue el trabajo activo.' },
  { value: 'D2', title: 'Tareas', text: 'Consulta pendientes, entregas y fechas importantes.' },
  { value: 'D3', title: 'Entregables', text: 'Sube avances y documentos para revisión.' },
  { value: 'D4', title: 'Retroalimentación', text: 'Revisa observaciones y mejoras sugeridas.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Panel de estudiante"
    subtitle="Vista enfocada en la participación y entrega de actividades"
    eyebrow="Estudiante"
    intro="Desde aquí puedes organizar tu trabajo, ver tareas y llevar control de tus entregables."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>