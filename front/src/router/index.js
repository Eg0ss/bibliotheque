// import { createRouter, createWebHistory } from 'vue-router'
// import { setupGuards } from './guards'

// const routes = [
//   // Public Routes (Guest Layout)
//   {
//     path: '/',
//     name: 'home',
//     component: () => import('@/views/public/HomeView.vue'),
//     meta: { layout: 'guest' }
//   },
//   {
//     path: '/catalogue',
//     name: 'catalogue',
//     component: () => import('@/views/public/CatalogView.vue'),
//     meta: { layout: 'guest' }
//   },
//   {
//     path: '/catalogue/:id',
//     name: 'catalogue.detail',
//     component: () => import('@/views/public/ReferenceDetailView.vue'),
//     meta: { layout: 'guest' }
//   },
//   {
//     path: '/recherche',
//     name: 'recherche',
//     component: () => import('@/views/public/SearchView.vue'),
//     meta: { layout: 'guest' }
//   },
//   {
//     path: '/statistiques',
//     name: 'statistiques',
//     component: () => import('@/views/public/StatisticsView.vue'),
//     meta: { layout: 'guest' }
//   },
//   {
//     path: '/connexion',
//     name: 'connexion',
//     component: () => import('@/views/auth/LoginView.vue'),
//     meta: { layout: 'none' }
//   },
//   {
//     path: '/inscription',
//     name: 'inscription',
//     component: () => import('@/views/auth/RegisterView.vue'),
//     meta: { layout: 'guest' }
//   },

//   // User Routes (User Layout)
//   {
//     path: '/profil',
//     name: 'profil',
//     component: () => import('@/views/user/ProfileView.vue'),
//     meta: { layout: 'user', roles: ['user', 'gestionnaire', 'admin', 'rh'] }
//   },
//   {
//     path: '/depot/nouveau',
//     name: 'depot.nouveau',
//     component: () => import('@/views/user/DepotRequestFormView.vue'),
//     meta: { layout: 'user', roles: ['user', 'gestionnaire', 'admin', 'rh'] }
//   },
//   {
//     path: '/mes-demandes',
//     name: 'mes-demandes',
//     component: () => import('@/views/user/MyRequestsView.vue'),
//     meta: { layout: 'user', roles: ['user', 'gestionnaire', 'admin', 'rh'] }
//   },

//   // Gestionnaire Routes (Admin Layout)
//   {
//     path: '/gestionnaire/documents',
//     name: 'gestionnaire.documents',
//     component: () => import('@/views/manager/PendingDocumentsView.vue'),
//     meta: { layout: 'admin', roles: ['gestionnaire'] }
//   },
//   {
//     path: '/gestionnaire/parametres',
//     name: 'gestionnaire.parametres',
//     component: () => import('@/views/manager/ValidationParamsView.vue'),
//     meta: { layout: 'admin', roles: ['gestionnaire'] }
//   },

//   // Admin Routes (Admin Layout)
//   {
//     path: '/admin',
//     name: 'admin.publication',
//     component: () => import('@/views/admin/PublicationView.vue'),
//     meta: { layout: 'admin', roles: ['admin'] }
//   },
//   {
//     path: '/admin/assignation',
//     name: 'admin.assignation',
//     component: () => import('@/views/admin/AssignmentView.vue'),
//     meta: { layout: 'admin', roles: ['admin'] }
//   },
//   {
//     path: '/admin/decision',
//     name: 'admin.decision',
//     component: () => import('@/views/admin/FinalDecisionView.vue'),
//     meta: { layout: 'admin', roles: ['admin'] }
//   },

//   // RH Routes (Admin Layout)
//   {
//     path: '/rh/utilisateurs',
//     name: 'rh.utilisateurs',
//     component: () => import('@/views/rh/UsersListView.vue'),
//     meta: { layout: 'admin', roles: ['rh'] }
//   },
//   {
//     path: '/rh/utilisateurs/nouveau',
//     name: 'rh.utilisateurs.nouveau',
//     component: () => import('@/views/rh/UserFormView.vue'),
//     meta: { layout: 'admin', roles: ['rh'] }
//   },
//   {
//     path: '/rh/utilisateurs/:id/modifier',
//     name: 'rh.utilisateurs.modifier',
//     component: () => import('@/views/rh/UserFormView.vue'),
//     meta: { layout: 'admin', roles: ['rh'] }
//   },
//   {
//     path: '/rh/historique',
//     name: 'rh.historique',
//     component: () => import('@/views/rh/ActionHistoryView.vue'),
//     meta: { layout: 'admin', roles: ['rh'] }
//   }
// ]

