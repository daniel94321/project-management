<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: 'A1', title: 'Seguimiento', text: 'Monitorea avances, pendientes y entregables del equipo.' },
  { value: 'A2', title: 'Cronograma', text: 'Observa fechas, fases y puntos de control del proyecto.' },
  { value: 'A3', title: 'Acompañamiento', text: 'Apoya a estudiantes y coordina bloqueos operativos.' },
  { value: 'A4', title: 'Reporte', text: 'Resume el estado general para los demás perfiles.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Panel de coordinación"
    subtitle="Vista para supervisar el avance de los proyectos"
    eyebrow="Coordinador"
    intro="Aquí puedes revisar el progreso, organizar el trabajo y dar soporte a los participantes."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>