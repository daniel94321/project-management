<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: 'B1', title: 'Entregas', text: 'Consulta archivos, evidencias y productos para revisión.' },
  { value: 'B2', title: 'Criterios', text: 'Aplica listas de evaluación y observaciones por proyecto.' },
  { value: 'B3', title: 'Resultados', text: 'Registra la valoración y el estado de cada entrega.' },
  { value: 'B4', title: 'Seguimiento', text: 'Marca recomendaciones y próximos pasos para el equipo.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Panel de evaluación"
    subtitle="Vista enfocada en revisar entregables y emitir observaciones"
    eyebrow="Evaluador"
    intro="Este espacio facilita el análisis de evidencias y la calificación de proyectos en curso."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>