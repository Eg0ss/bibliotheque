// src/api/assignmentApi.js
import apiClient from './axios'

const assignmentApi = {

  // Toutes les demandes en attente (status = pending)
  getPendingRequests(page = 1) {
    return apiClient.get(`/api/admin/depot-requests?page=${page}`)
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