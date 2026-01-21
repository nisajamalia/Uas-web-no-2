import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import components
import LoginForm from '@/components/LoginForm.vue'
import RegisterForm from '@/components/RegisterForm.vue'
import ProfilePage from '@/components/ProfilePage.vue'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginForm,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterForm,
    meta: { requiresGuest: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: ProfilePage,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/components/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Enhanced navigation guards with session validation
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth state if not already done
  if (!authStore.user && authStore.token) {
    try {
      await authStore.initializeAuth()
    } catch (error) {
      console.error('Auth initialization failed:', error)
    }
  }
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    // First check if user appears to be authenticated
    if (!authStore.isAuthenticated) {
      // Not authenticated, redirect to login
      next({
        path: '/login',
        query: { redirect: to.fullPath } // Save intended destination
      })
      return
    }
    
    // User appears authenticated, validate session
    try {
      const isValidSession = await authStore.validateSession()
      if (!isValidSession) {
        // Session expired or invalid, logout and redirect
        await authStore.logout()
        next({
          path: '/login',
          query: { 
            redirect: to.fullPath,
            message: 'Session expired. Please login again.'
          }
        })
        return
      }
      
      // Session is valid, proceed
      next()
    } catch (error) {
      // Session validation failed, treat as expired
      console.error('Session validation error:', error)
      await authStore.logout()
      next({
        path: '/login',
        query: { 
          redirect: to.fullPath,
          message: 'Session expired. Please login again.'
        }
      })
    }
  } 
  // Check if route requires guest (unauthenticated) access
  else if (to.meta.requiresGuest) {
    if (authStore.isAuthenticated) {
      // Already authenticated, redirect to profile or intended destination
      const redirectPath = from.query.redirect as string || '/profile'
      next(redirectPath)
    } else {
      next()
    }
  } 
  // Public route, no authentication required
  else {
    next()
  }
})

// Global after hook for additional security logging
router.afterEach((to, from) => {
  // Log navigation for security monitoring (in development)
  if (import.meta.env.DEV) {
    console.log(`Navigation: ${from.path} -> ${to.path}`)
  }
})

export default router