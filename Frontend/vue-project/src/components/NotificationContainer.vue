<template>
  <Teleport to="body">
    <div class="notification-container">
      <NotificationToast
        v-for="notification in notificationStore.notifications"
        :key="notification.id"
        :type="notification.type"
        :title="notification.title"
        :message="notification.message"
        :duration="notification.duration"
        @close="notificationStore.removeNotification(notification.id)"
      />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useNotificationStore } from '@/stores/notification'
import NotificationToast from './NotificationToast.vue'

const notificationStore = useNotificationStore()
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
}

.notification-container > * {
  pointer-events: auto;
}

@media (max-width: 640px) {
  .notification-container {
    top: 10px;
    right: 10px;
    left: 10px;
  }
}
</style>