<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Konfirmasi</h3>
      </div>

      <div class="modal-body">
        <div class="confirm-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <p class="confirm-message">{{ message }}</p>
      </div>

      <div class="modal-footer">
        <button @click="$emit('cancel')" class="btn btn-secondary">
          Batal
        </button>
        <button @click="$emit('confirm')" class="btn btn-danger">
          Hapus
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  message: string
}

interface Emits {
  (e: 'confirm'): void
  (e: 'cancel'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const handleOverlayClick = () => {
  emit('cancel')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
}

.modal-header {
  padding: 20px 20px 0 20px;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  text-align: center;
}

.modal-body {
  padding: 20px;
  text-align: center;
}

.confirm-icon {
  margin-bottom: 15px;
  display: flex;
  justify-content: center;
}

.confirm-message {
  margin: 0;
  font-size: 14px;
  color: #333;
  line-height: 1.5;
}

.modal-footer {
  display: flex;
  justify-content: center;
  gap: 10px;
  padding: 0 20px 20px 20px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
  min-width: 80px;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}
</style>