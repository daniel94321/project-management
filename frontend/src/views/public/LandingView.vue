<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

const router = useRouter()
const showAboutModal = ref(false)

const phases = [
  {
    step: '01',
    title: 'Descubrimiento',
    text: 'Se define el alcance, las necesidades y los entregables antes de iniciar.',
  },
  {
    step: '02',
    title: 'Planificación',
    text: 'Se organizan cronogramas, responsables, recursos y criterios de evaluación.',
  },
  {
    step: '03',
    title: 'Ejecución',
    text: 'El equipo avanza con trazabilidad clara sobre tareas, avances y bloqueos.',
  },
  {
    step: '04',
    title: 'Evaluación',
    text: 'Coordinadores, evaluadores y directores siguen el progreso y validan resultados.',
  },
]

const roles = [
  {
    name: 'Administrador',
    text: 'Configura permisos, usuarios y reglas operativas de la plataforma.',
  },
  {
    name: 'Coordinador',
    text: 'Supervisa avances, asigna seguimiento y mantiene alineado al equipo.',
  },
  {
    name: 'Evaluador',
    text: 'Revisa entregables y califica el cumplimiento con criterios definidos.',
  },
  {
    name: 'Director',
    text: 'Consulta el panorama general y toma decisiones estratégicas.',
  },
  {
    name: 'Estudiante',
    text: 'Participa en proyectos, entrega actividades y recibe retroalimentación.',
  },
]

function goToLogin() {
  router.push({ name: 'login', query: { entry: 'landing' } })
}

function goToRegister() {
  router.push({ name: 'register', query: { entry: 'landing' } })
}

function openAboutModal() {
  showAboutModal.value = true
}

function closeAboutModal() {
  showAboutModal.value = false
}
</script>

<template>
  <main class="landing-page">
    <header class="topbar">
      <RouterLink :to="{ name: 'landing' }" class="brand">
        <span class="brand-mark">PM</span>
        <span class="brand-text">Project Management</span>
      </RouterLink>

      <nav class="topbar-actions">
        <RouterLink :to="{ name: 'login', query: { entry: 'landing' } }" class="ghost-btn">
          Iniciar sesión
        </RouterLink>
        <button class="primary-btn topbar-btn" @click="goToRegister">Crear cuenta</button>
      </nav>
    </header>

    <section class="hero">
      <div class="hero-copy">
        <p class="eyebrow">Gestión de proyectos académicos</p>
        <h1>Organiza todo el ciclo de vida del proyecto en una sola plataforma.</h1>
        <p class="lead">
          Diseñada para equipos que necesitan coordinar estudiantes, evaluar avances y
          mantener visibilidad clara desde la planeación hasta la entrega final.
        </p>

        <div class="hero-actions">
          <button class="primary-btn" @click="goToLogin">Iniciar sesión</button>
          <button class="secondary-btn" @click="goToRegister">Crear cuenta</button>
          <button class="secondary-btn" @click="openAboutModal">Quiénes somos</button>
          <a class="secondary-btn" href="#proceso">Ver proceso</a>
        </div>

        <div class="hero-metrics">
          <article>
            <strong>5</strong>
            <span>roles operativos</span>
          </article>
          <article>
            <strong>4</strong>
            <span>fases de gestión</span>
          </article>
          <article>
            <strong>1</strong>
            <span>panel centralizado</span>
          </article>
        </div>
      </div>

      <div class="hero-panel">
        <div class="dashboard-preview">
          <div class="preview-header">
            <span class="dot red"></span>
            <span class="dot amber"></span>
            <span class="dot green"></span>
          </div>
          <div class="preview-body">
            <div class="preview-card accent">
              <span>Proyecto activo</span>
              <strong>Seguimiento en tiempo real</strong>
            </div>
            <div class="preview-grid">
              <div>
                <span>Avance</span>
                <strong>87%</strong>
              </div>
              <div>
                <span>Evaluaciones</span>
                <strong>12</strong>
              </div>
              <div>
                <span>Entregas</span>
                <strong>24</strong>
              </div>
              <div>
                <span>Alertas</span>
                <strong>03</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="proceso" class="section-block">
      <div class="section-heading">
        <p class="eyebrow">Proceso</p>
        <h2>Un flujo claro para administrar cada etapa del proyecto.</h2>
      </div>

      <div class="phase-grid">
        <article v-for="phase in phases" :key="phase.step" class="phase-card">
          <span class="phase-step">{{ phase.step }}</span>
          <h3>{{ phase.title }}</h3>
          <p>{{ phase.text }}</p>
        </article>
      </div>
    </section>

    <section class="section-block roles-section">
      <div class="section-heading compact">
        <p class="eyebrow">Roles</p>
        <h2>Los perfiles que usa la plataforma en esta versión.</h2>
      </div>

      <div class="role-grid">
        <article v-for="role in roles" :key="role.name" class="role-card">
          <h3>{{ role.name }}</h3>
          <p>{{ role.text }}</p>
        </article>
      </div>
    </section>

    <footer class="site-footer">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="brand footer-brandline">
            <span class="brand-mark">PM</span>
            <span class="brand-text">Project Management</span>
          </div>
          <p>
            Plataforma enfocada en la gestión académica de proyectos con seguimiento,
            evaluación y coordinación centralizada.
          </p>
        </div>

        <div>
          <h3>Quiénes somos</h3>
          <p>
            Somos un equipo orientado a construir herramientas para organizar procesos,
            roles y entregables dentro de proyectos educativos.
          </p>
        </div>

        <div>
          <h3>Contacto</h3>
          <p>Correo: soporte@projectmanagement.local</p>
          <p>Horario: Lun - Vie, 8:00 - 18:00</p>
        </div>

        <div>
          <h3>Accesos</h3>
          <RouterLink :to="{ name: 'login', query: { entry: 'landing' } }">Iniciar sesión</RouterLink>
          <RouterLink :to="{ name: 'register', query: { entry: 'landing' } }">Crear cuenta</RouterLink>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 Project Management. Todos los derechos reservados.</p>
        <button class="footer-link" @click="openAboutModal">Más información</button>
      </div>
    </footer>

    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showAboutModal" class="modal-overlay" @click.self="closeAboutModal">
          <section class="about-modal">
            <button class="modal-close" type="button" @click="closeAboutModal">✕</button>

            <p class="eyebrow">Quiénes somos</p>
            <h2>Gestión pensada para proyectos académicos con visión profesional.</h2>
            <p>
              Este espacio centraliza la organización de tareas, responsables, avances y
              evaluaciones para mantener el control del proceso de principio a fin.
            </p>

            <div class="modal-points">
              <article>
                <h3>Enfoque</h3>
                <p>Visibilidad clara para cada rol dentro del proyecto.</p>
              </article>
              <article>
                <h3>Soporte</h3>
                <p>Comunicación directa para resolver dudas y acompañar el proceso.</p>
              </article>
              <article>
                <h3>Resultados</h3>
                <p>Seguimiento ordenado, evaluaciones y entrega de evidencias.</p>
              </article>
            </div>
          </section>
        </div>
      </Transition>
    </Teleport>
  </main>
