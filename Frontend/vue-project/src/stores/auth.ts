import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, LoginCredentials, RegisterData } from '@/types'
import { apiService } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Actions
  const login = async (credentials: LoginCredentials): Promise<void> => {
    try {
      loading.value = true
      error.value = null
      
      const response = await apiService.login(credentials)
      
      if (response.success) {
        user.value = response.data.user
        token.value = response.data.token
        
        // Set token in API service for immediate use
        apiService.setAuthToken(response.data.token)
        
        // Store token securely in memory (preferred for security)
        // For production, consider using HttpOnly cookies set by the server
        // localStorage is used here for development convenience but should be avoided in production
        if (import.meta.env.DEV) {
          localStorage.setItem('auth_token', response.data.token)
        }
        // In production, token should be stored in HttpOnly cookie by the server
      } else {
        throw new Error(response.message)
      }
    } catch (err: any) {
      error.value = err.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (registerData: RegisterData): Promise<void> => {
    try {
      loading.value = true
      error.value = null
      
      const response = await apiService.register(registerData)
      
      if (response.success) {
        user.value = response.data.user
        token.value = response.data.token
        
        // Set token in API service for immediate use
        apiService.setAuthToken(response.data.token)
        
        // Store token securely in memory (preferred for security)
        if (import.meta.env.DEV) {
          localStorage.setItem('auth_token', response.data.token)
        }
      } else {
        throw new Error(response.message)
      }
    } catch (err: any) {
      error.value = err.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async (): Promise<void> => {
    try {
      loading.value = true
      
      if (token.value) {
        await apiService.logout()
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear state regardless of API call success
      user.value = null
      token.value = null
      
      // Clear API service auth token
      apiService.clearAuthToken()
      
      // Clear localStorage in development
      if (import.meta.env.DEV) {
        localStorage.removeItem('auth_token')
      }
      // In production, HttpOnly cookie will be cleared by server
      
      loading.value = false
    }
  }

  const fetchProfile = async (): Promise<void> => {
    try {
      loading.value = true
      error.value = null
      
      const userData = await apiService.getProfile()
      user.value = userData
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch profile'
      throw err
    } finally {
      loading.value = false
    }
  }

  const checkAuth = (): boolean => {
    // In development, check localStorage for token
    if (import.meta.env.DEV) {
      const storedToken = localStorage.getItem('auth_token')
      if (storedToken) {
        token.value = storedToken
        // Set token in API service
        apiService.setAuthToken(storedToken)
        return true
      }
    }
    // In production, token would be available via HttpOnly cookie
    // and validated by the server on each request
    return false
  }

  const clearError = () => {
    error.value = null
  }

  const initializeAuth = async (): Promise<void> => {
    // Check if we have a stored token and try to fetch user profile
    if (checkAuth() && token.value) {
      try {
        await fetchProfile()
      } catch (err) {
        // If profile fetch fails, clear auth state
        user.value = null
        token.value = null
        apiService.clearAuthToken()
        if (import.meta.env.DEV) {
          localStorage.removeItem('auth_token')
        }
      }
    }
  }

  const validateSession = async (): Promise<boolean> => {
    if (!token.value) {
      return false
    }
    
    try {
      const isValid = await apiService.validateToken()
      if (!isValid) {
        // Clear invalid session
        user.value = null
        token.value = null
        apiService.clearAuthToken()
        if (import.meta.env.DEV) {
          localStorage.removeItem('auth_token')
        }
      }
      return isValid
    } catch (err) {
      return false
    }
  }

  return {
    // State
    user,
    token,
    loading,
    error,
    // Getters
    isAuthenticated,
    // Actions
    login,
    register,
    logout,
    fetchProfile,
    checkAuth,
    clearError,
    initializeAuth,
    validateSession
  }
})