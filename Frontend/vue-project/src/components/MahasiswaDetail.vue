<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Detail Mahasiswa</h3>
        <button @click="$emit('close')" class="btn-close">×</button>
      </div>

      <div class="modal-body">
        <div class="detail-grid">
          <div class="detail-item">
            <label>NIM</label>
            <div class="detail-value">{{ mahasiswa.nim }}</div>
          </div>

          <div class="detail-item">
            <label>Nama</label>
            <div class="detail-value">{{ mahasiswa.nama }}</div>
          </div>

          <div class="detail-item">
            <label>Email</label>
            <div class="detail-value">
              <a :href="`mailto:${mahasiswa.email}`">{{ mahasiswa.email }}</a>
            </div>
          </div>

          <div class="detail-item">
            <label>Program Studi</label>
            <div class="detail-value">{{ mahasiswa.prodi }}</div>
          </div>

          <div class="detail-item">
            <label>Angkatan</label>
            <div class="detail-value">{{ mahasiswa.angkatan }}</div>
          </div>

          <div class="detail-item">
            <label>Status</label>
            <div class="detail-value">
              <span :class="['status', `status-${mahasiswa.status}`]">
                {{ mahasiswa.status }}
              </span>
            </div>
          </div>

          <div class="detail-item">
            <label>Tanggal Dibuat</label>
            <div class="detail-value">{{ formatDate(mahasiswa.created_at) }}</div>
          </div>

          <div class="detail-item">
            <label>Terakhir Diperbarui</label>
            <div class="detail-value">{{ formatDate(mahasiswa.updated_at) }}</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="$emit('close')" class="btn btn-secondary">
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Mahasiswa } from '@/stores/mahasiswa'

interface Props {
  mahasiswa: Mahasiswa
}

interface Emits {
  (e: 'close'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Methods
const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleOverlayClick = () => {
  emit('close')
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 20px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 768px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-item label {
  font-weight: 600;
  color: #666;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 5px;
}

.detail-value {
  font-size: 14px;
  color: #333;
  padding: 8px 0;
}

.detail-value a {
  color: #007bff;
  text-decoration: none;
}

.detail-value a:hover {
  text-decoration: underline;
}

.status {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
  display: inline-block;
}

.status-aktif {
  background-color: #d4edda;
  color: #155724;
}

.status-cuti {
  background-color: #fff3cd;
  color: #856404;
}

.status-lulus {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-dropout {
  background-color: #f8d7da;
  color: #721c24;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 20px;
  border-top: 1px solid #eee;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}
</style>