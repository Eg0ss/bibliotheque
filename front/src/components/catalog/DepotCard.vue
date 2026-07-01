<script setup>
import StatusBadge from '@/components/common/StatusBadge.vue'

const props = defineProps({
    request: { type: Object, required: true }
})

const statusConfig = {
    pending: { label: 'En attente', },
    assigned: { label: 'Assignée', },
    en_verification: { label: 'En vérification', },
    manager_approved: { label: 'Validée', },
    acceptee_partiel: { label: 'Acceptée partiellement', },
    rejetee_partiel: { label: 'Retournée', },
    resubmitted: { label: 'Renvoyée au gestionnaire', },
    soumise_publication: { label: 'Soumise pour publication', },
    published: { label: 'Publiée', },
    rejected: { label: 'Rejetée', },
    rejetee_definitif: { label: 'Rejetée définitivement', },
    acceptee_definitif: { label: 'Publiée', },
}

//
function getStatus(s) {
    return statusConfig[s] ?? { label: s, icon: '❓' }
}

// ─── Fonction pour formater la date d'une référence ──────────────────────────────────────────────────────
function formatYear(ref) {
    return ref?.publication_year ?? '—'
}

// ─── Fonction pour récupérer l'URL d'un fichier ──────────────────────────────────────────────────────
const getCoverUrl = (path) => {
    if (!path) return null;
    return `http://localhost:8000/storage/${path}`;
}
</script>

<template>
    <div
        class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:shadow-md">

        <!-- Couverture -->
        <div class="aspect-[3/4] overflow-hidden bg-slate-100 relative">
            <img v-if="request.reference?.cover_image" :src="getCoverUrl(request.reference.cover_image)"
                :alt="request.reference.title" class="h-full w-full object-cover transition group-hover:scale-[1.02]"
                loading="lazy" />
            <!-- Placeholder sans couverture -->
            <div v-else
                class="h-full w-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-slate-100 to-slate-200">
                <span class="text-4xl">📄</span>
                <span class="text-xs text-slate-400 font-medium px-2 text-center line-clamp-2">
                    {{ request.reference?.title }}
                </span>
            </div>

            <!-- Badge statut en overlay -->
            <div class="absolute top-2 right-2">
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold shadow-sm backdrop-blur-sm"
                    :class="{
                        'bg-green-100/90 text-green-700': ['published', 'acceptee_definitif', 'manager_approved'].includes(request.status),
                        'bg-red-100/90 text-red-700': ['rejected', 'rejetee_definitif'].includes(request.status),
                        'bg-amber-100/90 text-amber-700': ['en_verification', 'rejetee_partiel', 'resubmitted', 'soumise_publication'].includes(request.status),
                        'bg-blue-100/90 text-blue-700': request.status === 'assigned',
                        'bg-slate-100/90 text-slate-600': request.status === 'pending',
                    }">
                    {{ getStatus(request.status).icon }}
                    {{ getStatus(request.status).label }}
                </span>
            </div>
        </div>

        <!-- Infos -->
        <div class="flex flex-1 flex-col gap-2 p-4">
            <div class="flex flex-wrap gap-1.5">
                <StatusBadge tone="info">{{ request.reference?.category?.name ?? '—' }}</StatusBadge>
                <StatusBadge tone="gold">{{ request.reference?.type?.name ?? '—' }}</StatusBadge>
            </div>

            <h3 class="line-clamp-2 text-sm font-semibold text-slate-800 leading-snug">
                {{ request.reference?.title ?? '—' }}
            </h3>

            <p class="text-xs text-slate-500">
                {{ request.reference?.author ?? '—' }} · {{ formatYear(request.reference) }}
            </p>

            <p class="mt-auto text-[10px] text-slate-400">
                Soumis le {{ new Date(request.created_at).toLocaleDateString('fr-FR', {
                    day: '2-digit', month: 'short',
                    year: 'numeric' }) }}
            </p>

            <!-- Motif de rejet -->
            <div v-if="request.rejection_reason"
                class="mt-1 px-2 py-1.5 bg-red-50 border border-red-100 rounded-lg text-[11px] text-red-600 line-clamp-2">
                💬 {{ request.rejection_reason }}
            </div>
        </div>
    </div>
</template>