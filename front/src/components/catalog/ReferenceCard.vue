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
      <div class="mt-auto flex items-center gap-1 text-xs text-slate-500">
        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        {{ reference.downloads }}
      </div>
    </div>
  </RouterLink>
</template>
