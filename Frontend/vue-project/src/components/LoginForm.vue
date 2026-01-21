<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="login-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h2>Welcome Back</h2>
          <p class="login-subtitle">Sign in to your SAKTI Mini account</p>
        </div>

        <!-- Session/Redirect Messages -->
        <div v-if="redirectMessage" class="alert alert-info">
          <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M12 16V12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          {{ redirectMessage }}
        </div>

        <form @submit.prevent="handleSubmit" class="login-form" novalidate>
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
                autocomplete="email"
                @blur="validateField('email')"
                @input="clearFieldError('email')"
              />
            </div>
            <div v-if="errors.email" class="form-error">
              <svg class="error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ errors.email }}
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
                placeholder="Enter your password"
                autocomplete="current-password"
                @blur="validateField('password')"
                @input="clearFieldError('password')"
              />
            </div>
            <div v-if="errors.password" class="form-error">
              <svg class="error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ errors.password }}
            </div>
          </div>

          <!-- General Error Display -->
          <div v-if="generalError" class="alert alert-error">
            <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
              <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
            </svg>
            {{ generalError }}
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            class="form-submit"
            :disabled="isSubmitting || !isFormValid"
            :class="{ 'form-submit--loading': isSubmitting }"
          >
            <svg v-if="isSubmitting" class="loading-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M21 12A9 9 0 1 1 12 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg v-else class="submit-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M15 3H6A3 3 0 0 0 3 6V18A3 3 0 0 0 6 21H15" stroke="currentColor" stroke-width="2"/>
              <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2"/>
              <path d="M15 12H3" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>{{ isSubmitting ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <div class="form-footer">
          <div class="divider">
            <span>Don't have an account?</span>
          </div>
          <router-link to="/register" class="register-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M16 21V19A4 4 0 0 0 12 15H5A4 4 0 0 0 1 19V21" stroke="currentColor" stroke-width="2"/>
              <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2"/>
              <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2"/>
            </svg>
            Create new account
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types'

// Router and store
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Form state
const form = reactive<LoginCredentials>({
  email: '',
  password: ''
})

// Validation errors
const errors = reactive<Record<string, string>>({})

// General error (for server errors)
const generalError = ref<string>('')

// Redirect message (for session expiration, etc.)
const redirectMessage = ref<string>('')

// Loading state
const isSubmitting = ref(false)

// Handle redirect and message parameters
onMounted(() => {
  // Check for redirect message in query params
  if (route.query.message) {
    redirectMessage.value = route.query.message as string
    
    // Clear the message from URL after displaying
    setTimeout(() => {
      router.replace({ query: { ...route.query, message: undefined } })
    }, 100)
  }
})

// Validation rules
interface ValidationRule {
  required: string
  pattern?: RegExp
  patternMessage?: string
  minLength?: number
  minLengthMessage?: string
}

const validationRules: Record<keyof LoginCredentials, ValidationRule> = {
  email: {
    required: 'Email is required',
    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    patternMessage: 'Please enter a valid email address'
  },
  password: {
    required: 'Password is required',
    minLength: 6,
    minLengthMessage: 'Password must be at least 6 characters long'
  }
}

// Computed properties
const isFormValid = computed(() => {
  return form.email.trim() !== '' && 
         form.password.trim() !== '' && 
         Object.keys(errors).length === 0
})

// Validation functions
const validateField = (fieldName: keyof LoginCredentials): boolean => {
  const value = form[fieldName].trim()
  const rules = validationRules[fieldName]

  // Clear previous error
  delete errors[fieldName]

  // Required validation
  if (!value) {
    errors[fieldName] = rules.required
    return false
  }

  // Email pattern validation
  if (fieldName === 'email' && rules.pattern && !rules.pattern.test(value)) {
    errors[fieldName] = rules.patternMessage || 'Invalid email format'
    return false
  }

  // Password length validation
  if (fieldName === 'password' && rules.minLength && value.length < rules.minLength) {
    errors[fieldName] = rules.minLengthMessage || 'Password too short'
    return false
  }

  return true
}

const validateForm = (): boolean => {
  let isValid = true
  
  // Validate all fields
  Object.keys(form).forEach(field => {
    if (!validateField(field as keyof LoginCredentials)) {
      isValid = false
    }
  })

  return isValid
}

const clearFieldError = (fieldName: keyof LoginCredentials): void => {
  if (errors[fieldName]) {
    delete errors[fieldName]
  }
  // Clear general error when user starts typing
  if (generalError.value) {
    generalError.value = ''
  }
  // Clear redirect message when user starts interacting
  if (redirectMessage.value) {
    redirectMessage.value = ''
  }
}

const clearAllErrors = (): void => {
  Object.keys(errors).forEach(key => delete errors[key])
  generalError.value = ''
}

// Form submission
const handleSubmit = async (): Promise<void> => {
  // Clear previous errors
  clearAllErrors()

  // Client-side validation (Requirement 3.1)
  if (!validateForm()) {
    return
  }

  try {
    isSubmitting.value = true

    // Attempt login
    await authStore.login({
      email: form.email.trim(),
      password: form.password.trim()
    })

    // Success - redirect to intended destination or default
    const redirectPath = route.query.redirect as string || '/profile'
    router.push(redirectPath)
  } catch (error: any) {
    // Handle errors securely (Requirement 3.2)
    handleLoginError(error)
  } finally {
    isSubmitting.value = false
  }
}

// Error handling that doesn't leak sensitive information (Requirement 3.2)
const handleLoginError = (error: any): void => {
  // Clear the password field for security
  form.password = ''

  let errorMessage = 'Login failed. Please try again.'

  if (error.message) {
    // Only show safe error messages that don't leak system information
    const safeMessages = [
      'invalid credentials',
      'credentials are incorrect',
      'provided credentials are incorrect',
      'email and password are required',
      'email address is required',
      'password is required',
      'please enter a valid email address',
      'too many requests',
      'network error occurred',
      'login failed',
      'the given data was invalid'
    ]

    // Check if the error message is in our safe list or contains safe patterns
    const isSafeMessage = safeMessages.some(safe => 
      error.message.toLowerCase().includes(safe.toLowerCase())
    ) || error.message.includes('try again in') // For rate limiting messages

    if (isSafeMessage) {
      errorMessage = error.message
    }
  }

  generalError.value = errorMessage
}

// Clear auth store error when component mounts
authStore.clearError()
</script>

<style scoped>
.login-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.login-container {
  width: 100%;
  max-width: 480px;
}

.login-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  padding: 3rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.login-icon {
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

.login-header h2 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -0.025em;
}

.login-subtitle {
  margin: 0;
  color: #6b7280;
  font-size: 1rem;
  font-weight: 400;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  font-size: 0.9rem;
  font-weight: 500;
}

.alert-info {
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1e40af;
}

.alert-error {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
}

.alert-icon {
  flex-shrink: 0;
}

.login-form {
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

.register-link {
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

.register-link:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
  transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 640px) {
  .login-page {
    padding: 1rem;
    min-height: calc(100vh - 160px);
  }
  
  .login-card {
    padding: 2rem;
  }
  
  .login-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
  }
  
  .login-header h2 {
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
  .login-card {
    padding: 1.5rem;
  }
  
  .login-header h2 {
    font-size: 1.5rem;
  }
  
  .login-icon {
    width: 56px;
    height: 56px;
  }
}
</style>