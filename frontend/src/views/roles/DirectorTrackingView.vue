<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: 'D1', title: 'Visión general', text: 'Accede a un resumen de todos los proyectos y su estado.' },
  { value: 'D2', title: 'KPIs', text: 'Consulta indicadores clave de desempeño y productividad.' },
  { value: 'D3', title: 'Equipos', text: 'Supervisa el desempeño y composición de equipos de trabajo.' },
  { value: 'D4', title: 'Reportes estratégicos', text: 'Genera reportes de alto nivel para decisiones estratégicas.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Seguimiento - Dirección"
    subtitle="Monitoreo estratégico y análisis de desempeño"
    eyebrow="Director"
    intro="Desde aquí supervisa el desempeño general, consulta KPIs y toma decisiones estratégicas."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>
