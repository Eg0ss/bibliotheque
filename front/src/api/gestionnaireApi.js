import apiClient from './axios'

const gestionnaireApi = {
  // Documents assignés (en attente de décision)
  getDocuments: (page = 1) =>
    apiClient.get('/api/gestionnaire/documents', { params: { page } }),

  // Détail d'une assignation
  getDocument: (id) =>
    apiClient.get(`/api/gestionnaire/documents/${id}`),

  // Prendre une décision (accepter / rejeter)
  decide: (id, data) =>
    apiClient.post(`/api/gestionnaire/documents/${id}/decision`, data),

  // Mes validations déjà effectuées
  getMyValidations: (page = 1) =>
    apiClient.get('/api/gestionnaire/validations', { params: { page } }),
}

export default gestionnaireApi