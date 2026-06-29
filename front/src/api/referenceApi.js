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