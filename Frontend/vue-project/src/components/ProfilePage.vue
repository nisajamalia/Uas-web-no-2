<template>
  <div class="profile-page">
    <div class="profile-container">
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="header-content">
          <div class="profile-title">
            <div class="title-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div>
              <h1>User Profile</h1>
              <p>Manage your account information and settings</p>
            </div>
          </div>
          <button 
            @click="handleLogout" 
            :disabled="loading"
            class="logout-button"
            type="button"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9" stroke="currentColor" stroke-width="2"/>
              <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2"/>
              <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2"/>
            </svg>
            {{ loading ? 'Logging out...' : 'Logout' }}
          </button>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading && !user" class="loading-state">
        <div class="loading-spinner">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M21 12A9 9 0 1 1 12 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <p>Loading your profile...</p>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="error-state">
        <div class="error-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>
        <h3>Unable to load profile</h3>
        <p>{{ error }}</p>
        <button @click="refreshProfile" class="retry-button">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <polyline points="23,4 23,10 17,10" stroke="currentColor" stroke-width="2"/>
            <path d="M20.49 15A9 9 0 1 1 5.64 5.64L23 10" stroke="currentColor" stroke-width="2"/>
          </svg>
          Try Again
        </button>
      </div>

      <!-- Profile data display -->
      <div v-else-if="user" class="profile-content">
        <!-- User Avatar Section -->
        <div class="avatar-section">
          <div class="user-avatar">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
              <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
              <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>
          <div class="user-info">
            <h2>{{ user.name }}</h2>
            <p class="user-email">{{ user.email }}</p>
            <div class="verification-badge" :class="{ 'verified': user.email_verified_at }">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <template v-if="user.email_verified_at">
                  <path d="M22 11.08V12A10 10 0 1 1 5.93 7.25" stroke="currentColor" stroke-width="2"/>
                  <polyline points="22,4 12,14.01 9,11.01" stroke="currentColor" stroke-width="2"/>
                </template>
                <template v-else>
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                  <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                  <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
                </template>
              </svg>
              {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
            </div>
          </div>
        </div>

        <!-- Profile Information Cards -->
        <div class="info-grid">
          <div class="info-card">
            <div class="card-header">
              <div class="card-icon card-icon--primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                  <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <h3>Personal Information</h3>
            </div>
            <div class="card-content">
              <div class="info-field">
                <label>Full Name</label>
                <span class="field-value">{{ user.name }}</span>
              </div>
              <div class="info-field">
                <label>Email Address</label>
                <span class="field-value">{{ user.email }}</span>
              </div>
              <div class="info-field">
                <label>Email Status</label>
                <span class="field-value">
                  <span class="status-badge" :class="{ 'status-verified': user.email_verified_at }">
                    {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                  </span>
                </span>
              </div>
            </div>
          </div>

          <div class="info-card">
            <div class="card-header">
              <div class="card-icon card-icon--info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                  <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <h3>Account Information</h3>
            </div>
            <div class="card-content">
              <div class="info-field">
                <label>Member Since</label>
                <span class="field-value">{{ formatDate(user.created_at) }}</span>
              </div>
              <div class="info-field">
                <label>Last Updated</label>
                <span class="field-value">{{ formatDate(user.updated_at) }}</span>
              </div>
              <div class="info-field">
                <label>Account Status</label>
                <span class="field-value">
                  <span class="status-badge status-active">Active</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="profile-actions">
          <button @click="refreshProfile" :disabled="loading" class="action-button action-button--primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <polyline points="23,4 23,10 17,10" stroke="currentColor" stroke-width="2"/>
              <path d="M20.49 15A9 9 0 1 1 5.64 5.64L23 10" stroke="currentColor" stroke-width="2"/>
            </svg>
            {{ loading ? 'Refreshing...' : 'Refresh Profile' }}
          </button>
          
          <router-link to="/dashboard" class="action-button action-button--secondary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M3 9L12 2L21 9V20A2 2 0 0 1 19 22H5A2 2 0 0 1 3 20V9Z" stroke="currentColor" stroke-width="2"/>
              <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>
            </svg>
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <!-- No user data state -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M16 16S14 14 12 14 8 16 8 16" stroke="currentColor" stroke-width="2"/>
            <line x1="9" y1="9" x2="9.01" y2="9" stroke="currentColor" stroke-width="2"/>
            <line x1="15" y1="9" x2="15.01" y2="9" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>
        <h3>No Profile Data</h3>
        <p>We couldn't find your profile information.</p>
        <button @click="refreshProfile" class="retry-button">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <polyline points="23,4 23,10 17,10" stroke="currentColor" stroke-width="2"/>
            <path d="M20.49 15A9 9 0 1 1 5.64 5.64L23 10" stroke="currentColor" stroke-width="2"/>
          </svg>
          Load Profile
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Computed properties for reactive data
const user = computed(() => authStore.user)
const loading = computed(() => authStore.loading)
const error = computed(() => authStore.error)

// Format date for display
const formatDate = (dateString: string): string => {
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (err) {
    return 'Invalid date'
  }
}

// Refresh profile data from API
const refreshProfile = async (): Promise<void> => {
  try {
    authStore.clearError()
    await authStore.fetchProfile()
  } catch (err) {
    console.error('Failed to refresh profile:', err)
    // Error is already handled by the store
  }
}

// Handle logout functionality
const handleLogout = async (): Promise<void> => {
  try {
    await authStore.logout()
    // Redirect to login page after successful logout
    router.push('/login')
  } catch (err) {
    console.error('Logout failed:', err)
    // Even if logout API fails, we still redirect to login
    // as the local state has been cleared
    router.push('/login')
  }
}

// Fetch profile data on component mount
onMounted(async () => {
  // Only fetch profile if we don't have user data
  if (!user.value && authStore.isAuthenticated) {
    await refreshProfile()
  }
})
</script>

<style scoped>
.profile-page {
  min-height: calc(100vh - 200px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem;
}

.profile-container {
  max-width: 1000px;
  margin: 0 auto;
}

/* Profile Header */
.profile-header {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
  overflow: hidden;
}

.header-content {
  padding: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.profile-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.title-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.profile-title h1 {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
  font-size: 1.75rem;
  font-weight: 700;
}

.profile-title p {
  margin: 0;
  color: #6b7280;
  font-size: 1rem;
}

.logout-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-button:hover:not(:disabled) {
  background-color: #dc2626;
  transform: translateY(-1px);
}

.logout-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Loading State */
.loading-state {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 4rem 2rem;
  text-align: center;
}

.loading-spinner {
  display: inline-block;
  color: #667eea;
  margin-bottom: 1rem;
}

.loading-spinner svg {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.loading-state p {
  margin: 0;
  color: #6b7280;
  font-size: 1.125rem;
}

/* Error State */
.error-state {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 4rem 2rem;
  text-align: center;
}

.error-icon {
  color: #ef4444;
  margin-bottom: 1.5rem;
}

.error-state h3 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 700;
}

.error-state p {
  margin: 0 0 2rem 0;
  color: #6b7280;
  font-size: 1rem;
}

/* Empty State */
.empty-state {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  color: #9ca3af;
  margin-bottom: 1.5rem;
}

.empty-state h3 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 700;
}

.empty-state p {
  margin: 0 0 2rem 0;
  color: #6b7280;
  font-size: 1rem;
}

/* Profile Content */
.profile-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Avatar Section */
.avatar-section {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  display: flex;
  align-items: center;
  gap: 2rem;
}

.user-avatar {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.user-info h2 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 2rem;
  font-weight: 700;
}

.user-email {
  margin: 0 0 1rem 0;
  color: #6b7280;
  font-size: 1.125rem;
}

.verification-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
}

.verification-badge.verified {
  background-color: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.verification-badge:not(.verified) {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.info-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 2rem;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.card-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon--primary {
  background-color: #eff6ff;
  color: #2563eb;
}

.card-icon--info {
  background-color: #fef3c7;
  color: #d97706;
}

.card-header h3 {
  margin: 0;
  color: #1f2937;
  font-size: 1.125rem;
  font-weight: 600;
}

.card-content {
  padding: 2rem;
}

.info-field {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.info-field:last-child {
  border-bottom: none;
}

.info-field label {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.field-value {
  color: #1f2937;
  font-weight: 500;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-verified {
  background-color: #f0fdf4;
  color: #16a34a;
}

.status-active {
  background-color: #eff6ff;
  color: #2563eb;
}

/* Action Buttons */
.profile-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.action-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  font-size: 0.875rem;
}

.action-button--primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-button--primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.action-button--secondary {
  background-color: #f9fafb;
  color: #374151;
  border: 2px solid #e5e7eb;
}

.action-button--secondary:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
  transform: translateY(-1px);
}

.action-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.retry-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.retry-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
  .profile-page {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1.5rem;
    text-align: center;
  }
  
  .profile-title {
    flex-direction: column;
    text-align: center;
  }
  
  .avatar-section {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }
  
  .user-avatar {
    width: 100px;
    height: 100px;
  }
  
  .user-info h2 {
    font-size: 1.75rem;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .info-field {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .profile-actions {
    flex-direction: column;
  }
  
  .card-header {
    padding: 1rem 1.5rem;
  }
  
  .card-content {
    padding: 1.5rem;
  }
}

@media (max-width: 480px) {
  .title-icon {
    width: 56px;
    height: 56px;
  }
  
  .user-avatar {
    width: 80px;
    height: 80px;
  }
  
  .user-info h2 {
    font-size: 1.5rem;
  }
  
  .profile-title h1 {
    font-size: 1.5rem;
  }
}
</style>