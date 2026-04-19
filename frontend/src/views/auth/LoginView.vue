<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials, ApiError } from '@/types'
import { AxiosError } from 'axios'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

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
</script>

<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Project Management</h1>
        <p>Sign in to your account</p>
      </div>

      <form @submit.prevent="handleSubmit" class="login-form">
        <div v-if="generalError" class="error-alert">
          {{ generalError }}
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Enter your email"
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
          <span v-else>Sign in</span>
        </button>
      </form>

      <div class="login-footer">
        <p class="demo-credentials">
          <strong>Demo Credentials:</strong><br />
          Email: admin@example.com<br />
          Password: password
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem;
}

.login-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0 0 0.5rem 0;
}

.login-header p {
  color: #718096;
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.form-group input[type="email"],
.form-group input[type="password"] {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
  padding: 0.75rem 1rem;
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
}

.checkbox-label input[type="checkbox"] {
  width: 1rem;
  height: 1rem;
}

.submit-button {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.login-footer {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.demo-credentials {
  text-align: center;
  font-size: 0.75rem;
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
}
</style>
