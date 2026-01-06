<template>
  <AdminLayout :title="`Edit ${user.name}`"
    :breadcrumbs="[{ label: 'Users', href: usersIndex.url() }, { label: user.name }]">
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

          <div class="flex items-center gap-4 pt-4">
            <Button type="submit" label="Update User" :loading="form.processing" />
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
import {index as usersIndex, update as usersUpdate} from '@/actions/App/Http/Controllers/UserController';
import {Link, useForm} from '@inertiajs/vue3';
import {Button, InputText, Select} from 'primevue';
import {computed} from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
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
  name: props.user.name,
  email: props.user.email,
  role: props.user.roles?.[0]?.name || '',
});

function submit() {
  form.put(usersUpdate.url({user: props.user.uuid}));
}
</script>
