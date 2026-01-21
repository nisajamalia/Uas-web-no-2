<template>
  <div class="modal-overlay" @click="handleOverlayClick">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>{{ isEdit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' }}</h3>
        <button @click="$emit('close')" class="btn-close">×</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">
        <div class="form-group">
          <label for="nim">NIM *</label>
          <input
            id="nim"
            v-model="form.nim"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.nim }"
            maxlength="20"
            required
          />
          <div v-if="errors.nim" class="invalid-feedback">
            {{ errors.nim }}
          </div>
        </div>

        <div class="form-group">
          <label for="nama">Nama *</label>
          <input
            id="nama"
            v-model="form.nama"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.nama }"
            maxlength="255"
            required
          />
          <div v-if="errors.nama" class="invalid-feedback">
            {{ errors.nama }}
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control"
            :class="{ 'is-invalid': errors.email }"
            required
          />
          <div v-if="errors.email" class="invalid-feedback">
            {{ errors.email }}
          </div>
        </div>

        <div class="form-group">
          <label for="prodi">Program Studi *</label>
          <select
            id="prodi"
            v-model="form.prodi"
            class="form-control"
            :class="{ 'is-invalid': errors.prodi }"
            required
          >
            <option value="">Pilih Program Studi</option>
            <option value="Teknik Informatika">Teknik Informatika</option>
            <option value="Sistem Informasi">Sistem Informasi</option>
            <option value="Manajemen Informatika">Manajemen Informatika</option>
          </select>
          <div v-if="errors.prodi" class="invalid-feedback">
            {{ errors.prodi }}
          </div>
        </div>

        <div class="form-group">
          <label for="angkatan">Angkatan *</label>
          <select
            id="angkatan"
            v-model="form.angkatan"
            class="form-control"
            :class="{ 'is-invalid': errors.angkatan }"
            required
          >
            <option value="">Pilih Angkatan</option>
            <option v-for="year in availableYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
          <div v-if="errors.angkatan" class="invalid-feedback">
            {{ errors.angkatan }}
          </div>
        </div>

        <div class="form-group">
          <label for="status">Status *</label>
          <select
            id="status"
            v-model="form.status"
            class="form-control"
            :class="{ 'is-invalid': errors.status }"
            required
          >
            <option value="">Pilih Status</option>
            <option value="aktif">Aktif</option>
            <option value="cuti">Cuti</option>
            <option value="lulus">Lulus</option>
            <option value="dropout">Dropout</option>
          </select>
          <div v-if="errors.status" class="invalid-feedback">
            {{ errors.status }}
          </div>
        </div>

        <!-- General Error -->
        <div v-if="generalError" class="alert alert-danger">
          {{ generalError }}
        </div>

        <div class="modal-footer">
          <button type="button" @click="$emit('close')" class="btn btn-secondary">
            Batal
          </button>
          <button type="submit" :disabled="loading" class="btn btn-primary">
            {{ loading ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useMahasiswaStore, type Mahasiswa } from '@/stores/mahasiswa'

interface Props {
  mahasiswa: Mahasiswa | null
}

interface Emits {
  (e: 'close'): void
  (e: 'saved'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const mahasiswaStore = useMahasiswaStore()

// Reactive state
const loading = ref(false)
const generalError = ref<string | null>(null)
const errors = ref<Record<string, string>>({})

const form = ref({
  nim: '',
  nama: '',
  email: '',
  prodi: '',
  angkatan: null as number | null,
  status: '' as 'aktif' | 'cuti' | 'lulus' | 'dropout' | ''
})

// Computed
const isEdit = computed(() => props.mahasiswa !== null)

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let year = currentYear; year >= currentYear - 10; year--) {
    years.push(year)
  }
  return years
})

// Methods
const validateForm = (): boolean => {
  errors.value = {}
  let isValid = true

  // Client-side validation
  if (!form.value.nim.trim()) {
    errors.value.nim = 'NIM wajib diisi'
    isValid = false
  } else if (form.value.nim.length > 20) {
    errors.value.nim = 'NIM maksimal 20 karakter'
    isValid = false
  }

  if (!form.value.nama.trim()) {
    errors.value.nama = 'Nama wajib diisi'
    isValid = false
  } else if (form.value.nama.length > 255) {
    errors.value.nama = 'Nama maksimal 255 karakter'
    isValid = false
  }

  if (!form.value.email.trim()) {
    errors.value.email = 'Email wajib diisi'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Format email tidak valid'
    isValid = false
  }

  if (!form.value.prodi) {
    errors.value.prodi = 'Program studi wajib dipilih'
    isValid = false
  }

  if (!form.value.angkatan) {
    errors.value.angkatan = 'Angkatan wajib dipilih'
    isValid = false
  } else {
    const currentYear = new Date().getFullYear()
    const minYear = currentYear - 10
    if (form.value.angkatan < minYear || form.value.angkatan > currentYear) {
      errors.value.angkatan = `Angkatan harus antara ${minYear} - ${currentYear}`
      isValid = false
    }
  }

  if (!form.value.status) {
    errors.value.status = 'Status wajib dipilih'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  generalError.value = null
  
  if (!validateForm()) {
    return
  }

  loading.value = true

  try {
    const formData = {
      nim: form.value.nim.trim(),
      nama: form.value.nama.trim(),
      email: form.value.email.trim(),
      prodi: form.value.prodi,
      angkatan: form.value.angkatan!,
      status: form.value.status as 'aktif' | 'cuti' | 'lulus' | 'dropout'
    }

    let success = false

    if (isEdit.value && props.mahasiswa) {
      success = await mahasiswaStore.updateMahasiswa(props.mahasiswa.id, formData)
    } else {
      success = await mahasiswaStore.createMahasiswa(formData)
    }

    if (success) {
      emit('saved')
    } else {
      // Handle validation errors from server
      if (mahasiswaStore.error) {
        try {
          // Try to parse validation errors
          const errorMessage = mahasiswaStore.error
          if (errorMessage.includes('nim') && errorMessage.includes('sudah')) {
            errors.value.nim = 'NIM sudah terdaftar'
          } else if (errorMessage.includes('email') && errorMessage.includes('sudah')) {
            errors.value.email = 'Email sudah terdaftar'
          } else {
            generalError.value = errorMessage
          }
        } catch {
          generalError.value = mahasiswaStore.error
        }
      }
    }
  } catch (error: any) {
    console.error('Form submission error:', error)
    generalError.value = 'Terjadi kesalahan saat menyimpan data'
  } finally {
    loading.value = false
  }
}

const handleOverlayClick = () => {
  emit('close')
}

// Initialize form data
onMounted(() => {
  if (props.mahasiswa) {
    form.value = {
      nim: props.mahasiswa.nim,
      nama: props.mahasiswa.nama,
      email: props.mahasiswa.email,
      prodi: props.mahasiswa.prodi,
      angkatan: props.mahasiswa.angkatan,
      status: props.mahasiswa.status
    }
  }
})
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
  max-width: 500px;
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

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 12px;
  margin-top: 5px;
}

.alert {
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 20px;
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

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>