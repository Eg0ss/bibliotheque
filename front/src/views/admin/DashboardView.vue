<!-- Correspond à GET /api/admin/stats (à implémenter plus tard) -->
<template>
  <div>
    <h1 class="text-2xl font-bold text-[#042C53] mb-6">Tableau de bord</h1>

    <!-- Cards de statistiques (données statiques pour l'instant) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Utilisateurs inscrits</p>
        <p class="text-3xl font-bold text-[#042C53] mt-1">—</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Documents publiés</p>
        <p class="text-3xl font-bold text-[#042C53] mt-1">—</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <p class="text-sm text-gray-500">Demandes en attente</p>
        <p class="text-3xl font-bold text-[#042C53] mt-1">—</p>
      </div>
    </div>

    <!-- Accès rapides -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="text-base font-semibold text-gray-700 mb-4">Accès rapides</h2>
      <div class="flex flex-wrap gap-3">
        <RouterLink
          to="/admin/utilisateurs/nouveau"
          class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition"
        >
          + Créer un utilisateur
        </RouterLink>
        <RouterLink
          to="/admin/utilisateurs"
          class="border border-[#042C53] text-[#042C53] text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition"
        >
          Voir tous les utilisateurs
        </RouterLink>
      </div>
    </div>
  </div>
</template>