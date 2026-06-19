<script setup>
import { useAuthStore } from '@/stores/authStore'
const authStore = useAuthStore()
</script>

<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold text-[#1e3a5f] mb-4">Mon profil</h1>
    <div class="bg-white p-6 rounded-lg border border-slate-200">
      <p class="text-lg font-semibold">{{ authStore.user?.name }}</p>
      <p class="text-slate-600">{{ authStore.user?.email }}</p>
      <p class="text-sm text-[#c8a45a] capitalize mt-2">{{ authStore.role }}</p>
    </div>
  </div>
</template>
