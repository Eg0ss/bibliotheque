// src/api/categoryApi.js
import apiClient from './axios'

const categoryApi = {

  // Liste paginée
  getAll(page = 1) {
    return apiClient.get(`/api/admin/categories?page=${page}`)
  },

  // Toutes les catégories sans pagination (pour les <select>)
  getAllFlat() {
    return apiClient.get('/api/admin/categories/all')
  },

  // Créer
  create(data) {
    return apiClient.post('/api/admin/categories', data)
  },

  // Modifier (prochain étape)
  update(id, data) {
    return apiClient.put(`/api/admin/categories/${id}`, data)
  },

  // Supprimer (prochain étape)
  remove(id) {
    return apiClient.delete(`/api/admin/categories/${id}`)
  },
}

export default categoryApi