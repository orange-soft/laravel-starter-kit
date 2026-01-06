<template>
  <AdminLayout title="Profile">
    <div class="max-w-2xl space-y-6">
      <!-- Profile Information -->
      <div class="bg-white rounded-xl shadow-sm border border-surface-200 p-6">
        <h2 class="font-heading-5 text-surface-900 mb-4">Profile Information</h2>
        <p class="font-body-small text-surface-600 mb-6">
          Update your account's profile information and email address.
        </p>

        <form class="space-y-6" @submit.prevent="updateProfile">
          <div>
            <label for="name" class="block font-body-small font-medium text-surface-700 mb-1">
              Name
            </label>
            <InputText id="name" v-model="profileForm.name" fluid
              :invalid="!!profileForm.errors.name" />
            <FormError :message="profileForm.errors.name" />
          </div>

          <div>
            <label for="email" class="block font-body-small font-medium text-surface-700 mb-1">
              Email
            </label>
            <InputText id="email" v-model="profileForm.email" type="email" fluid
              :invalid="!!profileForm.errors.email" />
            <FormError :message="profileForm.errors.email" />
          </div>

          <div>
            <Button type="submit" label="Save" :loading="profileForm.processing" />
          </div>
        </form>
      </div>

      <!-- Update Password -->
      <div class="bg-white rounded-xl shadow-sm border border-surface-200 p-6">
        <h2 class="font-heading-5 text-surface-900 mb-4">Update Password</h2>
        <p class="font-body-small text-surface-600 mb-6">
          Ensure your account is using a long, random password to stay secure.
        </p>

        <form class="space-y-6" @submit.prevent="updatePassword">
          <div>
            <label for="current_password"
              class="block font-body-small font-medium text-surface-700 mb-1">
              Current Password
            </label>
            <Password id="current_password" v-model="passwordForm.current_password"
              :feedback="false" fluid toggleMask
              :invalid="!!passwordForm.errors.current_password" />
            <FormError :message="passwordForm.errors.current_password" />
          </div>

          <div>
            <label for="new_password" class="block font-body-small font-medium text-surface-700 mb-1">
              New Password
            </label>
            <Password id="new_password" v-model="passwordForm.password"
              :feedback="true" fluid toggleMask :invalid="!!passwordForm.errors.password" />
            <FormError :message="passwordForm.errors.password" />
          </div>

          <div>
            <label for="password_confirmation"
              class="block font-body-small font-medium text-surface-700 mb-1">
              Confirm Password
            </label>
            <Password id="password_confirmation" v-model="passwordForm.password_confirmation"
              :feedback="false" fluid toggleMask />
          </div>

          <div>
            <Button type="submit" label="Update Password" :loading="passwordForm.processing" />
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import FormError from '@/components/FormError.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {update as profileUpdate} from '@/actions/App/Http/Controllers/ProfileController';
import {useForm} from '@inertiajs/vue3';
import {Button, InputText, Password} from 'primevue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
});

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
});

const passwordForm = useForm({
  name: props.user.name,
  email: props.user.email,
  current_password: '',
  password: '',
  password_confirmation: '',
});

function updateProfile() {
  profileForm.put(profileUpdate.url());
}

function updatePassword() {
  passwordForm.put(profileUpdate.url(), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.current_password = '';
      passwordForm.password = '';
      passwordForm.password_confirmation = '';
    },
  });
}
</script>
