import apiClient from './axios'

const categoryApi = {

  // ← nouvelle URL publique (plus besoin d'être connecté)
  getAllFlat() {
    return apiClient.get('/api/categories')
  },

  // Pour le back-office admin (paginé)
  getAll(page = 1) {
    return apiClient.get(`/api/admin/categories?page=${page}`)
  },

  create(data) {
    return apiClient.post('/api/admin/categories', data)
  },

  update(id, data) {
    return apiClient.put(`/api/admin/categories/${id}`, data)
  },

  remove(id) {
    return apiClient.delete(`/api/admin/categories/${id}`)
  },
}

export default categoryApi