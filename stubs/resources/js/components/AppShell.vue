<template>
  <div class="min-h-screen flex bg-surface-50">
    <!-- Sidebar -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-surface-900">
      <slot name="sidebar" />
    </aside>

    <!-- Mobile sidebar backdrop -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 lg:hidden"
      @click="sidebarOpen = false">
      <div class="fixed inset-0 bg-surface-600/75" />
    </div>

    <!-- Mobile sidebar -->
    <aside v-if="sidebarOpen"
      class="fixed inset-y-0 left-0 z-50 w-64 bg-surface-900 lg:hidden">
      <slot name="sidebar" />
    </aside>

    <!-- Main content -->
    <div class="flex-1 lg:pl-64">
      <!-- Topbar -->
      <header class="sticky top-0 z-30 bg-white border-b border-surface-200">
        <div class="flex items-center gap-4 px-4 sm:px-6 lg:px-8 h-16">
          <!-- Mobile menu button -->
          <button type="button" class="lg:hidden -ml-2 p-2 text-surface-500 hover:text-surface-900"
            @click="sidebarOpen = true">
            <i class="pi pi-bars text-xl" />
          </button>

          <slot name="topbar" />
        </div>
      </header>

      <!-- Page content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import {ref} from 'vue';

const sidebarOpen = ref(false);
</script>
