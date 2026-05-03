<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: 'E1', title: 'Proyectos a evaluar', text: 'Consulta los proyectos asignados para evaluación.' },
  { value: 'E2', title: 'Evaluaciones', text: 'Realiza evaluaciones y registra observaciones.' },
  { value: 'E3', title: 'Rúbricas', text: 'Consulta criterios de evaluación y estándares.' },
  { value: 'E4', title: 'Reportes', text: 'Genera reportes de evaluación y retroalimentación.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Seguimiento - Evaluación"
    subtitle="Monitoreo de evaluaciones y criterios"
    eyebrow="Evaluador"
    intro="Desde aquí realizas evaluaciones, consultas rúbricas y generas reportes de desempeño."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>
