// On importe l'instance Axios configurée (baseURL, cookies Sanctum, etc.)
import apiClient from './axios'

/**
 * Récupère toutes les références publiées.
 * Appelle GET http://localhost:8000/api/references
 *
 * @returns {Promise} - La liste des références au format JSON
 */
export function fetchPublishedReferences() {
  return apiClient.get('/api/references')
}

/**
 * Récupère le détail d'une référence par son ID.
 * Appelle GET http://localhost:8000/api/references/{id}
 *
 * @param {string|number} id - L'identifiant de la référence
 * @returns {Promise} - Les données de la référence
 */
export function fetchReferenceById(id) {
  return apiClient.get(`/api/references/${id}`)
}

/**
 * Déclenche le téléchargement du PDF d'une référence.
 */
export function downloadReferenceFile(id) {
  return apiClient.get(`/api/references/${id}/telecharger`, {
    responseType: 'blob',
  })
}

/**
 * Like ou unlike une référence (bascule automatique côté backend).
 */
export function toggleLikeReference(id) {
  return apiClient.post(`/api/references/${id}/like`)
}

/**
 * Recherche avancée avec filtres.
 * @param {Object} params - Les paramètres de filtre
 *   { search: string, category_id: number, type_id: number, page: number }
 *
 * Axios transforme automatiquement l'objet params en query string :
 * { search: 'droit', category_id: 2 } → ?search=droit&category_id=2
 */
export function searchReferences(params = {}) {
  return apiClient.get('/api/references', { params })
}
