<template>
  <AdminLayout title="Dashboard">
    <div class="grid gap-6">
      <!-- Welcome card -->
      <div class="bg-white rounded-xl shadow-sm border border-surface-200 p-6">
        <h2 class="font-heading-4 text-surface-900 mb-2">Welcome back, {{ auth.user?.name }}!</h2>
        <p class="font-body-default text-surface-600">
          Here's what's happening with your application today.
        </p>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="stat in stats" :key="stat.name"
          class="bg-white rounded-xl shadow-sm border border-surface-200 p-6">
          <div class="flex items-center gap-4">
            <div :class="[stat.bgColor, 'p-3 rounded-lg']">
              <i :class="[stat.icon, stat.iconColor, 'text-xl']" />
            </div>
            <div>
              <p class="font-caption text-surface-500 uppercase tracking-wide">{{ stat.name }}</p>
              <p class="font-heading-4 text-surface-900">{{ stat.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="bg-white rounded-xl shadow-sm border border-surface-200 p-6">
        <h3 class="font-heading-5 text-surface-900 mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
          <Button label="Manage Users" icon="pi pi-users" @click="$inertia.visit('/users')" />
          <Button label="View Profile" icon="pi pi-user" severity="secondary"
            @click="$inertia.visit('/profile')" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import {usePage} from '@inertiajs/vue3';
import {Button} from 'primevue';
import {computed} from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);

const stats = [
  {
    name: 'Total Users',
    value: '1',
    icon: 'pi pi-users',
    iconColor: 'text-blue-600',
    bgColor: 'bg-blue-100',
  },
  {
    name: 'Active Sessions',
    value: '1',
    icon: 'pi pi-desktop',
    iconColor: 'text-green-600',
    bgColor: 'bg-green-100',
  },
  {
    name: 'Pending Tasks',
    value: '0',
    icon: 'pi pi-list-check',
    iconColor: 'text-amber-600',
    bgColor: 'bg-amber-100',
  },
  {
    name: 'System Status',
    value: 'OK',
    icon: 'pi pi-check-circle',
    iconColor: 'text-emerald-600',
    bgColor: 'bg-emerald-100',
  },
];
</script>
