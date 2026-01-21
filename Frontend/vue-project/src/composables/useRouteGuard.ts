import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

/**
 * Composable for route protection and authentication guards
 * Provides utilities for checking authentication status and handling redirects
 */
export function useRouteGuard() {
  const authStore = useAuthStore()
  const router = useRouter()

  /**
   * Check if user is authenticated and session is valid
   * @returns Promise<boolean> - true if authenticated and session valid
   */
  const checkAuthentication = async (): Promise<boolean> => {
    // First check if user appears to be authenticated
    if (!authStore.isAuthenticated) {
      return false
    }

    // Validate session with server
    try {
      const isValid = await authStore.validateSession()
      return isValid
    } catch (error) {
      console.error('Session validation failed:', error)
      return false
    }
  }

  /**
   * Redirect to login with optional message and return URL
   * @param returnUrl - URL to redirect to after successful login
   * @param message - Message to display on login page
   */
  const redirectToLogin = (returnUrl?: string, message?: string) => {
    const query: Record<string, string> = {}
    
    if (returnUrl) {
      query.redirect = returnUrl
    }
    
    if (message) {
      query.message = message
    }

    router.push({
      path: '/login',
      query: Object.keys(query).length > 0 ? query : undefined
    })
  }

  /**
   * Handle session expiration
   * @param currentPath - Current route path to return to after re-authentication
   */
  const handleSessionExpiration = async (currentPath?: string) => {
    await authStore.logout()
    redirectToLogin(
      currentPath, 
      'Your session has expired. Please login again.'
    )
  }

  /**
   * Require authentication for current route
   * Redirects to login if not authenticated
   * @param currentPath - Current route path
   * @returns Promise<boolean> - true if authenticated, false if redirected
   */
  const requireAuth = async (currentPath: string): Promise<boolean> => {
    const isAuthenticated = await checkAuthentication()
    
    if (!isAuthenticated) {
      redirectToLogin(currentPath, 'Please login to continue')
      return false
    }
    
    return true
  }

  /**
   * Require guest access (unauthenticated)
   * Redirects to profile if already authenticated
   * @param redirectPath - Path to redirect to if authenticated (default: /profile)
   * @returns boolean - true if guest, false if redirected
   */
  const requireGuest = (redirectPath: string = '/profile'): boolean => {
    if (authStore.isAuthenticated) {
      router.push(redirectPath)
      return false
    }
    
    return true
  }

  /**
   * Initialize authentication state
   * Should be called on app startup
   */
  const initializeAuth = async (): Promise<void> => {
    try {
      await authStore.initializeAuth()
    } catch (error) {
      console.error('Failed to initialize authentication:', error)
    }
  }

  return {
    checkAuthentication,
    redirectToLogin,
    handleSessionExpiration,
    requireAuth,
    requireGuest,
    initializeAuth
  }
}