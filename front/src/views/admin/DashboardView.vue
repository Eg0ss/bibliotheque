<script setup>
/**
 * DashboardView.vue — Tableau de bord Admin
 * Route : /admin (path: '')
 *
 * Charge GET /api/admin/stats au montage
 * et affiche les métriques de manière dynamique.
 */

import { ref, onMounted } from 'vue'
import { RouterLink }     from 'vue-router'
import { useAuthStore }   from '@/stores/authStore'
import apiClient          from '@/api/axios'

const authStore = useAuthStore()
const loading   = ref(true)

// Structure miroir de ce que retourne le backend
const stats = ref({
  utilisateurs: { actifs: 0, inactifs: 0, total: 0 },
  references  : { publiees: 0, rejetees: 0 },
  demandes    : { en_attente: 0, traitees_gestionnaires: 0 },
})

async function fetchStats() {
  loading.value = true
  try {
    const res  = await apiClient.get('/api/admin/stats')
    stats.value = res.data.data
  } catch {
    // Silencieux : le dashboard ne doit pas bloquer
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchStats())
</script>

<template>
  <div class="space-y-6">

    <!-- ── En-tête ─────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-[#042C53]">
          Tableau de bord
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Bonjour <span class="font-medium">{{ authStore.user?.name }}</span> —
          voici l'état actuel de la bibliothèque.
        </p>
      </div>
      <!-- Bouton rafraîchir -->
      <button
        @click="fetchStats"
        :disabled="loading"
        class="flex items-center gap-2 text-sm text-gray-500 border border-gray-300
               px-3 py-2 rounded-lg hover:bg-gray-50 transition disabled:opacity-40"
      >
        <span :class="{ 'animate-spin': loading }"></span>
        Actualiser
      </button>
    </div>

    <!-- ── SECTION 1 : Utilisateurs ─────────────────────────────────── -->
    <div>
      <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
        Comptes utilisateurs <span class="normal-case">(hors admin)</span>
      </h2>
      <div class="grid grid-cols-3 gap-4">

        <!-- Total -->
        <div class="bg-[#042C53] rounded-xl p-5 shadow-sm text-white">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-white/70">Total</span>
            <span class="text-2xl">👥</span>
          </div>
          <p class="text-4xl font-bold">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.utilisateurs.total }}</span>
          </p>
          <RouterLink
            to="/admin/utilisateurs"
            class="text-xs text-white/60 hover:text-white mt-2 inline-block transition"
          >
            Gérer les comptes →
          </RouterLink>
        </div>

        <!-- Actifs -->
        <div class="bg-green-50 border border-green-100 rounded-xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-green-700">Actifs</span>
            <span class="text-2xl"></span>
          </div>
          <p class="text-4xl font-bold text-green-800">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.utilisateurs.actifs }}</span>
          </p>
          <RouterLink
            to="/admin/utilisateurs?status=1"
            class="text-xs text-green-500 hover:text-green-700 mt-2 inline-block transition"
          >
            Voir les actifs →
          </RouterLink>
        </div>

        <!-- Inactifs -->
        <div class="bg-red-50 border border-red-100 rounded-xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-red-700">Inactifs</span>
            <span class="text-2xl"></span>
          </div>
          <p class="text-4xl font-bold text-red-800">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.utilisateurs.inactifs }}</span>
          </p>
          <RouterLink
            to="/admin/utilisateurs?status=0"
            class="text-xs text-red-400 hover:text-red-600 mt-2 inline-block transition"
          >
            Voir les inactifs →
          </RouterLink>
        </div>

      </div>
    </div>

    <!-- ── SECTION 2 : Mes décisions (admin courant) ─────────────────── -->
    <div>
      <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
        Mes décisions sur les références
      </h2>
      <div class="grid grid-cols-2 gap-4">

        <!-- Références publiées par moi -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-indigo-700">Publiées par moi</span>
            <span class="text-2xl"></span>
          </div>
          <p class="text-4xl font-bold text-indigo-800">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.references.publiees }}</span>
          </p>
          <p class="text-xs text-indigo-400 mt-2">
            Références validées et publiées par votre compte
          </p>
        </div>

        <!-- Références rejetées par moi -->
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-orange-700">Rejetées par moi</span>
            <span class="text-2xl"></span>
          </div>
          <p class="text-4xl font-bold text-orange-800">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.references.rejetees }}</span>
          </p>
          <p class="text-xs text-orange-400 mt-2">
            Références rejetées définitivement par votre compte
          </p>
        </div>

      </div>
    </div>

    <!-- ── SECTION 3 : Flux des demandes ─────────────────────────────── -->
    <div>
      <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
        Flux des demandes de dépôt
      </h2>
      <div class="grid grid-cols-2 gap-4">

        <!-- En attente d'assignation -->
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-amber-700">En attente d'assignation</span>
            <span class="text-2xl"></span>
          </div>
          <p class="text-4xl font-bold text-amber-800">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.demandes.en_attente }}</span>
          </p>
          <RouterLink
            to="/admin/assignation"
            class="text-xs text-amber-500 hover:text-amber-700 mt-2 inline-block transition"
          >
            Aller aux assignations →
          </RouterLink>
        </div>

        <!-- Traitées par les gestionnaires -->
        <div class="bg-teal-50 border border-teal-100 rounded-xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-teal-700">Traitées par les gestionnaires</span>
            <span class="text-2xl"></span>
          </div>
          <p class="text-4xl font-bold text-teal-800">
            <span v-if="loading" class="opacity-40">—</span>
            <span v-else>{{ stats.demandes.traitees_gestionnaires }}</span>
          </p>
          <RouterLink
            to="/admin/demandes-traitees"
            class="text-xs text-teal-500 hover:text-teal-700 mt-2 inline-block transition"
          >
            Voir les demandes traitées →
          </RouterLink>
        </div>

      </div>
    </div>

    <!-- ── SECTION 4 : Accès rapides ──────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">Accès rapides</h2>
      <div class="flex flex-wrap gap-3">
        <RouterLink
          to="/admin/utilisateurs/nouveau"
          class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition"
        >
          + Créer un utilisateur
        </RouterLink>
        <RouterLink
          to="/admin/assignation"
          class="border border-[#042C53] text-[#042C53] text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition"
        >
          Assigner une demande
        </RouterLink>
        <RouterLink
          to="/admin/demandes-traitees"
          class="border border-[#042C53] text-[#042C53] text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition"
        >
          Voir les demandes traitées
        </RouterLink>
        <RouterLink
          to="/admin/utilisateurs"
          class="border border-gray-300 text-gray-600 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition"
        >
          Tous les utilisateurs
        </RouterLink>
      </div>
    </div>

  </div>
</template>