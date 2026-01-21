import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiClient } from '@/services/api'

export interface Mahasiswa {
  id: number
  nim: string
  nama: string
  email: string
  prodi: string
  angkatan: number
  status: 'aktif' | 'cuti' | 'lulus' | 'dropout'
  created_at: string
  updated_at: string
}

export interface MahasiswaFilters {
  q?: string
  prodi?: string
  status?: string
  angkatan?: number
  sortBy?: string
  sortDir?: 'asc' | 'desc'
  per_page?: number
  page?: number
}

export interface PaginationInfo {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
}

export const useMahasiswaStore = defineStore('mahasiswa', () => {
  const mahasiswas = ref<Mahasiswa[]>([])
  const pagination = ref<PaginationInfo>({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: null,
    to: null
  })
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const totalMahasiswas = computed(() => pagination.value.total)
  const hasNextPage = computed(() => pagination.value.current_page < pagination.value.last_page)
  const hasPrevPage = computed(() => pagination.value.current_page > 1)

  // Actions
  const fetchMahasiswas = async (filters: MahasiswaFilters = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const params = new URLSearchParams()
      
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString())
        }
      })

      const response = await apiClient.get(`/mahasiswa?${params.toString()}`)
      
      if (response.data.success) {
        mahasiswas.value = response.data.data
        pagination.value = response.data.pagination
      } else {
        throw new Error(response.data.message || 'Failed to fetch mahasiswas')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to fetch mahasiswas'
      console.error('Error fetching mahasiswas:', err)
    } finally {
      loading.value = false
    }
  }

  const getMahasiswa = async (id: number): Promise<Mahasiswa | null> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.get(`/mahasiswa/${id}`)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Failed to fetch mahasiswa')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to fetch mahasiswa'
      console.error('Error fetching mahasiswa:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const createMahasiswa = async (data: Omit<Mahasiswa, 'id' | 'created_at' | 'updated_at'>): Promise<boolean> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.post('/mahasiswa', data)
      
      if (response.data.success) {
        // Refresh the list
        await fetchMahasiswas()
        return true
      } else {
        throw new Error(response.data.message || 'Failed to create mahasiswa')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to create mahasiswa'
      console.error('Error creating mahasiswa:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  const updateMahasiswa = async (id: number, data: Omit<Mahasiswa, 'id' | 'created_at' | 'updated_at'>): Promise<boolean> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.put(`/mahasiswa/${id}`, data)
      
      if (response.data.success) {
        // Update the local data
        const index = mahasiswas.value.findIndex(m => m.id === id)
        if (index !== -1) {
          mahasiswas.value[index] = response.data.data
        }
        return true
      } else {
        throw new Error(response.data.message || 'Failed to update mahasiswa')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to update mahasiswa'
      console.error('Error updating mahasiswa:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  const deleteMahasiswa = async (id: number): Promise<boolean> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.delete(`/mahasiswa/${id}`)
      
      if (response.data.success) {
        // Remove from local data
        mahasiswas.value = mahasiswas.value.filter(m => m.id !== id)
        // Update pagination total
        pagination.value.total = Math.max(0, pagination.value.total - 1)
        return true
      } else {
        throw new Error(response.data.message || 'Failed to delete mahasiswa')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to delete mahasiswa'
      console.error('Error deleting mahasiswa:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    mahasiswas,
    pagination,
    loading,
    error,
    
    // Computed
    totalMahasiswas,
    hasNextPage,
    hasPrevPage,
    
    // Actions
    fetchMahasiswas,
    getMahasiswa,
    createMahasiswa,
    updateMahasiswa,
    deleteMahasiswa,
    clearError
  }
})