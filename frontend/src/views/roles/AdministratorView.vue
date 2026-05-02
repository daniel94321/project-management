<script setup lang="ts">
import RoleWorkspaceShell from '@/components/RoleWorkspaceShell.vue'

const cards = [
  { value: '01', title: 'Usuarios', text: 'Gestiona cuentas, accesos y estados de los integrantes del sistema.' },
  { value: '02', title: 'Permisos', text: 'Define qué puede hacer cada perfil dentro de la plataforma.' },
  { value: '03', title: 'Proyectos', text: 'Supervisa la operación general y el estado de cada proyecto.' },
  { value: '04', title: 'Configuración', text: 'Mantiene la estructura de la aplicación alineada a la institución.' },
]
</script>

<template>
  <RoleWorkspaceShell
    title="Panel de administración"
    subtitle="Vista central para control total del sistema"
    eyebrow="Administrador"
    intro="Este espacio reúne las tareas de control, mantenimiento y seguimiento general de la plataforma."
    :cards="cards"
    :backTo="{ name: 'dashboard' }"
  />
</template>