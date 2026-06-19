<script setup>
import NavBar from '@/components/common/NavBar.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#f8f9fb]">
    <NavBar />
    <main class="flex-1">
      <RouterView />
    </main>
  </div>
</template>
