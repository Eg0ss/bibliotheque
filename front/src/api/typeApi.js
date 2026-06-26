import apiClient from './axios'

const typeApi = {

  // ← URL publique
  getAll() {
    return apiClient.get('/api/types')
  },

  // Pour le back-office admin
  getAllAdmin(page = 1) {
    return apiClient.get(`/api/admin/types?page=${page}`)
  },

  create(data) {
    return apiClient.post('/api/admin/types', data)
  },
}

export default typeApi