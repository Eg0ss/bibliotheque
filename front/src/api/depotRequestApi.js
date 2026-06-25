// Toutes les fonctions d'appel API liées aux demandes de dépôt

import apiClient from './axios'

const depotRequestApi = {

  /**
   * Soumettre une nouvelle demande de dépôt
   * On envoie FormData (pas JSON) car il y a des fichiers à uploader
   * @param {FormData} formData - les données + fichiers du formulaire
   */
  store(formData) {
    return apiClient.post('/api/user/depot-requests', formData, {
      // Axios détecte FormData et met automatiquement Content-Type: multipart/form-data
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  /**
   * Récupérer les demandes de l'utilisateur connecté
   * @param {number} page - numéro de page
   */
  getMyRequests(page = 1) {
    return apiClient.get('/api/user/depot-requests', { params: { page } })
  },

  /**
   * Voir le détail d'une demande
   * @param {number} id
   */
  getOne(id) {
    return apiClient.get(`/api/user/depot-requests/${id}`)
  },

  /**
   * Charger les catégories pour le <select> du formulaire
   */
  getCategories() {
    return apiClient.get('/api/user/categories')
  },

  /**
   * Charger les types pour le <select> du formulaire
   */
  getTypes() {
    return apiClient.get('/api/user/types')
  },
}

export default depotRequestApi