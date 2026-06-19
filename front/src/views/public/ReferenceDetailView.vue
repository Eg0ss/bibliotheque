<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const referenceId = route.params.id
</script>

<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold text-[#1e3a5f] mb-4">Détails de la référence #{{ referenceId }}</h1>
    <p class="text-slate-600">Page en cours de développement...</p>
  </div>
</template>
