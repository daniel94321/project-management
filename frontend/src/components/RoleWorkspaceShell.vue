<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { RouteLocationRaw } from 'vue-router'

type RoleCard = {
  title: string
  text: string
  value?: string
}

defineProps<{
  title: string
  subtitle: string
  eyebrow: string
  intro: string
  cards: RoleCard[]
  backTo?: RouteLocationRaw
}>()
</script>

<template>
  <DashboardLayout>
    <template #header>
      <div class="role-header">
        <div>
          <p class="eyebrow">{{ eyebrow }}</p>
          <h1>{{ title }}</h1>
          <p class="subtitle">{{ subtitle }}</p>
        </div>

        <RouterLink v-if="backTo" :to="backTo" class="back-link">Volver al panel</RouterLink>
      </div>
    </template>

    <section class="role-shell">
      <div class="role-intro">
        <p>{{ intro }}</p>
      </div>

      <div class="role-grid">
        <article v-for="card in cards" :key="card.title" class="role-card">
          <span v-if="card.value" class="role-value">{{ card.value }}</span>
          <h3>{{ card.title }}</h3>
          <p>{{ card.text }}</p>
        </article>
      </div>

      <slot></slot>
    </section>
  </DashboardLayout>
</template>

<style scoped>
.role-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.72rem;
  color: #7c3aed;
  font-weight: 700;
  margin-bottom: 0.4rem;
}

.role-header h1 {
  font-size: clamp(1.8rem, 2.6vw, 2.4rem);
  margin: 0;
}

.subtitle {
  margin-top: 0.4rem;
  color: #64748b;
}

.back-link {
  padding: 0.7rem 1rem;
  border-radius: 999px;
  background: #111827;
  color: white;
  text-decoration: none;
  font-weight: 600;
}

.role-shell {
  display: grid;
  gap: 1rem;
}

.role-intro {
  padding: 1.2rem 1.3rem;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.08));
  border: 1px solid #e2e8f0;
  color: #334155;
}

.role-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}

.role-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.role-value {
  display: inline-flex;
  margin-bottom: 0.6rem;
  padding: 0.28rem 0.7rem;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  font-weight: 700;
  font-size: 0.78rem;
}

.role-card h3 {
  margin: 0 0 0.45rem;
}

.role-card p {
  margin: 0;
  color: #64748b;
  line-height: 1.6;
}

@media (max-width: 720px) {
  .role-header {
    flex-direction: column;
  }
}
</style>