<script setup lang="ts">
import { onMounted, watch, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRoute, useRouter } from 'vue-router'
import { useRouteGuard } from '@/composables/useRouteGuard'
import NotificationContainer from '@/components/NotificationContainer.vue'

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const { initializeAuth, handleSessionExpiration } = useRouteGuard()

// Initialize authentication on app startup
onMounted(async () => {
  await initializeAuth()
})

// Watch for authentication state changes
watch(
  () => authStore.isAuthenticated,
  (isAuthenticated) => {
    // If user becomes unauthenticated while on a protected route
    if (!isAuthenticated && route.meta.requiresAuth) {
      handleSessionExpiration(route.fullPath)
    }
  }
)

// Handle session expiration events from API interceptors
const handleSessionExpirationEvent = () => {
  if (authStore.isAuthenticated) {
    handleSessionExpiration(route.fullPath)
  }
}

// Handle logout
const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

// Listen for session expiration events
window.addEventListener('auth:session-expired', handleSessionExpirationEvent)

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('auth:session-expired', handleSessionExpirationEvent)
})
</script>

<template>
  <div id="app">
    <div class="app-container">
      <header class="app-header">
        <div class="header-content">
          <div class="brand">
            <h1>SAKTI Mini</h1>
            <span class="brand-subtitle">Student Management System</span>
          </div>
          <nav class="header-nav">
            <router-link to="/mahasiswa" class="nav-item">Mahasiswa</router-link>
            <router-link v-if="authStore.isAuthenticated" to="/dashboard" class="nav-item">Dashboard</router-link>
            <router-link v-if="authStore.isAuthenticated" to="/profile" class="nav-item">Profile</router-link>
            <router-link v-if="!authStore.isAuthenticated" to="/login" class="nav-item">Login</router-link>
            <button v-if="authStore.isAuthenticated" @click="handleLogout" class="logout-btn">Logout</button>
          </nav>
        </div>
      </header>

      <main class="app-main">
        <RouterView />
      </main>

      <footer class="app-footer">
        <div class="footer-content">
          <p>&copy; 2024 SAKTI Mini Student Management System. All rights reserved.</p>
        </div>
      </footer>
    </div>

    <!-- Notification Container -->
    <NotificationContainer />
  </div>
</template>

<style>
/* Global styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  line-height: 1.6;
  color: #1f2937;
  background-color: #f9fafb;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header Styles */
.app-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.brand h1 {
  color: white;
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.025em;
}

.brand-subtitle {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.875rem;
  font-weight: 400;
}

.header-nav {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-item {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.nav-item:hover,
.nav-item.router-link-active {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.logout-btn {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
}

/* Main Content */
.app-main {
  flex: 1;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  padding: 2rem;
}

/* Footer */
.app-footer {
  background-color: #374151;
  color: #d1d5db;
  margin-top: auto;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem 2rem;
  text-align: center;
}

.footer-content p {
  font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    padding: 1rem;
    flex-direction: column;
    gap: 1rem;
  }
  
  .brand {
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
  }
  
  .brand h1 {
    font-size: 1.5rem;
  }
  
  .header-nav {
    gap: 1rem;
  }
  
  .app-main {
    padding: 1rem;
  }
  
  .footer-content {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .header-nav {
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
  }
  
  .nav-item,
  .logout-btn {
    width: 100%;
    text-align: center;
  }
}
</style>