// const router = createRouter({
//   history: createWebHistory(import.meta.env.BASE_URL),
//   routes
// })

// setupGuards(router)

// export default router
// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const routes = [
  // ── Routes publiques ──────────────────────────────────────
  { path: '/',           name: 'home',        component: () => import('../views/public/HomeView.vue') },
  { path: '/catalogue',  name: 'catalogue',   component: () => import('../views/public/CatalogView.vue') },
  { path: '/recherche',  name: 'recherche',   component: () => import('../views/public/SearchView.vue') },
  { path: '/connexion',  name: 'login',       component: () => import('../views/auth/LoginView.vue') },
  { path: '/inscription',name: 'register',    component: () => import('../views/auth/RegisterView.vue') },

  // ── Routes protégées : utilisateur connecté ───────────────
  {
    path: '/mon-espace',
    meta: { requiresAuth: true }, // ce flag est lu par le guard
    children: [
      { path: 'profil',  component: () => import('../views/user/ProfileView.vue') },
      { path: 'depots',  component: () => import('../views/user/MyRequestsView.vue') },
      { path: 'depots/nouveau', component: () => import('../views/user/DepotRequestFormView.vue') },
    ],
  },

  // ── Routes protégées : Admin ───────────────────────────────
{
  path: '/admin',
  meta: { requiresAuth: true, role: 'admin' },
  children: [
    // Tableau de bord admin (page d'accueil de l'espace admin)
    { path: '',              name: 'admin.dashboard',      component: () => import('../views/admin/DashboardView.vue') },

    // Gestion des utilisateurs
    { path: 'utilisateurs',         name: 'admin.users',          component: () => import('../views/admin/UsersListView.vue') },
    { path: 'utilisateurs/nouveau', name: 'admin.users.create',   component: () => import('../views/admin/UserCreateView.vue') },
    { path: 'utilisateurs/:id',     name: 'admin.users.show',     component: () => import('../views/admin/UserShowView.vue') },

    // Publication / Assignation / Décision (déjà existants)
    { path: 'publication',  name: 'admin.publication',    component: () => import('../views/admin/PublicationView.vue') },
    { path: 'assignation',  name: 'admin.assignation',    component: () => import('../views/admin/AssignmentView.vue') },
    { path: 'decision',     name: 'admin.decision',       component: () => import('../views/admin/FinalDecisionView.vue') },
  ],
},

  // ── Routes protégées : Gestionnaire ───────────────────────
  {
    path: '/gestionnaire',
    meta: { requiresAuth: true, role: 'gestionnaire' },
    children: [
      { path: '', component: () => import('../views/manager/PendingDocumentsView.vue') },
    ],
  },

  // ── Routes protégées : RH ─────────────────────────────────
  {
    path: '/rh',
    meta: { requiresAuth: true, role: 'rh' },
    children: [
      { path: '', component: () => import('../views/rh/UsersListView.vue') },
    ],
  },

  // ── 404 ───────────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

/**
 * Guard de navigation global
 * Exécuté avant chaque changement de route
 */
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Si la route nécessite une authentification
  if (to.meta.requiresAuth) {

    // Si on ne connaît pas encore l'état de la session (ex: rechargement de page)
    if (!authStore.isAuthenticated) {
      // On demande à Laravel si la session est encore valide
      await authStore.fetchUser()
    }

    // Toujours pas connecté après vérification → rediriger vers /connexion
    if (!authStore.isAuthenticated) {
      return next('/connexion')
    }

    // Connecté mais mauvais rôle pour cette route
    if (to.meta.role && authStore.userRole !== to.meta.role) {
      // Rediriger vers son propre espace selon son rôle
      return next('/')
    }
  }

  // Empêcher un utilisateur déjà connecté d'accéder à /connexion ou /inscription
  if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    return next('/')
  }

  next() // Tout est OK, on laisse passer
})

export default router