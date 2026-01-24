<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Button from 'primevue/button';

const toast = useToast();
const props = defineProps(['share']);

const form = useForm({
  name: '',
});

const validationStatus = ref({
  name: null,
});

const validateForm = () => {
  validationStatus.value.name = form.name ? 'success' : 'error';
  return form.name;
};

const submit = () => {
  if (validateForm()) {
    form.post(route(props.share.prefix + '.store'), {
      onSuccess: () => {
        toast.success("Permission berhasil ditambahkan.");
      },
      onError: (errors) => {
        Object.values(errors).forEach(errorMessages => {
          toast.error(errorMessages);
        });
      },
    });
  } else {
    toast.warning("Mohon isi nama permission.");
  }
};
</script>

<template>
  <Head :title="`Tambah ${share.title}`" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Tambah {{ share.title }}</h2>
    </template>
    
    <div class="py-12">
      <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
        <Breadcrumb :crumbs="[
            { label: 'Pengaturan', route: 'settings.users.index' },
            { label: share.title, route: share.prefix + '.index' },
            { label: 'Tambah', route: share.prefix + '.create' }
        ]" />
        
        <form @submit.prevent="submit" class="max-w-2xl">
          <div class="space-y-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
            <h3 class="font-semibold mb-4 border-b pb-2">Informasi Permission</h3>
            
            <fwb-input 
                v-model="form.name" 
                placeholder="Contoh: users.create" 
                label="Nama Permission" 
                required 
                :validation-status="validationStatus.name" 
            />
            <p class="text-xs text-gray-500">Gunakan format <code>resource.action</code> untuk memudahkan pengelompokan.</p>
            
            <div class="mt-6 flex justify-end">
                 <Button type="submit" label="Simpan Data" icon="pi pi-save" :loading="form.processing" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
