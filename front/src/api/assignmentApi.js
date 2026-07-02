// src/api/assignmentApi.js
import apiClient from './axios'

const assignmentApi = {
  // Demandes en attente avec recherche et filtres
  getPendingRequests(page = 1, filters = {}) {
    return apiClient.get('/api/admin/depot-requests', {
      params: { page, ...filters },
    })
  },

  // Toutes les assignations existantes
  getAssignments(page = 1) {
    return apiClient.get(`/api/admin/assignments?page=${page}`)
  },

  // Créer une assignation
  assign(data) {
    return apiClient.post('/api/admin/assignments', data)
  },

  // Liste des gestionnaires actifs (pour le <select> du modal)
  getGestionnaires() {
    return apiClient.get('/api/admin/gestionnaires')
  },
}

export default assignmentApi
