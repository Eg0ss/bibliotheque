// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const routes = [
  // ─────────────────────────────────────────────────────────
  // ROUTES PUBLIQUES  →  layout: 'guest' (NavBar publique)
  // ─────────────────────────────────────────────────────────
  {
    path: '/',
    name: 'home',
    component: () => import('../views/public/HomeView.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/catalogue',
    name: 'catalogue',
    component: () => import('../views/public/CatalogView.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/recherche',
    name: 'recherche',
    component: () => import('../views/public/SearchView.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/statistiques',
    name: 'statistiques',
    component: () => import('../views/public/StatisticsView.vue'),
    meta: { layout: 'guest' },
  },

  // ─────────────────────────────────────────────────────────
  // AUTHENTIFICATION  →  layout: 'none' (plein écran, pas de navbar)
  // ─────────────────────────────────────────────────────────
  {
    path: '/connexion',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue'),
    meta: { layout: 'none' },
  },
  {
    path: '/inscription',
    name: 'register',
    component: () => import('../views/auth/RegisterView.vue'),
    meta: { layout: 'none' },
  },

  // ─────────────────────────────────────────────────────────
  // ESPACE UTILISATEUR CONNECTÉ  →  layout: 'user'
  // ─────────────────────────────────────────────────────────
  {
    path: '/mon-espace',
    meta: { layout: 'user', requiresAuth: true },
    children: [
      {
        path: 'profil',
        name: 'user.profil',
        component: () => import('../views/user/ProfileView.vue'),
      },
      {
        path: 'depots',
        name: 'user.depots',
        component: () => import('../views/user/MyRequestsView.vue'),
      },
      {
        path: 'depots/nouveau',
        name: 'user.depots.create',
        component: () => import('../views/user/DepotRequestFormView.vue'),
      },
    ],
  },

  // ─────────────────────────────────────────────────────────
  // ESPACE ADMIN  →  layout: 'admin' (sidebar AdminLayout)
  // ─────────────────────────────────────────────────────────
  {
    path: '/admin',
    meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: () => import('../views/admin/DashboardView.vue'),
      },
      // ── Gestion utilisateurs ──
      {
        path: 'utilisateurs',
        name: 'admin.users',
        component: () => import('../views/admin/UsersListView.vue'),
      },
      {
        path: 'utilisateurs/nouveau',
        name: 'admin.users.create',
        component: () => import('../views/admin/UserCreateView.vue'),
      },
      {
        path: 'utilisateurs/:id',
        name: 'admin.users.show',
        component: () => import('../views/admin/UserShowView.vue'),
      },
      // ── Workflow documents ──
      {
        path: 'publication',
        name: 'admin.publication',
        component: () => import('../views/admin/PublicationView.vue'),
      },
      {
        path: 'assignation',
        name: 'admin.assignation',
        component: () => import('../views/admin/AssignmentView.vue'),
      },
      {
        path: 'decision',
        name: 'admin.decision',
        component: () => import('../views/admin/FinalDecisionView.vue'),
      },
    ],
  },

  //_______________________________________________________________
  //admin/children
  //______________________________________________________
  // Catégories
  {
    path: '/categories',
    name: 'admin.categories',
    component: () => import('../views/admin/categories/CategoriesListView.vue'),
  },
  {
    path: '/categories/nouvelle',
    name: 'admin.categories.create',
    component: () => import('../views/admin/categories/CategoryCreateView.vue'),
  },

  // ─────────────────────────────────────────────────────────
  // ESPACE GESTIONNAIRE  →  layout: 'admin' (même sidebar)
  // ─────────────────────────────────────────────────────────
  {
    path: '/gestionnaire',
    meta: { layout: 'admin', requiresAuth: true, role: 'gestionnaire' },
    children: [
      {
        path: '',
        name: 'gestionnaire.dashboard',
        component: () => import('../views/manager/PendingDocumentsView.vue'),
      },
      {
        path: 'documents',
        name: 'gestionnaire.documents',
        component: () => import('../views/manager/PendingDocumentsView.vue'),
      },
      {
        path: 'parametres',
        name: 'gestionnaire.parametres',
        component: () => import('../views/manager/ValidationParamsView.vue'),
      },
    ],
  },

  // ─────────────────────────────────────────────────────────
  // ESPACE RH  →  layout: 'admin' (même sidebar)
  // ─────────────────────────────────────────────────────────
  {
    path: '/rh',
    meta: { layout: 'admin', requiresAuth: true, role: 'rh' },
    children: [
      {
        path: '',
        name: 'rh.dashboard',
        component: () => import('../views/rh/UsersListView.vue'),
      },
      {
        path: 'utilisateurs',
        name: 'rh.utilisateurs',
        component: () => import('../views/rh/UsersListView.vue'),
      },
      {
        path: 'utilisateurs/nouveau',
        name: 'rh.utilisateurs.create',
        component: () => import('../views/rh/UserFormView.vue'),
      },
      {
        path: 'utilisateurs/:id/modifier',
        name: 'rh.utilisateurs.edit',
        component: () => import('../views/rh/UserFormView.vue'),
      },
      {
        path: 'historique',
        name: 'rh.historique',
        component: () => import('../views/rh/ActionHistoryView.vue'),
      },
    ],
  },

  // ─────────────────────────────────────────────────────────
  // 404
  // ─────────────────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ── Guard de navigation global ──────────────────────────────

router.beforeEach(async (to, from) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth) {
    // Rechargement de page : session inconnue → on demande à Laravel
    if (!authStore.isAuthenticated) {
      await authStore.fetchUser()
    }

    // Toujours pas connecté → rediriger vers /connexion
    if (!authStore.isAuthenticated) {
      return '/connexion' // ← plus de next(), on retourne directement
    }

    // Connecté mais mauvais rôle → retour accueil
    if (to.meta.role && authStore.userRole !== to.meta.role) {
      return '/'
    }
  }

  // Déjà connecté : bloquer /connexion et /inscription
  if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    return '/'
  }
})

export default router
