<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { usePermissions } from '@/composables/usePermissions'

const authStore = useAuthStore()
const { isSuperAdmin, isAdmin } = usePermissions()
</script>

<template>
  <DashboardLayout>
    <template #header>
      <h1>Dashboard</h1>
    </template>

    <div class="dashboard-content">
      <div class="welcome-card">
        <h2>Welcome back, {{ authStore.user?.name }}!</h2>
        <p>
          You are logged in as
          <strong>{{ authStore.roles.join(', ') || 'User' }}</strong>
        </p>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">&#128100;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Total Users</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">&#128193;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Projects</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">&#9745;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Tasks</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">&#128101;</div>
          <div class="stat-info">
            <span class="stat-value">-</span>
            <span class="stat-label">Teams</span>
          </div>
        </div>
      </div>

      <div class="info-cards">
        <div class="info-card">
          <h3>Your Permissions</h3>
          <div class="permissions-list">
            <span
              v-for="permission in authStore.permissions"
              :key="permission"
              class="permission-badge"
            >
              {{ permission }}
            </span>
            <span v-if="isSuperAdmin" class="permission-badge super">
              All Permissions (Super Admin)
            </span>
          </div>
        </div>

        <div class="info-card" v-if="isAdmin">
          <h3>Quick Actions</h3>
          <div class="quick-actions">
            <RouterLink to="/users" class="action-btn">
              Manage Users
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.welcome-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 12px;
}

.welcome-card h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
}

.welcome-card p {
  margin: 0;
  opacity: 0.9;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 2rem;
  width: 60px;
  height: 60px;
  background: #edf2f7;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
}

.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-card h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: #1a202c;
}

.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.permission-badge {
  background: #edf2f7;
  color: #4a5568;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.permission-badge.super {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.quick-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: #667eea;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.2s;
}

.action-btn:hover {
  background: #5a67d8;
}
</style>
