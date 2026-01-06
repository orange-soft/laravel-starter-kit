<template>
  <div class="flex flex-col h-full">
    <!-- Logo -->
    <div class="flex items-center h-16 px-6 shrink-0">
      <span class="font-heading-4 text-white">{{ appName }}</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
      <Link v-for="item in navigation" :key="item.name" :href="item.href"
        :class="[
          item.current
            ? 'bg-surface-800 text-white'
            : 'text-surface-300 hover:bg-surface-800 hover:text-white',
          'group flex items-center gap-3 px-3 py-2 rounded-lg font-body-default transition-colors',
        ]">
        <i :class="[item.icon, 'text-lg']" />
        {{ item.name }}
      </Link>
    </nav>
  </div>
</template>

<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {computed} from 'vue';

const page = usePage();

const appName = computed(() => page.props.appName || 'Laravel');

const navigation = computed(() => {
  const currentUrl = page.url;

  return [
    {
      name: 'Dashboard',
      href: '/dashboard',
      icon: 'pi pi-home',
      current: currentUrl === '/dashboard',
    },
    {
      name: 'Users',
      href: '/users',
      icon: 'pi pi-users',
      current: currentUrl.startsWith('/users'),
    },
    {
      name: 'Profile',
      href: '/profile',
      icon: 'pi pi-user',
      current: currentUrl === '/profile',
    },
  ];
});
</script>
