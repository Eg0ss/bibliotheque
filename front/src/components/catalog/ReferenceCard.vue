<script setup>
import { RouterLink } from 'vue-router'
import StatusBadge from '@/components/common/StatusBadge.vue'

const props = defineProps({
  reference: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <RouterLink
    :to="`/catalogue/${reference.id}`"
    class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:shadow-sm"
  >
    <div class="aspect-[3/4] overflow-hidden bg-slate-100">
      <img :src="reference.cover" :alt="reference.title" class="h-full w-full object-cover transition group-hover:scale-[1.02]" loading="lazy" />
    </div>
    <div class="flex flex-1 flex-col gap-2 p-4">
      <div class="flex flex-wrap gap-1.5">
        <StatusBadge tone="info">{{ reference.category }}</StatusBadge>
        <StatusBadge tone="gold">{{ reference.type }}</StatusBadge>
      </div>
      <h3 class="line-clamp-2 text-sm font-semibold text-slate-800">{{ reference.title }}</h3>
      <p class="text-xs text-slate-500">{{ reference.author }} · {{ reference.year }}</p>

      <!-- Bloc des 3 compteurs : vues, téléchargements, likes -->
      <div class="mt-auto flex items-center gap-3 text-xs text-slate-500">
        <!-- Vues -->
        <span class="inline-flex items-center gap-1">
          <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          {{ reference.views ?? 0 }}
        </span>

        <!-- Téléchargements -->
        <span class="inline-flex items-center gap-1">
          <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
          </svg>
          {{ reference.downloads ?? 0 }}
        </span>

        <!-- Likes -->
        <span class="inline-flex items-center gap-1">
          <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
          </svg>
          {{ reference.likes ?? 0 }}
        </span>
      </div>
    </div>
  </RouterLink>
</template>