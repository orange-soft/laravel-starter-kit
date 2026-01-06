<template>
  <Head :title="title" />

  <AppShell>
    <template #sidebar>
      <Sidebar />
    </template>

    <template #topbar>
      <Topbar :title="title" />
    </template>

    <template #default>
      <!-- Breadcrumbs (hidden on Dashboard since it's the root) -->
      <nav v-if="breadcrumbs?.length || (title && title !== 'Dashboard')" class="mb-4">
        <ol class="flex items-center gap-1 font-body-small text-surface-500 list-none p-0">
          <li>
            <Link href="/dashboard" class="hover:text-surface-900 transition-colors">
              Dashboard
            </Link>
          </li>
          <template v-if="breadcrumbs?.length">
            <template v-for="(crumb, index) in breadcrumbs" :key="index">
              <li class="text-surface-400">/</li>
              <li>
                <Link v-if="crumb.href" :href="crumb.href"
                  class="hover:text-surface-900 transition-colors">
                  {{ crumb.label }}
                </Link>
                <span v-else class="text-surface-900">{{ crumb.label }}</span>
              </li>
            </template>
          </template>
          <template v-else-if="title">
            <li class="text-surface-400">/</li>
            <li class="text-surface-900">{{ title }}</li>
          </template>
        </ol>
      </nav>

      <slot />
    </template>
  </AppShell>

  <Toast />
  <ConfirmDialog />
</template>

<script setup>
import AppShell from '@/components/AppShell.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Sidebar from '@/components/Sidebar.vue';
import Toast from '@/components/Toast.vue';
import Topbar from '@/components/Topbar.vue';
import {Head, Link} from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    default: '',
  },
  breadcrumbs: {
    type: Array,
    default: null,
  },
});
</script>
