<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()

const icons: Record<string, string> = {
  success: '✓',
  error:   '✕',
  warning: '⚠',
  info:    'ℹ',
}
</script>

<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="['toast', `toast-${toast.type}`]"
        >
          <span class="toast-icon">{{ icons[toast.type] }}</span>
          <span class="toast-message">{{ toast.message }}</span>
          <button class="toast-close" @click="dismiss(toast.id)">✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-container {
  position: fixed;
  top: 1.25rem;
  right: 1.25rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  min-width: 280px;
  max-width: 380px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  font-size: 0.9rem;
  font-weight: 500;
  pointer-events: all;
  border-left: 4px solid transparent;
}

.toast-success {
  background: #f0fff4;
  color: #22543d;
  border-left-color: #48bb78;
}
.toast-error {
  background: #fff5f5;
  color: #822727;
  border-left-color: #e53e3e;
}
.toast-warning {
  background: #fffbeb;
  color: #744210;
  border-left-color: #ecc94b;
}
.toast-info {
  background: #ebf4ff;
  color: #3730a3;
  border-left-color: #667eea;
}

.toast-icon {
  font-size: 1rem;
  font-weight: 700;
  flex-shrink: 0;
  width: 20px;
  text-align: center;
}

.toast-message {
  flex: 1;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.75rem;
  opacity: 0.5;
  padding: 0 0.15rem;
  flex-shrink: 0;
  color: inherit;
}
.toast-close:hover {
  opacity: 1;
}

/* Animaciones */
.toast-enter-active {
  transition: all 0.3s ease;
}
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(40px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(40px);
}
</style>
