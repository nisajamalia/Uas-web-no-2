<template>
  <div class="mahasiswa-list">
    <div class="header">
      <h2>Data Mahasiswa</h2>
      <button @click="showCreateForm = true" class="btn btn-primary">
        Tambah Mahasiswa
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          v-model="filters.q"
          @input="debouncedSearch"
          type="text"
          placeholder="Cari nama, NIM, atau email..."
          class="form-control"
        />
      </div>
      
      <div class="filter-controls">
        <select v-model="filters.prodi" @change="applyFilters" class="form-control">
          <option value="">Semua Prodi</option>
          <option value="Teknik Informatika">Teknik Informatika</option>
          <option value="Sistem Informasi">Sistem Informasi</option>
          <option value="Manajemen Informatika">Manajemen Informatika</option>
        </select>

        <select v-model="filters.status" @change="applyFilters" class="form-control">
          <option value="">Semua Status</option>
          <option value="aktif">Aktif</option>
          <option value="cuti">Cuti</option>
          <option value="lulus">Lulus</option>
          <option value="dropout">Dropout</option>
        </select>

        <select v-model="filters.angkatan" @change="applyFilters" class="form-control">
          <option value="">Semua Angkatan</option>
          <option v-for="year in availableYears" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="mahasiswaStore.loading" class="loading">
      Loading...
    </div>

    <!-- Error State -->
    <div v-if="mahasiswaStore.error" class="error">
      {{ mahasiswaStore.error }}
      <button @click="mahasiswaStore.clearError()" class="btn-close">×</button>
    </div>

    <!-- Data Table -->
    <div v-if="!mahasiswaStore.loading" class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th @click="sort('nim')" class="sortable">
              NIM
              <span v-if="filters.sortBy === 'nim'" class="sort-indicator">
                {{ filters.sortDir === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th @click="sort('nama')" class="sortable">
              Nama
              <span v-if="filters.sortBy === 'nama'" class="sort-indicator">
                {{ filters.sortDir === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th @click="sort('email')" class="sortable">
              Email
              <span v-if="filters.sortBy === 'email'" class="sort-indicator">
                {{ filters.sortDir === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th @click="sort('prodi')" class="sortable">
              Prodi
              <span v-if="filters.sortBy === 'prodi'" class="sort-indicator">
                {{ filters.sortDir === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th @click="sort('angkatan')" class="sortable">
              Angkatan
              <span v-if="filters.sortBy === 'angkatan'" class="sort-indicator">
                {{ filters.sortDir === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th @click="sort('status')" class="sortable">
              Status
              <span v-if="filters.sortBy === 'status'" class="sort-indicator">
                {{ filters.sortDir === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="mahasiswa in mahasiswaStore.mahasiswas" :key="mahasiswa.id">
            <td>{{ mahasiswa.nim }}</td>
            <td>{{ mahasiswa.nama }}</td>
            <td>{{ mahasiswa.email }}</td>
            <td>{{ mahasiswa.prodi }}</td>
            <td>{{ mahasiswa.angkatan }}</td>
            <td>
              <span :class="['status', `status-${mahasiswa.status}`]">
                {{ mahasiswa.status }}
              </span>
            </td>
            <td class="actions">
              <button @click="viewDetail(mahasiswa)" class="btn btn-info btn-sm">
                Detail
              </button>
              <button @click="editMahasiswa(mahasiswa)" class="btn btn-warning btn-sm">
                Edit
              </button>
              <button @click="confirmDelete(mahasiswa)" class="btn btn-danger btn-sm">
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="mahasiswaStore.mahasiswas.length === 0" class="empty-state">
        <p>Tidak ada data mahasiswa yang ditemukan.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="mahasiswaStore.pagination.total > 0" class="pagination">
      <div class="pagination-info">
        Menampilkan {{ mahasiswaStore.pagination.from }} - {{ mahasiswaStore.pagination.to }} 
        dari {{ mahasiswaStore.pagination.total }} data
      </div>
      
      <div class="pagination-controls">
        <button 
          @click="changePage(mahasiswaStore.pagination.current_page - 1)"
          :disabled="!mahasiswaStore.hasPrevPage"
          class="btn btn-secondary"
        >
          Previous
        </button>
        
        <span class="page-info">
          Halaman {{ mahasiswaStore.pagination.current_page }} dari {{ mahasiswaStore.pagination.last_page }}
        </span>
        
        <button 
          @click="changePage(mahasiswaStore.pagination.current_page + 1)"
          :disabled="!mahasiswaStore.hasNextPage"
          class="btn btn-secondary"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Modals -->
    <MahasiswaForm
      v-if="showCreateForm"
      :mahasiswa="null"
      @close="showCreateForm = false"
      @saved="handleSaved"
    />

    <MahasiswaForm
      v-if="showEditForm && selectedMahasiswa"
      :mahasiswa="selectedMahasiswa"
      @close="showEditForm = false"
      @saved="handleSaved"
    />

    <MahasiswaDetail
      v-if="showDetail && selectedMahasiswa"
      :mahasiswa="selectedMahasiswa"
      @close="showDetail = false"
    />

    <ConfirmDialog
      v-if="showDeleteConfirm"
      :message="`Apakah Anda yakin ingin menghapus mahasiswa ${selectedMahasiswa?.nama}?`"
      @confirm="handleDelete"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useMahasiswaStore, type Mahasiswa, type MahasiswaFilters } from '@/stores/mahasiswa'
import { useNotificationStore } from '@/stores/notification'
import MahasiswaForm from './MahasiswaForm.vue'
import MahasiswaDetail from './MahasiswaDetail.vue'
import ConfirmDialog from './ConfirmDialog.vue'

const mahasiswaStore = useMahasiswaStore()
const notificationStore = useNotificationStore()

// Reactive state
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showDetail = ref(false)
const showDeleteConfirm = ref(false)
const selectedMahasiswa = ref<Mahasiswa | null>(null)

const filters = ref<MahasiswaFilters>({
  q: '',
  prodi: '',
  status: '',
  angkatan: undefined,
  sortBy: 'created_at',
  sortDir: 'desc',
  per_page: 15,
  page: 1
})

// Computed
const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let year = currentYear; year >= currentYear - 10; year--) {
    years.push(year)
  }
  return years
})

// Methods
let searchTimeout: NodeJS.Timeout

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  filters.value.page = 1 // Reset to first page
  mahasiswaStore.fetchMahasiswas(filters.value)
}

const sort = (field: string) => {
  if (filters.value.sortBy === field) {
    filters.value.sortDir = filters.value.sortDir === 'asc' ? 'desc' : 'asc'
  } else {
    filters.value.sortBy = field
    filters.value.sortDir = 'asc'
  }
  applyFilters()
}

const changePage = (page: number) => {
  filters.value.page = page
  mahasiswaStore.fetchMahasiswas(filters.value)
}

const viewDetail = (mahasiswa: Mahasiswa) => {
  selectedMahasiswa.value = mahasiswa
  showDetail.value = true
}

const editMahasiswa = (mahasiswa: Mahasiswa) => {
  selectedMahasiswa.value = mahasiswa
  showEditForm.value = true
}

const confirmDelete = (mahasiswa: Mahasiswa) => {
  selectedMahasiswa.value = mahasiswa
  showDeleteConfirm.value = true
}

const handleDelete = async () => {
  if (selectedMahasiswa.value) {
    const success = await mahasiswaStore.deleteMahasiswa(selectedMahasiswa.value.id)
    if (success) {
      notificationStore.success(
        'Berhasil!', 
        `Mahasiswa ${selectedMahasiswa.value.nama} berhasil dihapus`
      )
      // Refresh data if we're on a page that might be empty now
      if (mahasiswaStore.mahasiswas.length === 0 && filters.value.page! > 1) {
        filters.value.page = filters.value.page! - 1
      }
      mahasiswaStore.fetchMahasiswas(filters.value)
    } else {
      notificationStore.error(
        'Gagal!', 
        mahasiswaStore.error || 'Gagal menghapus mahasiswa'
      )
    }
  }
  showDeleteConfirm.value = false
  selectedMahasiswa.value = null
}

const handleSaved = () => {
  const isEdit = showEditForm.value
  showCreateForm.value = false
  showEditForm.value = false
  
  if (isEdit) {
    notificationStore.success('Berhasil!', 'Data mahasiswa berhasil diperbarui')
  } else {
    notificationStore.success('Berhasil!', 'Mahasiswa baru berhasil ditambahkan')
  }
  
  selectedMahasiswa.value = null
  // Refresh the current page
  mahasiswaStore.fetchMahasiswas(filters.value)
}

// Lifecycle
onMounted(() => {
  mahasiswaStore.fetchMahasiswas(filters.value)
})
</script>

<style scoped>
.mahasiswa-list {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
}

.filter-controls {
  display: flex;
  gap: 10px;
}

.form-control {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.table-container {
  overflow-x: auto;
  margin-bottom: 20px;
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th,
.table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  background-color: #e9ecef;
}

.sort-indicator {
  margin-left: 5px;
  font-size: 12px;
}

.status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
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

.actions {
  display: flex;
  gap: 5px;
}

.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  text-decoration: none;
  display: inline-block;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-info {
  background-color: #17a2b8;
  color: white;
}

.btn-warning {
  background-color: #ffc107;
  color: #212529;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 15px;
}

.page-info {
  font-size: 14px;
  color: #666;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #666;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #721c24;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}
</style>