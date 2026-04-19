<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

function goHome() {
  router.push('/')
}
</script>

<template>
  <div class="not-found">
    <div class="not-found-content">
      <h1>404</h1>
      <h2>Page Not Found</h2>
      <p>The page you're looking for doesn't exist or has been moved.</p>
      <button @click="goHome" class="btn-home">
        Go to Dashboard
      </button>
    </div>
  </div>
</template>

<style scoped>
.not-found {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7fafc;
}

.not-found-content {
  text-align: center;
  padding: 2rem;
}

.not-found-content h1 {
  font-size: 8rem;
  margin: 0;
  color: #667eea;
  line-height: 1;
}

.not-found-content h2 {
  font-size: 2rem;
  margin: 0.5rem 0 1rem;
  color: #1a202c;
}

.not-found-content p {
  color: #718096;
  margin-bottom: 2rem;
}

.btn-home {
  background: #667eea;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-home:hover {
  background: #5a67d8;
}
</style>
