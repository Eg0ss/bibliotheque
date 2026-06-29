<script setup>
import { onMounted }            from 'vue'
import { useGestionnaireStore } from '@/stores/gestionnaireStore'

const store = useGestionnaireStore()

onMounted(() => store.fetchMyValidations())

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  })
}

const decisionConfig = {
  manager_approved: { label: 'Accepté',  bg: 'bg-green-100', text: 'text-green-700' },
  manager_rejected: { label: 'Rejeté',   bg: 'bg-red-100',   text: 'text-red-700'   },
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Mes validations</h1>
      <p class="text-sm text-gray-500 mt-1">
        Historique de vos décisions sur les documents assignés.
      </p>
    </div>

    <div v-if="store.loading" class="text-center py-16 text-gray-400">
      ⏳ Chargement...
    </div>

    <div v-else-if="store.myValidations.length === 0"
      class="bg-white rounded-xl border border-gray-100 p-12 text-center">
      <div class="text-5xl mb-3">📋</div>
      <p class="text-gray-400">Vous n'avez pas encore effectué de validation.</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="step in store.myValidations"
        :key="step.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <h2 class="font-semibold text-[#042C53] truncate">
              {{ step.depot_request?.reference?.title ?? '—' }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
              ✍️ {{ step.depot_request?.reference?.author ?? '—' }}
            </p>
            <div class="flex gap-2 mt-2">
              <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
                {{ step.depot_request?.reference?.type?.name ?? '—' }}
              </span>
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                {{ step.depot_request?.reference?.category?.name ?? '—' }}
              </span>
            </div>
            <!-- Commentaire si présent -->
            <p v-if="step.comment"
              class="mt-2 text-xs text-gray-500 italic border-l-2 border-gray-200 pl-2">
              "{{ step.comment }}"
            </p>
          </div>
          <div class="flex flex-col items-end gap-1 flex-shrink-0">
            <span class="px-3 py-1 rounded-full text-xs font-medium"
              :class="[
                decisionConfig[step.decision]?.bg ?? 'bg-gray-100',
                decisionConfig[step.decision]?.text ?? 'text-gray-600'
              ]">
              {{ decisionConfig[step.decision]?.label ?? step.decision }}
            </span>
            <span class="text-xs text-gray-400">{{ formatDate(step.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>