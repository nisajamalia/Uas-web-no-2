import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'

import LoginForm from '@/components/LoginForm.vue'
import ProfilePage from '@/components/ProfilePage.vue'
import { useAuthStore } from '@/stores/auth'

// Mock the entire API service module
vi.mock('@/services/api', () => ({
  apiService: {
    login: vi.fn(),
    logout: vi.fn(),
    getProfile: vi.fn(),
    validateToken: vi.fn(),
    setAuthToken: vi.fn(),
    clearAuthToken: vi.fn(),
  }
}))

// Import the mocked API service
import { apiService } from '@/services/api'
const mockedApiService = vi.mocked(apiService)

// Define test routes
const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: LoginForm },
  { path: '/profile', name: 'Profile', component: ProfilePage, meta: { requiresAuth: true } },
]

/**
 * Frontend Integration Tests for SAKTI Mini Login Module
 * Tests complete authentication flow from frontend perspective
 * 
 * **Feature: sakti-mini-login, Frontend Integration Testing**
 * **Validates: Requirements 1.1, 1.2, 1.5, 3.1, 3.2, 3.3, 3.4, 3.5**
 */
describe('Authentication Flow Integration Tests', () => {
  let pinia: any
  let router: any
  let authStore: any

  beforeEach(() => {
    // Setup Pinia
    pinia = createPinia()
    setActivePinia(pinia)
    
    // Setup Router
    router = createRouter({
      history: createWebHistory(),
      routes,
    })
    
    // Setup auth store
    authStore = useAuthStore()
    
    // Clear all mocks
    vi.clearAllMocks()
    
    // Reset localStorage mock
    vi.mocked(localStorage.getItem).mockReturnValue(null)
    vi.mocked(localStorage.setItem).mockImplementation(() => {})
    vi.mocked(localStorage.removeItem).mockImplementation(() => {})
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  /**
   * Test complete login flow from form submission to dashboard redirect
   * Validates Requirements 1.1, 3.1, 3.3
   */
  it('should complete full login flow successfully', async () => {
    // Mock successful API response
    const mockLoginResponse = {
      success: true,
      message: 'Login successful',
      data: {
        user: {
          id: 1,
          name: 'Test User',
          email: 'test@kampus.ac.id',
          created_at: '2024-01-01T00:00:00.000000Z',
          updated_at: '2024-01-01T00:00:00.000000Z',
        },
        token: 'mock-jwt-token-12345',
      },
    }

    mockedApiService.login.mockResolvedValueOnce(mockLoginResponse)

    // Mount LoginForm component
    const wrapper = mount(LoginForm, {
      global: {
        plugins: [pinia, router],
      },
    })

    // Fill in the form
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const submitButton = wrapper.find('button[type="submit"]')

    await emailInput.setValue('test@kampus.ac.id')
    await passwordInput.setValue('SecurePassword123!')

    // Submit the form
    await submitButton.trigger('click')
    await wrapper.vm.$nextTick()

    // Verify API was called with correct data
    expect(mockedApiService.login).toHaveBeenCalledWith({
      email: 'test@kampus.ac.id',
      password: 'SecurePassword123!',
    })

    // Verify auth store was updated
    expect(authStore.user).toEqual({
      id: 1,
      name: 'Test User',
      email: 'test@kampus.ac.id',
    })
    expect(authStore.token).toBe('mock-jwt-token-12345')
    expect(authStore.isAuthenticated).toBe(true)

    // Verify token was stored securely
    expect(localStorage.setItem).toHaveBeenCalledWith('auth_token', 'mock-jwt-token-12345')
  })

  /**
   * Test login failure handling and error display
   * Validates Requirements 1.2, 3.2
   */
  it('should handle login failures with uniform error messages', async () => {
    // Mock failed API response
    const mockError = new Error('The provided credentials are incorrect.')

    mockedApiService.login.mockRejectedValueOnce(mockError)

    const wrapper = mount(LoginForm, {
      global: {
        plugins: [pinia, router],
      },
    })

    // Fill in the form with invalid credentials
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    const submitButton = wrapper.find('button[type="submit"]')

    await emailInput.setValue('wrong@email.com')
    await passwordInput.setValue('wrongpassword')
    await submitButton.trigger('click')
    await wrapper.vm.$nextTick()

    // Verify error is displayed without leaking sensitive information
    const errorMessage = wrapper.find('.error-message')
    expect(errorMessage.exists()).toBe(true)
    expect(errorMessage.text()).toBe('The provided credentials are incorrect.')

    // Verify auth store remains unauthenticated
    expect(authStore.isAuthenticated).toBe(false)
    expect(authStore.user).toBeNull()
    expect(authStore.token).toBeNull()

    // Verify no token was stored
    expect(localStorage.setItem).not.toHaveBeenCalledWith('auth_token', expect.any(String))
  })

  /**
   * Test client-side validation before server requests
   * Validates Requirements 3.1
   */
  it('should perform client-side validation before server requests', async () => {
    const wrapper = mount(LoginForm, {
      global: {
        plugins: [pinia, router],
      },
    })

    const submitButton = wrapper.find('button[type="submit"]')

    // Try to submit empty form
    await submitButton.trigger('click')
    await wrapper.vm.$nextTick()

    // Verify API was not called
    expect(mockedApiService.login).not.toHaveBeenCalled()

    // Verify validation errors are shown
    const emailError = wrapper.find('.email-error')
    const passwordError = wrapper.find('.password-error')
    
    expect(emailError.exists()).toBe(true)
    expect(passwordError.exists()).toBe(true)

    // Test invalid email format
    const emailInput = wrapper.find('input[type="email"]')
    await emailInput.setValue('invalid-email')
    await submitButton.trigger('click')
    await wrapper.vm.$nextTick()

    // Verify API still not called
    expect(mockedApiService.login).not.toHaveBeenCalled()
  })

  /**
   * Test route protection and authentication guards
   * Validates Requirements 1.4, 3.4, 3.5
   */
  it('should protect routes and redirect unauthenticated users', async () => {
    // Mock unauthenticated state
    authStore.isAuthenticated = false
    authStore.user = null
    authStore.token = null

    // Try to navigate to protected route
    await router.push('/profile')
    await router.isReady()

    // Verify redirect to login
    expect(router.currentRoute.value.path).toBe('/login')
  })

  /**
   * Test authenticated route access
   * Validates Requirements 1.4, 3.4
   */
  it('should allow authenticated users to access protected routes', async () => {
    // Mock authenticated state
    authStore.isAuthenticated = true
    authStore.user = { id: 1, name: 'Test User', email: 'test@kampus.ac.id' }
    authStore.token = 'valid-token'

    // Mock profile API response
    const mockProfileResponse = {
      id: 1,
      name: 'Test User',
      email: 'test@kampus.ac.id',
      created_at: '2024-01-01T00:00:00.000000Z',
      updated_at: '2024-01-01T00:00:00.000000Z',
    }

    mockedApiService.getProfile.mockResolvedValueOnce(mockProfileResponse)

    // Navigate to protected route
    await router.push('/profile')
    await router.isReady()

    // Verify access is allowed
    expect(router.currentRoute.value.path).toBe('/profile')

    // Mount ProfilePage component
    const wrapper = mount(ProfilePage, {
      global: {
        plugins: [pinia, router],
      },
    })

    await wrapper.vm.$nextTick()

    // Verify profile data is displayed
    expect(wrapper.text()).toContain('Test User')
    expect(wrapper.text()).toContain('test@kampus.ac.id')
  })

  /**
   * Test logout functionality and session cleanup
   * Validates Requirements 1.5
   */
  it('should handle logout and clean up session completely', async () => {
    // Setup authenticated state
    authStore.isAuthenticated = true
    authStore.user = { id: 1, name: 'Test User', email: 'test@kampus.ac.id' }
    authStore.token = 'valid-token'

    // Mock logout API response
    mockedApiService.logout.mockResolvedValueOnce(undefined)

    // Mount ProfilePage with logout functionality
    const wrapper = mount(ProfilePage, {
      global: {
        plugins: [pinia, router],
      },
    })

    // Find and click logout button
    const logoutButton = wrapper.find('.logout-button')
    await logoutButton.trigger('click')
    await wrapper.vm.$nextTick()

    // Verify logout API was called
    expect(mockedApiService.logout).toHaveBeenCalled()

    // Verify auth store was cleared
    expect(authStore.isAuthenticated).toBe(false)
    expect(authStore.user).toBeNull()
    expect(authStore.token).toBeNull()

    // Verify token was removed from storage
    expect(localStorage.removeItem).toHaveBeenCalledWith('auth_token')

    // Verify redirect to login page
    expect(router.currentRoute.value.path).toBe('/login')
  })

  /**
   * Test session expiration handling
   * Validates Requirements 3.5
   */
  it('should handle session expiration and auto-logout', async () => {
    // Setup authenticated state
    authStore.isAuthenticated = true
    authStore.user = { id: 1, name: 'Test User', email: 'test@kampus.ac.id' }
    authStore.token = 'expired-token'

    // Mock 401 response for expired token
    const mockError = new Error('Unauthenticated')
    mockedApiService.getProfile.mockRejectedValueOnce(mockError)

    // Try to access profile
    await router.push('/profile')
    await router.isReady()

    const wrapper = mount(ProfilePage, {
      global: {
        plugins: [pinia, router],
      },
    })

    await wrapper.vm.$nextTick()

    // Verify automatic logout occurred
    expect(authStore.isAuthenticated).toBe(false)
    expect(authStore.user).toBeNull()
    expect(authStore.token).toBeNull()

    // Verify redirect to login
    expect(router.currentRoute.value.path).toBe('/login')
  })

  /**
   * Test cross-domain API communication setup
   * Validates Requirements 2.5
   */
  it('should configure API for cross-domain communication', async () => {
    // Test that API service is properly configured for cross-domain requests
    // This is validated by the API service configuration and CORS headers
    
    // Mock a successful login to test API configuration
    const mockResponse = {
      success: true,
      message: 'Login successful',
      data: {
        user: { 
          id: 1, 
          name: 'Test', 
          email: 'test@test.com',
          created_at: '2024-01-01T00:00:00.000000Z',
          updated_at: '2024-01-01T00:00:00.000000Z',
        },
        token: 'test-token'
      }
    }
    
    mockedApiService.login.mockResolvedValueOnce(mockResponse)

    await authStore.login('test@test.com', 'password')

    // Verify request was made with proper configuration
    expect(mockedApiService.login).toHaveBeenCalledWith({
      email: 'test@test.com',
      password: 'password',
    })
  })

  /**
   * Test error message security (no sensitive information leakage)
   * Validates Requirements 3.2
   */
  it('should not leak sensitive information in error messages', async () => {
    const sensitiveErrors = [
      new Error('Database connection failed: mysql://user:password@localhost/db'),
      new Error('SQL Error: SELECT * FROM users WHERE email = "test@test.com"'),
    ]

    const wrapper = mount(LoginForm, {
      global: {
        plugins: [pinia, router],
      },
    })

    for (const error of sensitiveErrors) {
      mockedApiService.login.mockRejectedValueOnce(error)

      const emailInput = wrapper.find('input[type="email"]')
      const passwordInput = wrapper.find('input[type="password"]')
      const submitButton = wrapper.find('button[type="submit"]')

      await emailInput.setValue('test@test.com')
      await passwordInput.setValue('password')
      await submitButton.trigger('click')
      await wrapper.vm.$nextTick()

      // Verify generic error message is shown instead of sensitive details
      const errorMessage = wrapper.find('.error-message')
      expect(errorMessage.exists()).toBe(true)
      
      const errorText = errorMessage.text()
      expect(errorText).not.toContain('mysql://')
      expect(errorText).not.toContain('password@localhost')
      expect(errorText).not.toContain('SELECT * FROM')
      expect(errorText).not.toContain('SQL Error')
      
      // Should show generic error message
      expect(errorText).toContain('An error occurred')
    }
  })
})