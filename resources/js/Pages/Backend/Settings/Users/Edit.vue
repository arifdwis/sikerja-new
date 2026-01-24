<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';

const toast = useToast();
const props = defineProps(['user', 'roles', 'share']);

const roleOptions = props.roles.map(role => ({
    value: role.id,
    name: role.name
}));

// Setup initial role from user's current roles (assuming single role assignment logic for form, though user might have multiple)
const currentRole = props.user.roles && props.user.roles.length > 0 ? props.user.roles[0].id : '';

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  role_id: currentRole,
});

const validationStatus = ref({
  name: null,
  email: null,
  role_id: null,
});

const validateForm = () => {
  validationStatus.value.name = form.name ? 'success' : 'error';
  validationStatus.value.email = form.email ? 'success' : 'error';
  validationStatus.value.role_id = form.role_id ? 'success' : 'error';
  
  // Password is optional on edit
  
  return form.name && form.email && form.role_id;
};

const submit = () => {
  if (validateForm()) {
    form.put(route(props.share.prefix + '.update', props.user.id), {
      onSuccess: () => {
        toast.success("User berhasil diperbarui.");
      },
      onError: (errors) => {
        Object.values(errors).forEach(errorMessages => {
          toast.error(errorMessages);
        });
      },
    });
  } else {
    toast.warning("Mohon lengkapi data wajib.");
  }
};
</script>

<template>
  <Head :title="`Edit ${share.title}`" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Edit {{ share.title }}</h2>
    </template>
    
    <div class="py-12">
      <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
        <Breadcrumb :crumbs="[
            { label: 'Pengaturan', route: 'settings.users.index' },
            { label: share.title, route: share.prefix + '.index' },
            { label: 'Edit', route: share.prefix + '.edit', args: [user.id] }
        ]" />
        
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Form Data Pengguna -->
          <div class="md:col-span-2 space-y-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
            <h3 class="font-semibold mb-4 border-b pb-2">Informasi Akun</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <fwb-input 
                v-model="form.name" 
                placeholder="Nama Lengkap" 
                label="Nama Lengkap" 
                required 
                :validation-status="validationStatus.name" 
              />
              
              <fwb-input 
                v-model="form.email" 
                placeholder="Email Address" 
                label="Email" 
                type="email"
                required 
                :validation-status="validationStatus.email" 
              />
              
              <div class="col-span-1 md:col-span-2 mt-2">
                 <p class="text-sm text-gray-500 mb-2">Biarkan kosong jika tidak ingin mengubah password.</p>
              </div>

              <fwb-input 
                v-model="form.password" 
                placeholder="Password Baru (Opsional)" 
                label="Password Baru" 
                type="password" 
              />
              
              <fwb-input 
                v-model="form.password_confirmation" 
                placeholder="Konfirmasi Password Baru" 
                label="Konfirmasi Password Baru" 
                type="password" 
              />
            </div>
            
            <div class="mt-6 flex justify-end">
                 <Button type="submit" label="Simpan Perubahan" icon="pi pi-save" class="w-full md:w-auto" :loading="form.processing" />
            </div>
          </div>
          
          <!-- Select Role -->
          <div class="md:col-span-1 space-y-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100 h-fit">
            <h3 class="font-semibold mb-4 border-b pb-2">Role & Hak Akses</h3>
            
            <fwb-select 
                v-model="form.role_id" 
                :options="roleOptions" 
                label="Pilih Role" 
                required 
            />
            
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
              Mengubah role akan mengubah hak akses pengguna ini pada login berikutnya.
            </p>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
