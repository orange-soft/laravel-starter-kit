<template>
  <AdminLayout title="Add User"
    :breadcrumbs="[{ label: 'Users', href: usersIndex.url() }, { label: 'Add User' }]">
    <div class="max-w-2xl">
      <div class="bg-white rounded-xl shadow-sm border border-surface-200 p-6">
        <form class="space-y-6" @submit.prevent="submit">
          <div>
            <label for="name" class="block font-body-small font-medium text-surface-700 mb-1">
              Name
            </label>
            <InputText id="name" v-model="form.name" fluid
              :invalid="!!form.errors.name" />
            <FormError :message="form.errors.name" />
          </div>

          <div>
            <label for="email" class="block font-body-small font-medium text-surface-700 mb-1">
              Email
            </label>
            <InputText id="email" v-model="form.email" type="email" fluid
              :invalid="!!form.errors.email" />
            <FormError :message="form.errors.email" />
          </div>

          <div>
            <label for="role" class="block font-body-small font-medium text-surface-700 mb-1">
              Role
            </label>
            <Select id="role" v-model="form.role" :options="roleOptions"
              optionLabel="label" optionValue="value" fluid :invalid="!!form.errors.role" />
            <FormError :message="form.errors.role" />
          </div>

          <div class="bg-surface-50 rounded-lg p-4">
            <p class="font-body-small text-surface-600">
              <i class="pi pi-info-circle mr-2" />
              A temporary password will be generated and the user will be required to change it on first login.
            </p>
          </div>

          <div class="flex items-center gap-4 pt-4">
            <Button type="submit" label="Create User" :loading="form.processing" />
            <Link :href="usersIndex.url()">
              <Button type="button" label="Cancel" severity="secondary" outlined />
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import FormError from '@/components/FormError.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {index as usersIndex, store as usersStore} from '@/actions/App/Http/Controllers/UserController';
import {Link, useForm} from '@inertiajs/vue3';
import {Button, InputText, Select} from 'primevue';
import {computed} from 'vue';

const props = defineProps({
  roles: {
    type: Array,
    required: true,
  },
});

const roleOptions = computed(() =>
  props.roles.map((role) => ({
    label: role.replace(/-/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase()),
    value: role,
  }))
);

const form = useForm({
  name: '',
  email: '',
  role: '',
});

function submit() {
  form.post(usersStore.url());
}
</script>
