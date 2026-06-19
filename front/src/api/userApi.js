// src/api/userApi.js
// Toutes les fonctions qui appellent les routes admin/users de Laravel

import apiClient from './axios'

const userApi = {

  /**
   * Récupérer la liste paginée des utilisateurs
   * @param {number} page - numéro de page (1 par défaut)
   */
  getAll(page = 1) {
    return apiClient.get(`/api/admin/users?page=${page}`)
  },

  /**
   * Récupérer un utilisateur par son id
   */
  getOne(id) {
    return apiClient.get(`/api/admin/users/${id}`)
  },

  /**
   * Créer un nouveau compte utilisateur
   * @param {Object} data - { name, email, password, password_confirmation, role_id }
   */
  create(data) {
    return apiClient.post('/api/admin/users', data)
  },

  /**
   * Modifier un compte (on l'utilisera à l'étape suivante)
   */
  update(id, data) {
    return apiClient.put(`/api/admin/users/${id}`, data)
  },

  /**
   * Basculer actif/inactif (on l'utilisera à l'étape suivante)
   */
  toggleStatus(id) {
    return apiClient.patch(`/api/admin/users/${id}/toggle-status`)
  },

  /**
   * Supprimer un compte (on l'utilisera à l'étape suivante)
   */
  remove(id) {
    return apiClient.delete(`/api/admin/users/${id}`)
  },

  /**
   * Récupérer la liste de tous les rôles (pour le select du formulaire)
   */
  getRoles() {
    return apiClient.get('/api/admin/roles')
  },
}

export default userApi