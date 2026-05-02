<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { AxiosError } from 'axios'
import apiClient from '@/api/axios'
import type { ApiError, LoginResponse } from '@/types'

const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref<Record<string, string[]>>({})
const generalError = ref('')
const isSubmitting = ref(false)

async function handleSubmit() {
  errors.value = {}
  generalError.value = ''
  isSubmitting.value = true

  try {
    await apiClient.post<LoginResponse>('/auth/register', form)
    router.push({ name: 'login', query: { registered: '1' } })
  } catch (error) {
    if (error instanceof AxiosError && error.response) {
      const data = error.response.data as ApiError

      if (error.response.status === 422 && data.errors) {
        errors.value = data.errors
      } else {
        generalError.value = data.message || 'No se pudo crear la cuenta.'
      }
    } else {
      generalError.value = 'Ocurrió un error inesperado. Intenta de nuevo.'
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

function goToLogin() {
  router.push({ name: 'login' })
}
</script>

<template>
  <main class="register-page">
    <button type="button" class="home-fab" @click="goToLanding">
      Volver al inicio
    </button>

    <section class="register-card">
      <div class="register-copy">
        <p class="eyebrow">Crear cuenta</p>
        <h1>Únete como estudiante y comienza a trabajar en tus proyectos.</h1>
        <p>
          El registro crea una cuenta con el rol de <strong>estudiante</strong> para acceder al sistema desde el inicio.
        </p>
      </div>

      <form class="register-form" @submit.prevent="handleSubmit">
        <div v-if="generalError" class="error-alert">
          {{ generalError }}
        </div>

        <div class="form-group">
          <label for="name">Nombre completo</label>
          <input id="name" v-model="form.name" type="text" placeholder="Tu nombre" :class="{ 'input-error': getError('name') }" required />
          <span v-if="getError('name')" class="error-text">{{ getError('name') }}</span>
        </div>

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input id="email" v-model="form.email" type="email" placeholder="nombre@correo.com" :class="{ 'input-error': getError('email') }" required />
          <span v-if="getError('email')" class="error-text">{{ getError('email') }}</span>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input id="password" v-model="form.password" type="password" placeholder="Mínimo 8 caracteres" :class="{ 'input-error': getError('password') }" required />
          <span v-if="getError('password')" class="error-text">{{ getError('password') }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar contraseña</label>
          <input id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="Repite la contraseña" required />
        </div>

        <button class="submit-button" type="submit" :disabled="isSubmitting">
          <span v-if="isSubmitting">Creando cuenta...</span>
          <span v-else>Crear cuenta</span>
        </button>

        <p class="helper-link">
          ¿Ya tienes una cuenta?
          <button type="button" class="text-link" @click="goToLogin">Inicia sesión</button>
        </p>
      </form>
    </section>
  </main>
</template>

<style scoped>
.register-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 1.25rem;
  background:
    radial-gradient(circle at top left, rgba(251, 191, 36, 0.24), transparent 28%),
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 30%),
    linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
  position: relative;
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

.register-card {
  width: min(100%, 980px);
  display: grid;
  grid-template-columns: 1fr 1fr;
  overflow: hidden;
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.82);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
}

.register-copy {
  padding: 2.5rem;
  color: #0f172a;
  background: linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(29, 78, 216, 0.92));
}

.register-copy h1 {
  margin: 0.8rem 0 1rem;
  font-size: clamp(2rem, 3vw, 3.2rem);
  line-height: 1.02;
  color: #fff;
}

.register-copy p {
  color: rgba(241, 245, 249, 0.84);
  line-height: 1.7;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.74rem;
  font-weight: 700;
  color: #fbbf24;
}

.register-form {
  padding: 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.form-group label {
  font-weight: 600;
  color: #334155;
}

input {
  padding: 0.85rem 0.95rem;
  border-radius: 12px;
  border: 1px solid #dbe3ef;
  font: inherit;
}

input:focus {
  outline: none;
  border-color: #d97706;
  box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.18);
}

.input-error {
  border-color: #ef4444;
}

.error-alert {
  padding: 0.75rem 1rem;
  border-radius: 12px;
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.error-text {
  color: #ef4444;
  font-size: 0.8rem;
}

.submit-button {
  margin-top: 0.3rem;
  border: 0;
  border-radius: 12px;
  padding: 0.9rem 1.25rem;
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.helper-link {
  margin: 0.3rem 0 0;
  color: #475569;
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

@media (max-width: 880px) {
  .register-card {
    grid-template-columns: 1fr;
  }
}
</style>
