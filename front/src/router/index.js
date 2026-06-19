import { createRouter, createWebHistory } from 'vue-router'
import { setupGuards } from './guards'

const routes = [
  // Public Routes (Guest Layout)
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/public/HomeView.vue'),
    meta: { layout: 'guest' }
  },
  {
    path: '/catalogue',
    name: 'catalogue',
    component: () => import('@/views/public/CatalogView.vue'),
    meta: { layout: 'guest' }
  },
  {
    path: '/catalogue/:id',
    name: 'catalogue.detail',
    component: () => import('@/views/public/ReferenceDetailView.vue'),
    meta: { layout: 'guest' }
  },
  {
    path: '/recherche',
    name: 'recherche',
    component: () => import('@/views/public/SearchView.vue'),
    meta: { layout: 'guest' }
  },
  {
    path: '/statistiques',
    name: 'statistiques',
    component: () => import('@/views/public/StatisticsView.vue'),
    meta: { layout: 'guest' }
  },
  {
    path: '/connexion',
    name: 'connexion',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { layout: 'none' }
  },
  {
    path: '/inscription',
    name: 'inscription',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { layout: 'guest' }
  },

  // User Routes (User Layout)
  {
    path: '/profil',
    name: 'profil',
    component: () => import('@/views/user/ProfileView.vue'),
    meta: { layout: 'user', roles: ['user', 'gestionnaire', 'admin', 'rh'] }
  },
  {
    path: '/depot/nouveau',
    name: 'depot.nouveau',
    component: () => import('@/views/user/DepotRequestFormView.vue'),
    meta: { layout: 'user', roles: ['user', 'gestionnaire', 'admin', 'rh'] }
  },
  {
    path: '/mes-demandes',
    name: 'mes-demandes',
    component: () => import('@/views/user/MyRequestsView.vue'),
    meta: { layout: 'user', roles: ['user', 'gestionnaire', 'admin', 'rh'] }
  },

  // Gestionnaire Routes (Admin Layout)
  {
    path: '/gestionnaire/documents',
    name: 'gestionnaire.documents',
    component: () => import('@/views/manager/PendingDocumentsView.vue'),
    meta: { layout: 'admin', roles: ['gestionnaire'] }
  },
  {
    path: '/gestionnaire/parametres',
    name: 'gestionnaire.parametres',
    component: () => import('@/views/manager/ValidationParamsView.vue'),
    meta: { layout: 'admin', roles: ['gestionnaire'] }
  },

  // Admin Routes (Admin Layout)
  {
    path: '/admin',
    name: 'admin.publication',
    component: () => import('@/views/admin/PublicationView.vue'),
    meta: { layout: 'admin', roles: ['admin'] }
  },
  {
    path: '/admin/assignation',
    name: 'admin.assignation',
    component: () => import('@/views/admin/AssignmentView.vue'),
    meta: { layout: 'admin', roles: ['admin'] }
  },
  {
    path: '/admin/decision',
    name: 'admin.decision',
    component: () => import('@/views/admin/FinalDecisionView.vue'),
    meta: { layout: 'admin', roles: ['admin'] }
  },

  // RH Routes (Admin Layout)
  {
    path: '/rh/utilisateurs',
    name: 'rh.utilisateurs',
    component: () => import('@/views/rh/UsersListView.vue'),
    meta: { layout: 'admin', roles: ['rh'] }
  },
  {
    path: '/rh/utilisateurs/nouveau',
    name: 'rh.utilisateurs.nouveau',
    component: () => import('@/views/rh/UserFormView.vue'),
    meta: { layout: 'admin', roles: ['rh'] }
  },
  {
    path: '/rh/utilisateurs/:id/modifier',
    name: 'rh.utilisateurs.modifier',
    component: () => import('@/views/rh/UserFormView.vue'),
    meta: { layout: 'admin', roles: ['rh'] }
  },
  {
    path: '/rh/historique',
    name: 'rh.historique',
    component: () => import('@/views/rh/ActionHistoryView.vue'),
    meta: { layout: 'admin', roles: ['rh'] }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

setupGuards(router)

export default router
