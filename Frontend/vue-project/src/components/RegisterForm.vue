<template>
  <div class="register-page">
    <div class="register-container">
      <div class="register-card">
        <div class="register-header">
          <div class="register-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M16 21V19A4 4 0 0 0 12 15H5A4 4 0 0 0 1 19V21" stroke="currentColor" stroke-width="2"/>
              <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2"/>
              <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>
          <h2>Create Account</h2>
          <p class="register-subtitle">Join SAKTI Mini and get started today</p>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <!-- Name Field -->
          <div class="form-group">
            <label for="name" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              </svg>
              Full Name
            </label>
            <div class="input-wrapper">
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': errors.name }"
                placeholder="Enter your full name"
                required
                :disabled="isLoading"
              />
            </div>
            <div v-if="errors.name" class="form-error">
              <svg class="error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ errors.name[0] }}
            </div>
          </div>

          <!-- Email Field -->
          <div class="form-group">
            <label for="email" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2"/>
                <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
              </svg>
              Email Address
            </label>
            <div class="input-wrapper">
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                :class="{ 'form-input--error': errors.email }"
                placeholder="Enter your email address"
                required
                :disabled="isLoading"
              />
            </div>
            <div v-if="errors.email" class="form-error">
              <svg class="error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ errors.email[0] }}
            </div>
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label for="password" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="16" r="1" stroke="currentColor" stroke-width="2"/>
                <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="currentColor" stroke-width="2"/>
              </svg>
              Password
            </label>
            <div class="input-wrapper">
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-input"
                :class="{ 'form-input--error': errors.password }"
                placeholder="Create a strong password"
                required
                :disabled="isLoading"
              />
            </div>
            <div v-if="errors.password" class="form-error">
              <svg class="error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ errors.password[0] }}
            </div>
          </div>

          <!-- Password Confirmation Field -->
          <div class="form-group">
            <label for="password_confirmation" class="form-label">
              <svg class="label-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                <path d="M9 16L11 18L15 14" stroke="currentColor" stroke-width="2"/>
                <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="currentColor" stroke-width="2"/>
              </svg>
              Confirm Password
            </label>
            <div class="input-wrapper">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="form-input"
                :class="{ 'form-input--error': errors.password_confirmation }"
                placeholder="Confirm your password"
                required
                :disabled="isLoading"
              />
            </div>
            <div v-if="errors.password_confirmation" class="form-error">
              <svg class="error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ errors.password_confirmation[0] }}
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="isLoading" 
            class="form-submit"
            :class="{ 'form-submit--loading': isLoading }"
          >
            <svg v-if="isLoading" class="loading-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M21 12A9 9 0 1 1 12 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg v-else class="submit-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M16 21V19A4 4 0 0 0 12 15H5A4 4 0 0 0 1 19V21" stroke="currentColor" stroke-width="2"/>
              <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2"/>
              <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>{{ isLoading ? 'Creating Account...' : 'Create Account' }}</span>
          </button>

          <!-- Message Display -->
          <div v-if="message" class="alert" :class="messageType === 'success' ? 'alert-success' : 'alert-error'">
            <svg v-if="messageType === 'success'" class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M22 11.08V12A10 10 0 1 1 5.93 7.25" stroke="currentColor" stroke-width="2"/>
              <polyline points="22,4 12,14.01 9,11.01" stroke="currentColor" stroke-width="2"/>
            </svg>
            <svg v-else class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
              <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
            </svg>
            {{ message }}
          </div>
        </form>

        <div class="form-footer">
          <div class="divider">
            <span>Already have an account?</span>
          </div>
          <router-link to="/login" class="login-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M15 3H6A3 3 0 0 0 3 6V18A3 3 0 0 0 6 21H15" stroke="currentColor" stroke-width="2"/>
              <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2"/>
              <path d="M15 12H3" stroke="currentColor" stroke-width="2"/>
            </svg>
            Sign in to your account
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import type { RegisterData } from '../types'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive<RegisterData>({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const isLoading = ref(false)
const message = ref('')
const messageType = ref<'success' | 'error'>('success')
const errors = ref<Record<string, string[]>>({})

const handleRegister = async () => {
  if (isLoading.value) return

  // Debug logging
  console.log('Form data before submit:', form)

  // Reset previous errors and messages
  errors.value = {}
  message.value = ''
  isLoading.value = true

  try {
    await authStore.register(form)
    message.value = 'Registrasi berhasil! Mengalihkan ke dashboard...'
    messageType.value = 'success'
    
    // Redirect to dashboard after successful registration
    setTimeout(() => {
      router.push('/dashboard')
    }, 1500)
  } catch (error: any) {
    console.error('Registration error:', error)
    messageType.value = 'error'
    
    if (error.response?.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
      message.value = 'Mohon periksa data yang dimasukkan'
    } else if (error.response?.data?.message) {
      message.value = error.response.data.message
    } else {
      message.value = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.register-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.register-container {
  width: 100%;
  max-width: 520px;
}

.register-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  padding: 3rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.register-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.register-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  color: white;
  margin-bottom: 1.5rem;
}

.register-header h2 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -0.025em;
}

.register-subtitle {
  margin: 0;
  color: #6b7280;
  font-size: 1rem;
  font-weight: 400;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.label-icon {
  color: #6b7280;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 1rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.2s ease;
  background-color: #ffffff;
  color: #1f2937;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-input--error {
  border-color: #ef4444;
}

.form-input--error:focus {
  border-color: #ef4444;
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.form-input:disabled {
  background-color: #f9fafb;
  cursor: not-allowed;
  opacity: 0.7;
}

.form-error {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #ef4444;
  font-size: 0.875rem;
  font-weight: 500;
}

.error-icon {
  flex-shrink: 0;
}

.form-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
  min-height: 56px;
}

.form-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.form-submit:active:not(:disabled) {
  transform: translateY(0);
}

.form-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.form-submit--loading {
  background: #6b7280;
}

.loading-spinner {
  animation: spin 1s linear infinite;
}

.submit-icon {
  flex-shrink: 0;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 500;
}

.alert-success {
  background-color: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.alert-error {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
}

.alert-icon {
  flex-shrink: 0;
}

.form-footer {
  margin-top: 2rem;
}

.divider {
  text-align: center;
  margin-bottom: 1.5rem;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background-color: #e5e7eb;
}

.divider span {
  background-color: white;
  padding: 0 1rem;
  color: #6b7280;
  font-size: 0.875rem;
}

.login-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  background-color: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.2s ease;
}

.login-link:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
  transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 640px) {
  .register-page {
    padding: 1rem;
    min-height: calc(100vh - 160px);
  }
  
  .register-card {
    padding: 2rem;
  }
  
  .register-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
  }
  
  .register-header h2 {
    font-size: 1.75rem;
  }
  
  .form-input {
    padding: 0.875rem 1rem;
  }
  
  .form-submit {
    padding: 0.875rem 1.25rem;
    min-height: 52px;
  }
}

@media (max-width: 480px) {
  .register-card {
    padding: 1.5rem;
  }
  
  .register-header h2 {
    font-size: 1.5rem;
  }
  
  .register-icon {
    width: 56px;
    height: 56px;
  }
}
</style>