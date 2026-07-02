<script setup>
/**
 * DashboardView.vue
 * Tableau de bord de l'espace utilisateur
 * Route : /mon-espace
 *
 * Affiche un récapitulatif dynamique des demandes de l'utilisateur :
 * total soumises, en attente, en cours de validation, publiées, rejetées
 */

import { onMounted }             from 'vue'
import { RouterLink }            from 'vue-router'
import { useDepotRequestStore }  from '@/stores/depotRequestStore'
import { useAuthStore }          from '@/stores/authStore'

const store     = useDepotRequestStore()
const authStore = useAuthStore()

onMounted(() => {
  // On charge les stats dès l'arrivée sur le tableau de bord
  store.fetchStats()
})

// Configuration des cartes — chaque carte correspond à un statut
const cards = [
  {
    key    : 'total',
    label  : 'Total soumises',
    bg     : 'bg-[#1e3a5f]',
    text   : 'text-white',
    sub    : 'text-white/70',
    link   : '/mon-espace/depots',
    linkLabel: 'Voir tout',
  },
  {
    key    : 'in_progress',
    label  : 'En cours de traitement',
    bg     : 'bg-amber-50',
    text   : 'text-amber-800',
    sub    : 'text-amber-500',
    link   : '/mon-espace/depots?status=in_progress',
    linkLabel: 'Voir',
  },
  {
    key    : 'published',
    label  : 'Publiées',
    bg     : 'bg-green-50',
    text   : 'text-green-800',
    sub    : 'text-green-500',
    link   : '/mon-espace/depots?status=published',
    linkLabel: 'Voir',
  },
  {
    key    : 'rejected',
    label  : 'Rejetées',
    bg     : 'bg-red-50',
    text   : 'text-red-800',
    sub    : 'text-red-500',
    link   : '/mon-espace/depots?status=rejected',
    linkLabel: 'Voir',
  },
]
</script>

<template>
  <div class="space-y-6">

    <!-- ── En-tête de bienvenue ──────────────────────────────────────── -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-[#1e3a5f]">
          Bonjour, {{ authStore.user?.name?.split(' ')[0] }} 
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Voici le récapitulatif de vos demandes de dépôt.
        </p>
      </div>

      <!-- Bouton accès rapide -->
      <RouterLink
        to="/mon-espace/depots/nouveau"
        class="flex items-center gap-2 bg-[#1e3a5f] text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-[#2d5a8e] transition"
      >
        <span>+</span> Nouvelle demande
      </RouterLink>
    </div>

    <!-- ── Cartes statistiques ───────────────────────────────────────── -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div
        v-for="card in cards"
        :key="card.key"
        class="rounded-xl p-5 shadow-sm border border-white/10 flex flex-col gap-3"
        :class="card.bg"
      >
        <!-- Icône + compteur -->
        <div class="flex items-start justify-between">
          <span class="text-3xl">{{ card.icon }}</span>

          <!-- Compteur animé — affiche le chiffre depuis store.stats -->
          <span
            class="text-4xl font-bold leading-none"
            :class="card.text"
          >
            <!-- Si les stats chargent encore, on affiche — -->
            {{ store.stats[card.key] ?? '—' }}
          </span>
        </div>

        <!-- Label + lien -->
        <div>
          <p class="text-sm font-semibold" :class="card.text">{{ card.label }}</p>
          <RouterLink
            :to="card.link"
            class="text-xs mt-0.5 hover:underline"
            :class="card.sub"
          >
            {{ card.linkLabel }} →
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- ── Détail des statuts intermédiaires ─────────────────────────── -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">
        Détail par étape de validation
      </h2>

      <div class="space-y-3">

        <!-- En attente d'assignation -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-gray-400 flex-shrink-0"></span>
            <span class="text-sm text-gray-600">En attente d'assignation</span>
          </div>
          <span class="text-sm font-semibold text-gray-800">
            {{ store.stats.pending }}
          </span>
        </div>

        <!-- Assignée à un gestionnaire -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-400 flex-shrink-0"></span>
            <span class="text-sm text-gray-600">Assignée à un gestionnaire</span>
          </div>
          <span class="text-sm font-semibold text-gray-800">
            {{ store.stats.assigned }}
          </span>
        </div>

        <!-- Validée par le gestionnaire -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 flex-shrink-0"></span>
            <span class="text-sm text-gray-600">Validée par le gestionnaire</span>
          </div>
          <span class="text-sm font-semibold text-gray-800">
            {{ store.stats.manager_approved }}
          </span>
        </div>

        <!-- Publiée -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-green-400 flex-shrink-0"></span>
            <span class="text-sm text-gray-600">Publiée dans le catalogue</span>
          </div>
          <span class="text-sm font-semibold text-gray-800">
            {{ store.stats.published }}
          </span>
        </div>

        <!-- Rejetée -->
        <div class="flex items-center justify-between py-2">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-red-400 flex-shrink-0"></span>
            <span class="text-sm text-gray-600">Rejetée</span>
          </div>
          <span class="text-sm font-semibold text-gray-800">
            {{ store.stats.rejected }}
          </span>
        </div>

      </div>
    </div>

    <!-- ── Barre de progression visuelle ─────────────────────────────── -->
    <div v-if="store.stats.total > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Répartition globale</h2>

      <!-- Barre segmentée -->
      <div class="flex h-3 rounded-full overflow-hidden gap-px">
        <div
          v-if="store.stats.pending > 0"
          class="bg-gray-400 transition-all"
          :style="{ width: (store.stats.pending / store.stats.total * 100) + '%' }"
          :title="`En attente : ${store.stats.pending}`"
        ></div>
        <div
          v-if="store.stats.assigned > 0"
          class="bg-blue-400 transition-all"
          :style="{ width: (store.stats.assigned / store.stats.total * 100) + '%' }"
          :title="`Assignée : ${store.stats.assigned}`"
        ></div>
        <div
          v-if="store.stats.manager_approved > 0"
          class="bg-indigo-400 transition-all"
          :style="{ width: (store.stats.manager_approved / store.stats.total * 100) + '%' }"
          :title="`Validée gestionnaire : ${store.stats.manager_approved}`"
        ></div>
        <div
          v-if="store.stats.published > 0"
          class="bg-green-400 transition-all"
          :style="{ width: (store.stats.published / store.stats.total * 100) + '%' }"
          :title="`Publiée : ${store.stats.published}`"
        ></div>
        <div
          v-if="store.stats.rejected > 0"
          class="bg-red-400 transition-all"
          :style="{ width: (store.stats.rejected / store.stats.total * 100) + '%' }"
          :title="`Rejetée : ${store.stats.rejected}`"
        ></div>
      </div>

      <!-- Légende -->
      <div class="flex flex-wrap gap-4 mt-3 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span> En attente</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Assignée</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span> Validée</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Publiée</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Rejetée</span>
      </div>
    </div>

    <!-- Message si aucune demande encore -->
    <div v-if="store.stats.total === 0 && !store.loading"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
      <div class="text-5xl mb-3"></div>
      <p class="text-gray-500 font-medium mb-1">Aucune demande pour le moment</p>
      <p class="text-sm text-gray-400 mb-4">Commencez par soumettre votre première référence.</p>
      <RouterLink
        to="/mon-espace/depots/nouveau"
        class="inline-flex items-center gap-2 bg-[#1e3a5f] text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-[#2d5a8e] transition"
      >
        + Soumettre une demande
      </RouterLink>
    </div>

  </div>
</template>