<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Button from 'primevue/button';

const toast = useToast();
const props = defineProps(['share', 'permission']);

const form = useForm({
  name: props.permission.name,
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
    form.put(route(props.share.prefix + '.update', props.permission.id), {
      onSuccess: () => {
        toast.success("Permission berhasil diperbarui.");
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
            { label: 'Edit', route: share.prefix + '.edit', args: [permission.id] }
        ]" />
        
        <form @submit.prevent="submit" class="max-w-2xl">
          <div class="space-y-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
            <h3 class="font-semibold mb-4 border-b pb-2">Edit Permission</h3>
            
            <fwb-input 
                v-model="form.name" 
                placeholder="Contoh: users.create" 
                label="Nama Permission" 
                required 
                :validation-status="validationStatus.name" 
            />
            
            <div class="mt-6 flex justify-end">
                 <Button type="submit" label="Simpan Perubahan" icon="pi pi-save" :loading="form.processing" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