</template>

<style scoped>
.landing-page {
  min-height: 100vh;
  padding: 1.1rem 1.25rem 1.25rem;
  color: #0f172a;
  background:
    radial-gradient(circle at top left, rgba(251, 191, 36, 0.22), transparent 28%),
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
    linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
}

.topbar {
  max-width: 1180px;
  margin: 0 auto 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(12px);
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  color: #0f172a;
  text-decoration: none;
  font-weight: 700;
}

.brand-mark {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: inline-grid;
  place-items: center;
  color: white;
  background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
}

.brand-text {
  font-size: 0.98rem;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.ghost-btn,
.topbar-btn {
  min-height: 42px;
}

.hero,
.section-block {
  max-width: 1180px;
  margin: 0 auto;
}

.hero {
  min-height: calc(100vh - 2.5rem);
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 2rem;
  align-items: center;
}

.hero-copy {
  padding: 1rem 0;
}

.eyebrow {
  display: inline-flex;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.74rem;
  font-weight: 700;
  color: #b45309;
  margin-bottom: 1rem;
}

.hero-copy h1,
.section-heading h2 {
  font-size: clamp(2.2rem, 4vw, 4.5rem);
  line-height: 1.02;
  letter-spacing: -0.04em;
  margin: 0;
}

.lead {
  margin-top: 1.25rem;
  max-width: 58ch;
  font-size: 1.08rem;
  line-height: 1.7;
  color: #475569;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem;
  margin-top: 1.8rem;
}

.primary-btn,
.secondary-btn,
.ghost-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 0.85rem 1.3rem;
  border-radius: 999px;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.primary-btn {
  border: 0;
  color: white;
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  box-shadow: 0 18px 30px rgba(180, 83, 9, 0.24);
}

.secondary-btn {
  color: #0f172a;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: rgba(255, 255, 255, 0.72);
}

.ghost-btn {
  color: #0f172a;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: rgba(255, 255, 255, 0.6);
  text-decoration: none;
}

.primary-btn:hover,
.secondary-btn:hover {
  transform: translateY(-1px);
}

.hero-metrics {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 2rem;
}

.hero-metrics article,
.phase-card,
.role-card,
.dashboard-preview {
  background: rgba(255, 255, 255, 0.76);
  border: 1px solid rgba(255, 255, 255, 0.85);
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
  backdrop-filter: blur(12px);
}

.hero-metrics article {
  border-radius: 20px;
  padding: 1rem;
}

.hero-metrics strong {
  display: block;
  font-size: 1.9rem;
  line-height: 1;
}

.hero-metrics span {
  display: block;
  margin-top: 0.35rem;
  color: #64748b;
}

.hero-panel {
  display: flex;
  justify-content: center;
}

.dashboard-preview {
  width: min(100%, 520px);
  border-radius: 28px;
  overflow: hidden;
  transform: rotate(1deg);
}

.preview-header {
  display: flex;
  gap: 0.45rem;
  padding: 1rem 1.2rem;
  background: rgba(15, 23, 42, 0.94);
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
}

.red { background: #fb7185; }
.amber { background: #f59e0b; }
.green { background: #34d399; }

.preview-body {
  padding: 1.4rem;
  display: grid;
  gap: 1rem;
  background:
    linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.96) 100%);
}

.preview-card {
  border-radius: 22px;
  padding: 1.25rem;
}

.preview-card span,
.preview-grid span {
  display: block;
  color: #64748b;
  font-size: 0.88rem;
}

.preview-card strong {
  display: block;
  margin-top: 0.35rem;
  font-size: 1.3rem;
}

.preview-card.accent {
  color: white;
  background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
}

.preview-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}

.preview-grid div {
  border-radius: 18px;
  padding: 1rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
}

.preview-grid strong {
  display: block;
  margin-top: 0.4rem;
  font-size: 1.35rem;
}

.section-block {
  padding: 2rem 0 1rem;
}

.section-heading {
  max-width: 680px;
  margin-bottom: 1.25rem;
}

.section-heading.compact {
  margin-bottom: 1rem;
}

.section-heading h2 {
  font-size: clamp(1.8rem, 3vw, 2.9rem);
}

.phase-grid,
.role-grid {
  display: grid;
  gap: 1rem;
}

.phase-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.role-grid {
  grid-template-columns: repeat(5, minmax(0, 1fr));
}

.phase-card,
.role-card {
  border-radius: 24px;
  padding: 1.25rem;
}

.phase-step {
  display: inline-flex;
  font-weight: 800;
  color: #b45309;
  margin-bottom: 0.75rem;
}

.phase-card h3,
.role-card h3 {
  margin: 0 0 0.55rem;
  font-size: 1.1rem;
}

.phase-card p,
.role-card p {
  margin: 0;
  color: #475569;
  line-height: 1.65;
}

@media (max-width: 1080px) {
  .hero,
  .phase-grid,
  .role-grid {
    grid-template-columns: 1fr;
  }

  .hero {
    min-height: auto;
    padding-top: 1rem;
  }

  .dashboard-preview {
    transform: none;
  }
}

@media (max-width: 640px) {
  .landing-page {
    padding: 0.85rem;
  }

  .topbar {
    border-radius: 24px;
    align-items: flex-start;
    flex-direction: column;
  }

  .topbar-actions {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .hero-metrics,
  .preview-grid {
    grid-template-columns: 1fr;
  }

  .site-footer {
    padding: 1.2rem;
  }

  .footer-grid {
    grid-template-columns: 1fr;
  }

  .hero-actions {
    flex-direction: column;
    align-items: stretch;
  }
}

.site-footer {
  max-width: 1180px;
  margin: 2.2rem auto 0;
  padding: 1.5rem 1.5rem 1rem;
  border-radius: 28px 28px 0 0;
  background: rgba(15, 23, 42, 0.96);
  color: #e2e8f0;
  box-shadow: 0 -12px 40px rgba(15, 23, 42, 0.12);
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr 0.7fr;
  gap: 1.2rem;
}

.footer-brand p,
.site-footer p {
  color: rgba(226, 232, 240, 0.82);
  line-height: 1.7;
}

.footer-grid h3 {
  margin-bottom: 0.7rem;
  font-size: 1rem;
  color: #fff;
}

.footer-grid a,
.footer-link {
  display: block;
  width: fit-content;
  margin-top: 0.35rem;
  color: #fbbf24;
  background: transparent;
  border: 0;
  padding: 0;
  font: inherit;
  cursor: pointer;
  text-decoration: none;
}

.footer-bottom {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.footer-brandline {
  margin-bottom: 0.9rem;
}

.about-modal {
  position: relative;
  width: min(92vw, 760px);
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.18);
  padding: 2rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: grid;
  place-items: center;
  background: rgba(15, 23, 42, 0.52);
  padding: 1rem;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 38px;
  height: 38px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
}

.about-modal h2 {
  margin: 0.6rem 0 0.8rem;
  font-size: clamp(1.6rem, 2.4vw, 2.3rem);
  line-height: 1.1;
}

.about-modal p {
  color: #475569;
  line-height: 1.7;
}

.modal-points {
  margin-top: 1.1rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.9rem;
}

.modal-points article {
  padding: 1rem;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.modal-points h3 {
  margin-bottom: 0.45rem;
  font-size: 1rem;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

@media (max-width: 720px) {
  .modal-points,
  .footer-grid {
    grid-template-columns: 1fr;
  }

  .footer-bottom {
    align-items: flex-start;
  }
}
</style>