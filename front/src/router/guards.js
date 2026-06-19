import { useAuthStore } from '@/stores/authStore'

export function setupGuards(router) {
  router.beforeEach((to, from) => {
    const authStore = useAuthStore()
    const requiredRoles = to.meta.roles
    const requiresAuth = to.meta.requiresAuth !== false && requiredRoles

    if (requiresAuth) {
      if (!authStore.isAuthenticated) {
        return { name: 'connexion' }
      }

      if (requiredRoles && !requiredRoles.includes(authStore.role)) {
        return { name: 'home' }
      }
    }
  })
}
