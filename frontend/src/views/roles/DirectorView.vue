<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: 'C1', title: 'Visión global', text: 'Revisa el estado general de los proyectos y sus responsables.' },
  { value: 'C2', title: 'Indicadores', text: 'Consulta métricas resumidas para apoyar decisiones.' },
  { value: 'C3', title: 'Riesgos', text: 'Identifica proyectos con retrasos o bloqueos críticos.' },
  { value: 'C4', title: 'Aprobaciones', text: 'Da seguimiento a hitos y resultados finales.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Panel de dirección"
    subtitle="Vista ejecutiva para tomar decisiones estratégicas"
    eyebrow="Director"
    intro="Pensado para revisar el panorama completo y priorizar acciones de alto nivel."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>