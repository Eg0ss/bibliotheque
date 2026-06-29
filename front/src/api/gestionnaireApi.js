// src/api/gestionnaireApi.js
import apiClient from './axios'

const gestionnaireApi = {

  // Documents assignés au gestionnaire connecté
  getDocuments(page = 1) {
    return apiClient.get(`/api/gestionnaire/documents?page=${page}`)
  },
}

export default gestionnaireApi