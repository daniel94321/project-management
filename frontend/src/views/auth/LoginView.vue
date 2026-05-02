<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials, ApiError } from '@/types'
import { AxiosError } from 'axios'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const appName = import.meta.env.VITE_APP_NAME || 'Project Management'
const registered = route.query.registered === '1'

const form = reactive<LoginCredentials>({
  email: '',
  password: '',
  remember: false,
})

const errors = ref<Record<string, string[]>>({})
const generalError = ref('')
const isSubmitting = ref(false)

async function handleSubmit() {
  errors.value = {}
  generalError.value = ''
  isSubmitting.value = true

  try {
    await authStore.login(form)

    // Redirect to intended page or dashboard
    const redirect = route.query.redirect as string
    router.push(redirect || '/dashboard')
  } catch (error) {
    if (error instanceof AxiosError && error.response) {
      const data = error.response.data as ApiError

      if (error.response.status === 422 && data.errors) {
        errors.value = data.errors
      } else {
        generalError.value = data.message || 'An error occurred during login.'
      }
    } else {
      generalError.value = 'An unexpected error occurred. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

function getError(field: string): string | undefined {
  return errors.value[field]?.[0]
}

function goToLanding() {
  router.push({ name: 'landing' })
}

function goToRegister() {
  router.push({ name: 'register' })
}
</script>

<template>
  <div class="login-page">
    <div class="bg-orb orb-1" aria-hidden="true"></div>
    <div class="bg-orb orb-2" aria-hidden="true"></div>

    <button type="button" class="home-fab" @click="goToLanding">
      Volver al inicio
    </button>

    <div class="login-shell">
      <aside class="brand-panel">
        <p class="brand-kicker">Workspace Hub</p>
        <h1>{{ appName }}</h1>
        <p>
          Organize projects, align teams, and track progress from one focused
          dashboard.
        </p>

        <ul>
          <li>Live project snapshots</li>
          <li>Role-based permissions</li>
          <li>Task and deadline visibility</li>
        </ul>
      </aside>

      <div class="login-card">
        <div class="login-header">
          <p class="eyebrow">Welcome back</p>
          <h2>Sign in to continue</h2>
          <p>Use your team account credentials to access the dashboard.</p>
        </div>

        <form @submit.prevent="handleSubmit" class="login-form">
          <div v-if="registered" class="success-alert">
            Cuenta creada correctamente. Ya puedes iniciar sesión.
          </div>

          <div v-if="generalError" class="error-alert">
            {{ generalError }}
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="name@company.com"
              :class="{ 'input-error': getError('email') }"
              required
            />
            <span v-if="getError('email')" class="error-text">
              {{ getError('email') }}
            </span>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="Enter your password"
              :class="{ 'input-error': getError('password') }"
              required
            />
            <span v-if="getError('password')" class="error-text">
              {{ getError('password') }}
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input v-model="form.remember" type="checkbox" />
              <span>Remember me</span>
            </label>
          </div>

          <button
            type="submit"
            class="submit-button"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting">Signing in...</span>
            <span v-else>Access workspace</span>
          </button>
        </form>

        <div class="login-footer">
          <p class="demo-title">Demo credentials</p>
          <p class="demo-credentials">
            <strong>Email:</strong> admin@example.com<br />
            <strong>Password:</strong> password
          </p>
          <p class="helper-link">
            ¿No tienes cuenta?
            <button type="button" class="text-link" @click="goToRegister">Crear cuenta</button>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  --bg-base: #f5f7fb;
  --bg-card: rgba(255, 255, 255, 0.82);
  --text-main: #0f172a;
  --text-muted: #50607a;
  --border-soft: #dbe3ef;
  --accent: #d97706;
  --accent-deep: #b45309;
  --focus-ring: rgba(217, 119, 6, 0.2);

  font-family: 'Space Grotesk', 'Segoe UI', 'Helvetica Neue', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    radial-gradient(circle at 10% 15%, rgba(253, 224, 71, 0.36), transparent 30%),
    radial-gradient(circle at 85% 20%, rgba(45, 212, 191, 0.2), transparent 33%),
    linear-gradient(140deg, #f9fbff 0%, var(--bg-base) 45%, #eef3fb 100%);
  padding: 1.25rem;
  position: relative;
  overflow: hidden;
}

.bg-orb {
  position: absolute;
  border-radius: 999px;
  filter: blur(24px);
  z-index: 0;
  animation: drift 12s ease-in-out infinite alternate;
}

.home-fab {
  position: absolute;
  top: 1rem;
  left: 1rem;
  z-index: 2;
  border: 1px solid rgba(255, 255, 255, 0.75);
  background: rgba(255, 255, 255, 0.72);
  color: #0f172a;
  padding: 0.6rem 0.9rem;
  border-radius: 999px;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
  backdrop-filter: blur(12px);
}

.orb-1 {
  width: 260px;
  height: 260px;
  background: rgba(251, 191, 36, 0.3);
  top: -80px;
  right: -50px;
}

.orb-2 {
  width: 230px;
  height: 230px;
  background: rgba(20, 184, 166, 0.22);
  left: -60px;
  bottom: -70px;
}

.login-shell {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 920px;
  background: var(--bg-card);
  border: 1px solid rgba(255, 255, 255, 0.65);
  border-radius: 24px;
  backdrop-filter: blur(12px);
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.16);
  display: grid;
  grid-template-columns: 1fr 1fr;
  overflow: hidden;
  animation: rise-in 0.6s ease-out;
}

.brand-panel {
  background: linear-gradient(170deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.93));
  color: #f8fafc;
  padding: 2.75rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 1.1rem;
}

.brand-kicker {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.72rem;
  color: rgba(248, 250, 252, 0.72);
}

.brand-panel h1 {
  font-size: clamp(1.9rem, 3vw, 2.45rem);
  line-height: 1.08;
  font-weight: 700;
}

.brand-panel p {
  color: rgba(241, 245, 249, 0.82);
  line-height: 1.6;
}

.brand-panel ul {
  margin-top: 0.75rem;
  display: grid;
  gap: 0.65rem;
}

.brand-panel li {
  position: relative;
  padding-left: 1.2rem;
  color: rgba(241, 245, 249, 0.9);
}

.brand-panel li::before {
  content: '';
  width: 7px;
  height: 7px;
  border-radius: 999px;
  background: #f59e0b;
  position: absolute;
  left: 0;
  top: 0.54rem;
}

.login-card {
  padding: 2.4rem;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-header {
  margin-bottom: 2rem;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 0.72rem;
  color: #64748b;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.login-header h2 {
  font-size: clamp(1.55rem, 2.2vw, 2rem);
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 0.45rem;
}

.login-header p {
  color: var(--text-muted);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.form-group label {
  font-weight: 600;
  color: #334155;
  font-size: 0.875rem;
}

.form-group input[type="email"],
.form-group input[type="password"] {
  padding: 0.8rem 0.95rem;
  border: 1px solid var(--border-soft);
  background: rgba(255, 255, 255, 0.95);
  border-radius: 10px;
  font-size: 1rem;
  color: var(--text-main);
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: var(--accent);
  transform: translateY(-1px);
  box-shadow: 0 0 0 4px var(--focus-ring);
}

.form-group input.input-error {
  border-color: #ef4444;
}

.error-text {
  color: #ef4444;
  font-size: 0.75rem;
}

.error-alert {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.72rem 0.95rem;
  border-radius: 8px;
  font-size: 0.875rem;
}

.success-alert {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 0.72rem 0.95rem;
  border-radius: 8px;
  font-size: 0.875rem;
}

.checkbox-group {
  flex-direction: row;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: normal;
  color: #475569;
}

.checkbox-label input[type="checkbox"] {
  width: 1.05rem;
  height: 1.05rem;
  accent-color: var(--accent);
}

.submit-button {
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
  color: white;
  padding: 0.88rem 1.4rem;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-1px);
  filter: brightness(1.03);
  box-shadow: 0 12px 20px rgba(180, 83, 9, 0.3);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.login-footer {
  margin-top: 1.35rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.helper-link {
  margin-top: 0.65rem;
  color: #475569;
  font-size: 0.9rem;
}

.text-link {
  display: inline;
  padding: 0;
  border: 0;
  background: transparent;
  color: #b45309;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  font: inherit;
}

.demo-title {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748b;
  margin-bottom: 0.35rem;
}

.demo-credentials {
  font-size: 0.8rem;
  color: #475569;
  line-height: 1.6;
  margin: 0;
}

@keyframes rise-in {
  from {
    transform: translateY(14px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes drift {
  from {
    transform: translateY(0) scale(1);
  }
  to {
    transform: translateY(-16px) scale(1.03);
  }
}

@media (max-width: 920px) {
  .login-shell {
    max-width: 540px;
    grid-template-columns: 1fr;
  }

  .brand-panel {
    display: none;
  }

  .login-card {
    padding: 2rem 1.3rem;
  }
}

@media (max-width: 480px) {
  .login-page {
    padding: 0.75rem;
  }

  .login-card {
    padding: 1.7rem 1rem;
  }
}
</style>
