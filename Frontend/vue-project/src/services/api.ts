import axios, { type AxiosInstance, type AxiosResponse } from 'axios'
import type { LoginCredentials, RegisterData, AuthResponse, User, ApiResponse } from '@/types'

class ApiService {
  private api: AxiosInstance

  constructor() {
    this.api = axios.create({
      baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      (config) => {
        // In development, get token from localStorage
        // In production, token would be sent via HttpOnly cookie automatically
        if (import.meta.env.DEV) {
          const token = localStorage.getItem('auth_token')
          if (token) {
            config.headers.Authorization = `Bearer ${token}`
          }
        }
        return config
      },
      (error) => {
        return Promise.reject(error)
      }
    )

    // Response interceptor for error handling
    this.api.interceptors.response.use(
      (response: AxiosResponse) => {
        return response
      },
      (error) => {
        if (error.response?.status === 401) {
          // Token expired or invalid, clear auth state
          if (import.meta.env.DEV) {
            localStorage.removeItem('auth_token')
          }
          
          // Emit session expiration event for global handling
          window.dispatchEvent(new CustomEvent('auth:session-expired'))
          
          // Don't redirect here, let the global handler manage it
          // to avoid conflicts with router navigation guards
        }
        
        // Handle rate limiting (429 status)
        if (error.response?.status === 429) {
          const retryAfter = error.response.headers['retry-after']
          const message = `Too many requests. Please try again in ${retryAfter || 60} seconds.`
          error.message = message
        }
        
        return Promise.reject(error)
      }
    )
  }

  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      // Validate credentials before sending
      if (!credentials.email || !credentials.password) {
        throw new Error('Email and password are required')
      }

      const response = await this.api.post<AuthResponse>('/login', credentials)
      
      // Validate response structure
      if (!response.data || typeof response.data.success !== 'boolean') {
        throw new Error('Invalid response format from server')
      }
      
      return response.data
    } catch (error: any) {
      if (error.response?.data) {
        // Server returned an error response
        let errorMessage = error.response.data.message || 'Login failed'
        
        // Check if there are specific field errors
        if (error.response.data.errors) {
          const errors = error.response.data.errors
          
          // Priority order: email errors first, then password errors
          if (errors.email && Array.isArray(errors.email) && errors.email.length > 0) {
            errorMessage = errors.email[0]
          } else if (errors.password && Array.isArray(errors.password) && errors.password.length > 0) {
            errorMessage = errors.password[0]
          }
        }
        
        throw new Error(errorMessage)
      } else if (error.message) {
        // Client-side error (validation, network, etc.)
        throw error
      }
      throw new Error('Network error occurred')
    }
  }

  async register(registerData: RegisterData): Promise<AuthResponse> {
    try {
      // Debug logging
      console.log('Sending register data:', registerData)
      
      // Validate register data before sending
      if (!registerData.name || !registerData.email || !registerData.password) {
        throw new Error('Name, email and password are required')
      }

      if (registerData.password !== registerData.password_confirmation) {
        throw new Error('Password confirmation does not match')
      }

      const response = await this.api.post<AuthResponse>('/register', registerData)
      
      // Debug logging
      console.log('Register response:', response.data)
      
      // Validate response structure
      if (!response.data || typeof response.data.success !== 'boolean') {
        throw new Error('Invalid response format from server')
      }
      
      return response.data
    } catch (error: any) {
      // Debug logging
      console.error('Register error:', error)
      console.error('Error response:', error.response?.data)
      
      if (error.response?.data) {
        // Server returned an error response
        throw error // Pass the full error for validation handling
      } else if (error.message) {
        // Client-side error (validation, network, etc.)
        throw error
      }
      throw new Error('Network error occurred')
    }
  }

  async logout(): Promise<void> {
    try {
      await this.api.post('/logout')
    } catch (error: any) {
      // Don't throw error for logout, just log it
      console.error('Logout API error:', error)
    }
  }

  async getProfile(): Promise<User> {
    try {
      const response = await this.api.get<ApiResponse<User>>('/profile')
      if (response.data.success && response.data.data) {
        return response.data.data
      }
      throw new Error(response.data.message || 'Failed to fetch profile')
    } catch (error: any) {
      if (error.response?.data) {
        throw new Error(error.response.data.message || 'Failed to fetch profile')
      }
      throw new Error('Network error occurred')
    }
  }

  // Method to check if current token is valid
  async validateToken(): Promise<boolean> {
    try {
      await this.getProfile()
      return true
    } catch (error) {
      return false
    }
  }

  // Method to set authorization header manually (for cases where token is managed externally)
  setAuthToken(token: string): void {
    this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  // Method to clear authorization header
  clearAuthToken(): void {
    delete this.api.defaults.headers.common['Authorization']
  }
}

export const apiService = new ApiService()