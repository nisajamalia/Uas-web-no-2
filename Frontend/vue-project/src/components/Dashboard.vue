<template>
  <div class="dashboard-page">
    <div class="dashboard-container">
      <!-- Dashboard Header -->
      <div class="dashboard-header">
        <div class="header-content">
          <div class="welcome-section">
            <div class="avatar">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="welcome-text">
              <h1>Welcome back, {{ authStore.user?.name }}!</h1>
              <p>Here's what's happening with your account today.</p>
            </div>
          </div>
          <button @click="logout" class="logout-button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9" stroke="currentColor" stroke-width="2"/>
              <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2"/>
              <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2"/>
            </svg>
            Logout
          </button>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div class="dashboard-content">
        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon stat-icon--primary">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="stat-content">
              <h3>Profile Status</h3>
              <p class="stat-value">Active</p>
              <p class="stat-description">Your account is active and verified</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon stat-icon--success">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M22 11.08V12A10 10 0 1 1 5.93 7.25" stroke="currentColor" stroke-width="2"/>
                <polyline points="22,4 12,14.01 9,11.01" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="stat-content">
              <h3>Security</h3>
              <p class="stat-value">Secure</p>
              <p class="stat-description">All security checks passed</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon stat-icon--info">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="stat-content">
              <h3>Last Login</h3>
              <p class="stat-value">Today</p>
              <p class="stat-description">{{ new Date().toLocaleDateString() }}</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <h2>Quick Actions</h2>
          <div class="actions-grid">
            <router-link to="/profile" class="action-card">
              <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                  <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <div class="action-content">
                <h3>View Profile</h3>
                <p>Manage your account information and settings</p>
              </div>
              <div class="action-arrow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <polyline points="9,18 15,12 9,6" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
            </router-link>

            <div class="action-card action-card--disabled">
              <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                  <circle cx="12" cy="16" r="1" stroke="currentColor" stroke-width="2"/>
                  <path d="M7 11V7A5 5 0 0 1 17 7V11" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <div class="action-content">
                <h3>Security Settings</h3>
                <p>Update password and security preferences</p>
                <span class="coming-soon">Coming Soon</span>
              </div>
            </div>

            <div class="action-card action-card--disabled">
              <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"/>
                  <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"/>
                  <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <div class="action-content">
                <h3>System Preferences</h3>
                <p>Customize your SAKTI Mini experience</p>
                <span class="coming-soon">Coming Soon</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
          <h2>Recent Activity</h2>
          <div class="activity-list">
            <div class="activity-item">
              <div class="activity-icon activity-icon--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M15 3H6A3 3 0 0 0 3 6V18A3 3 0 0 0 6 21H15" stroke="currentColor" stroke-width="2"/>
                  <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2"/>
                  <path d="M15 12H3" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <div class="activity-content">
                <p class="activity-title">Successfully logged in</p>
                <p class="activity-time">{{ new Date().toLocaleString() }}</p>
              </div>
            </div>

            <div class="activity-item">
              <div class="activity-icon activity-icon--info">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M20 21V19A4 4 0 0 0 16 15H8A4 4 0 0 0 4 19V21" stroke="currentColor" stroke-width="2"/>
                  <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <div class="activity-content">
                <p class="activity-title">Account accessed</p>
                <p class="activity-time">Welcome to SAKTI Mini</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>

<style scoped>
.dashboard-page {
  min-height: calc(100vh - 200px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

/* Dashboard Header */
.dashboard-header {
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

.welcome-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.avatar {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.welcome-text h1 {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
  font-size: 1.75rem;
  font-weight: 700;
}

.welcome-text p {
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

.logout-button:hover {
  background-color: #dc2626;
  transform: translateY(-1px);
}

/* Dashboard Content */
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon--primary {
  background-color: #eff6ff;
  color: #2563eb;
}

.stat-icon--success {
  background-color: #f0fdf4;
  color: #16a34a;
}

.stat-icon--info {
  background-color: #fef3c7;
  color: #d97706;
}

.stat-content h3 {
  margin: 0 0 0.5rem 0;
  color: #374151;
  font-size: 1rem;
  font-weight: 600;
}

.stat-value {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-description {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

/* Quick Actions */
.quick-actions h2 {
  margin: 0 0 1.5rem 0;
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 700;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
}

.action-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.action-card:hover:not(.action-card--disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border-color: #667eea;
}

.action-card--disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.action-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.action-content {
  flex: 1;
}

.action-content h3 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 1.125rem;
  font-weight: 600;
}

.action-content p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

.coming-soon {
  display: inline-block;
  margin-top: 0.5rem;
  padding: 0.25rem 0.75rem;
  background-color: #fef3c7;
  color: #d97706;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.action-arrow {
  color: #9ca3af;
  flex-shrink: 0;
}

/* Recent Activity */
.recent-activity h2 {
  margin: 0 0 1.5rem 0;
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 700;
}

.activity-list {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-icon--success {
  background-color: #f0fdf4;
  color: #16a34a;
}

.activity-icon--info {
  background-color: #eff6ff;
  color: #2563eb;
}

.activity-content {
  flex: 1;
}

.activity-title {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
  font-weight: 600;
  font-size: 0.875rem;
}

.activity-time {
  margin: 0;
  color: #6b7280;
  font-size: 0.75rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1.5rem;
    text-align: center;
  }
  
  .welcome-section {
    flex-direction: column;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .action-card {
    padding: 1.5rem;
  }
  
  .stat-card {
    padding: 1.5rem;
  }
  
  .activity-item {
    padding: 1rem 1.5rem;
  }
}

@media (max-width: 480px) {
  .welcome-text h1 {
    font-size: 1.5rem;
  }
  
  .avatar {
    width: 56px;
    height: 56px;
  }
  
  .stat-card,
  .action-card {
    padding: 1rem;
  }
  
  .activity-item {
    padding: 1rem;
  }
}
</style>