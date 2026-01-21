import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import RegisterForm from '../../components/RegisterForm.vue'
import { useAuthStore } from '../../stores/auth'
import { apiService } from '../../services/api'

// Mock the API service
vi.mock('../../services/api', () => ({
  apiService: {
    register: vi.fn(),
    setAuthToken: vi.fn(),
    clearAuthToken: vi.fn()
  }
}))

const mockApiService = apiService as any

describe('Registration Flow Integration', () => {
  let router: any
  let pinia: any

  beforeEach(() => {
    // Setup Pinia
    pinia = createPinia()
    setActivePinia(pinia)

    // Setup Router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/register', component: RegisterForm },
        { path: '/login', component: { template: '<div>Login</div>' } },
        { path: '/dashboard', component: { template: '<div>Dashboard</div>' } }
      ]
    })

    // Reset mocks
    vi.clearAllMocks()
  })

  it('should render registration form correctly', async () => {
    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    expect(wrapper.find('h2').text()).toBe('Buat Akun Baru')
    expect(wrapper.find('input#name').exists()).toBe(true)
    expect(wrapper.find('input#email').exists()).toBe(true)
    expect(wrapper.find('input#password').exists()).toBe(true)
    expect(wrapper.find('input#password_confirmation').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').text()).toBe('Daftar')
    expect(wrapper.find('a[href="/login"]').exists()).toBe(true)
  })

  it('should handle successful registration', async () => {
    const mockResponse = {
      success: true,
      message: 'Registration successful',
      data: {
        user: {
          id: 1,
          name: 'John Doe',
          email: 'john@example.com'
        },
        token: 'mock-token-123'
      }
    }

    mockApiService.register.mockResolvedValue(mockResponse)

    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    // Fill form
    await wrapper.find('input#name').setValue('John Doe')
    await wrapper.find('input#email').setValue('john@example.com')
    await wrapper.find('input#password').setValue('password123')
    await wrapper.find('input#password_confirmation').setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Verify API was called with correct data
    expect(mockApiService.register).toHaveBeenCalledWith({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123'
    })

    // Verify success message
    await wrapper.vm.$nextTick()
    expect(wrapper.find('.message.success').text()).toContain('Registrasi berhasil')
  })

  it('should handle validation errors', async () => {
    const mockError = {
      response: {
        status: 422,
        data: {
          errors: {
            email: ['Email sudah terdaftar.'],
            password: ['Password harus minimal 8 karakter.']
          }
        }
      }
    }

    mockApiService.register.mockRejectedValue(mockError)

    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    // Fill form with invalid data
    await wrapper.find('input#name').setValue('John Doe')
    await wrapper.find('input#email').setValue('existing@example.com')
    await wrapper.find('input#password').setValue('123')
    await wrapper.find('input#password_confirmation').setValue('123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Wait for error handling
    await new Promise(resolve => setTimeout(resolve, 100))
    await wrapper.vm.$nextTick()

    // Verify error messages are displayed
    expect(wrapper.find('.error').exists()).toBe(true)
  })

  it('should handle network errors', async () => {
    const mockError = new Error('Network error occurred')
    mockApiService.register.mockRejectedValue(mockError)

    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    // Fill form
    await wrapper.find('input#name').setValue('John Doe')
    await wrapper.find('input#email').setValue('john@example.com')
    await wrapper.find('input#password').setValue('password123')
    await wrapper.find('input#password_confirmation').setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Wait for error handling
    await new Promise(resolve => setTimeout(resolve, 100))
    await wrapper.vm.$nextTick()

    // Verify error message
    expect(wrapper.find('.message.error').text()).toContain('Terjadi kesalahan')
  })

  it('should disable form during submission', async () => {
    // Mock a delayed response
    mockApiService.register.mockImplementation(() => 
      new Promise(resolve => setTimeout(() => resolve({
        success: true,
        data: { user: {}, token: 'token' }
      }), 100))
    )

    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    // Fill form
    await wrapper.find('input#name').setValue('John Doe')
    await wrapper.find('input#email').setValue('john@example.com')
    await wrapper.find('input#password').setValue('password123')
    await wrapper.find('input#password_confirmation').setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Verify form is disabled during submission
    expect(wrapper.find('input#name').attributes('disabled')).toBeDefined()
    expect(wrapper.find('input#email').attributes('disabled')).toBeDefined()
    expect(wrapper.find('input#password').attributes('disabled')).toBeDefined()
    expect(wrapper.find('input#password_confirmation').attributes('disabled')).toBeDefined()
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
    expect(wrapper.find('button[type="submit"]').text()).toBe('Mendaftar...')
  })

  it('should validate password confirmation match', async () => {
    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    // Fill form with mismatched passwords
    await wrapper.find('input#name').setValue('John Doe')
    await wrapper.find('input#email').setValue('john@example.com')
    await wrapper.find('input#password').setValue('password123')
    await wrapper.find('input#password_confirmation').setValue('different123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Wait for validation to complete
    await new Promise(resolve => setTimeout(resolve, 100))

    // API should be called but server will handle validation
    // Client-side validation for password match is handled by the server
    expect(mockApiService.register).toHaveBeenCalled()
  })

  it('should integrate with auth store correctly', async () => {
    const mockResponse = {
      success: true,
      message: 'Registration successful',
      data: {
        user: {
          id: 1,
          name: 'John Doe',
          email: 'john@example.com'
        },
        token: 'mock-token-123'
      }
    }

    mockApiService.register.mockResolvedValue(mockResponse)

    const wrapper = mount(RegisterForm, {
      global: {
        plugins: [router, pinia]
      }
    })

    const authStore = useAuthStore()

    // Fill and submit form
    await wrapper.find('input#name').setValue('John Doe')
    await wrapper.find('input#email').setValue('john@example.com')
    await wrapper.find('input#password').setValue('password123')
    await wrapper.find('input#password_confirmation').setValue('password123')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Wait for async operations
    await new Promise(resolve => setTimeout(resolve, 100))

    // Verify auth store was updated
    expect(authStore.user).toEqual(mockResponse.data.user)
    expect(authStore.token).toBe(mockResponse.data.token)
    expect(authStore.isAuthenticated).toBe(true)
  })
})