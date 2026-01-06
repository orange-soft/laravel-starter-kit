<template>
  <div class="flex-1 flex items-center justify-between">
    <h1 class="font-heading-4 text-surface-900">{{ title }}</h1>

    <div class="flex items-center gap-4">
      <!-- Notifications placeholder -->
      <button type="button"
        class="p-2 text-surface-500 hover:text-surface-900 rounded-lg hover:bg-surface-100">
        <i class="pi pi-bell text-xl" />
      </button>

      <!-- User menu -->
      <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
      <button type="button"
        class="flex items-center gap-2 p-2 text-surface-500 hover:text-surface-900 rounded-lg hover:bg-surface-100"
        @click="toggleUserMenu">
        <span class="hidden sm:block font-body-small">{{ user?.name }}</span>
        <i class="pi pi-chevron-down text-sm" />
      </button>
    </div>
  </div>
</template>

<script setup>
import {router, usePage} from '@inertiajs/vue3';
import {Menu} from 'primevue';
import {computed, ref} from 'vue';

defineProps({
  title: {
    type: String,
    default: '',
  },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const userMenu = ref();

function toggleUserMenu(event) {
  userMenu.value.toggle(event);
}

const userMenuItems = [
  {
    label: 'Profile',
    icon: 'pi pi-user',
    command: () => router.visit('/profile'),
  },
  {
    separator: true,
  },
  {
    label: 'Logout',
    icon: 'pi pi-sign-out',
    command: () => router.post('/logout'),
  },
];
</script>
