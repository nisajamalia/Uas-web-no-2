<template>
  <Transition name="toast" appear>
    <div v-if="show" :class="['toast', `toast-${type}`]">
      <div class="toast-icon">
        <svg v-if="type === 'success'" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="type === 'error'" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M13 16H12V12H11M12 8H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="toast-content">
        <div class="toast-title">{{ title }}</div>
        <div v-if="message" class="toast-message">{{ message }}</div>
      </div>
      <button @click="close" class="toast-close">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Props {
  type: 'success' | 'error' | 'info'
  title: string
  message?: string
  duration?: number
}

interface Emits {
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  duration: 5000
})

const emit = defineEmits<Emits>()

const show = ref(false)

const close = () => {
  show.value = false
  setTimeout(() => emit('close'), 300) // Wait for transition
}

onMounted(() => {
  show.value = true
  
  if (props.duration > 0) {
    setTimeout(() => {
      close()
    }, props.duration)
  }
})
</script>

<style scoped>
.toast {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  max-width: 400px;
  min-width: 300px;
}

.toast-success {
  background-color: #f0f9ff;
  border: 1px solid #bae6fd;
  color: #0c4a6e;
}

.toast-error {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.toast-info {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #475569;
}

.toast-icon {
  flex-shrink: 0;
  margin-top: 2px;
}

.toast-success .toast-icon {
  color: #059669;
}

.toast-error .toast-icon {
  color: #dc2626;
}

.toast-info .toast-icon {
  color: #3b82f6;
}

.toast-content {
  flex: 1;
}

.toast-title {
  font-weight: 600;
  font-size: 14px;
  line-height: 1.4;
}

.toast-message {
  font-size: 13px;
  margin-top: 4px;
  opacity: 0.8;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  opacity: 0.6;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.toast-close:hover {
  opacity: 1;
}

/* Transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>