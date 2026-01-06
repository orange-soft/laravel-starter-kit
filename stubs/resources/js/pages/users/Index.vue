<template>
  <AdminLayout title="Users" :breadcrumbs="[{ label: 'Users' }]">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1 max-w-sm">
          <IconField>
            <InputIcon class="pi pi-search" />
            <InputText v-model="search" placeholder="Search users..." fluid
              @keyup.enter="performSearch" />
          </IconField>
        </div>
        <Link :href="usersCreate.url()">
          <Button label="Add User" icon="pi pi-plus" />
        </Link>
      </div>

      <!-- Users table -->
      <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
        <DataTable :value="users.data" stripedRows>
          <Column field="name" header="Name">
            <template #body="{data}">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-surface-200 flex items-center justify-center">
                  <span class="font-body-small font-medium text-surface-600">
                    {{ getInitials(data.name) }}
                  </span>
                </div>
                <div>
                  <p class="font-body-default font-medium text-surface-900">{{ data.name }}</p>
                  <p class="font-body-small text-surface-500">{{ data.email }}</p>
                </div>
              </div>
            </template>
          </Column>
          <Column field="roles" header="Role">
            <template #body="{data}">
              <Tag v-if="data.roles?.length" :value="data.roles[0].name" severity="info" />
              <span v-else class="text-surface-400">No role</span>
            </template>
          </Column>
          <Column field="created_at" header="Created">
            <template #body="{data}">
              {{ formatDate(data.created_at) }}
            </template>
          </Column>
          <Column header="Actions" style="width: 100px">
            <template #body="{data}">
              <div class="flex items-center gap-2">
                <Link :href="usersEdit.url({user: data.uuid})">
                  <Button icon="pi pi-pencil" text rounded severity="secondary" />
                </Link>
                <Button icon="pi pi-trash" text rounded severity="danger"
                  @click="confirmDelete(data)" />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- Pagination -->
      <div v-if="users.last_page > 1" class="flex justify-center">
        <Paginator :rows="users.per_page" :totalRecords="users.total"
          :first="(users.current_page - 1) * users.per_page"
          @page="onPageChange" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import {create as usersCreate, edit as usersEdit, destroy as usersDestroy} from '@/actions/App/Http/Controllers/UserController';
import {Link, router} from '@inertiajs/vue3';
import {Button, Column, DataTable, IconField, InputIcon, InputText, Paginator, Tag, useConfirm} from 'primevue';
import {ref} from 'vue';

const props = defineProps({
  users: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const confirm = useConfirm();
const search = ref(props.filters.search || '');

function performSearch() {
  router.get('/users', {search: search.value}, {preserveState: true});
}

function onPageChange(event) {
  router.get('/users', {page: event.page + 1, search: search.value}, {preserveState: true});
}

function getInitials(name) {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function confirmDelete(user) {
  confirm.require({
    message: `Are you sure you want to delete ${user.name}?`,
    header: 'Delete User',
    icon: 'pi pi-exclamation-triangle',
    rejectClass: 'p-button-secondary p-button-outlined',
    acceptClass: 'p-button-danger',
    accept: () => {
      router.delete(usersDestroy.url({user: user.uuid}));
    },
  });
}
</script>
