<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import AutoComplete from 'primevue/autocomplete';
import axios from 'axios';
import InputText from 'primevue/inputtext';

const toast = useToast();
const props = defineProps(['roles', 'share']);

// Map roles to format needed by select
const roleOptions = props.roles.map(role => ({
    value: role.id,
    name: role.name
}));

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '',
  uid: null, // Field khusus untuk SSO User ID
});

const validationStatus = ref({
  name: null,
  email: null,
  password: null,
  password_confirmation: null,
  role_id: null,
});

// SSO Search Logic
const ssoUsers = ref([]);
const selectedSsoUser = ref(null);
const isSsoLocked = ref(false);

const searchSso = async (event) => {
    try {
        const res = await axios.get(route('settings.users.search-sso'), {
            params: { query: event.query }
        });
        
        let results = res.data;
        
        // Handle case where API returns array of JSON strings instead of objects
        if (Array.isArray(results) && results.length > 0) {
            // Check first item
            if (typeof results[0] === 'string' && results[0].trim().startsWith('{')) {
                try {
                     results = results.map(item => JSON.parse(item));
                } catch (e) {
                    console.error("Failed to parse SSO result", e);
                }
            }
        }
        
        ssoUsers.value = results.slice(0, 50);
    } catch (e) {
        ssoUsers.value = [];
    }
};

const onSsoSelect = (event) => {
    const item = event.value;
    let name = item.text || item.name;
    let email = item.email || '';
    let uid = item.uid || item.id;

    // Handle Legacy SSO Format: uid|||email|||name
    if (typeof item.id === 'string' && item.id.includes('|||')) {
        const parts = item.id.split('|||');
        if (parts.length >= 3) {
            uid = parts[0];
            email = parts[1];
            name = parts[2];
        }
    }

    form.name = name;
    form.email = email;
    form.uid = uid;
    form.password = 'Substituted by SSO';
    form.password_confirmation = 'Substituted by SSO';
    isSsoLocked.value = true;
    toast.info("Data user SSO berhasil dimuat.");
};

const clearSso = () => {
    selectedSsoUser.value = null;
    isSsoLocked.value = false;
    form.uid = null;
    form.name = '';
    form.email = '';
    form.password = '';
    form.password_confirmation = '';
};

const validateForm = () => {
  validationStatus.value.name = form.name ? 'success' : 'error';
  validationStatus.value.email = form.email ? 'success' : 'error';
  // Password not needed for validation visual if SSO
  validationStatus.value.role_id = form.role_id ? 'success' : 'error';
  
  return form.name && form.email && (isSsoLocked.value || (form.password && form.password_confirmation)) && form.role_id;
};

const submit = () => {
  if (validateForm()) {
    form.post(route(props.share.prefix + '.store'), {
      onSuccess: () => {
        toast.success("User berhasil ditambahkan.");
      },
      onError: (errors) => {
        Object.values(errors).forEach(errorMessages => {
          toast.error(errorMessages);
        });
      },
    });
  } else {
    toast.warning("Mohon lengkapi semua kolom dengan benar.");
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
        
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Form Data Pengguna -->
          <div class="md:col-span-2 space-y-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
            <h3 class="font-semibold mb-4 border-b pb-2 flex justify-between items-center">
                <span>Informasi Akun</span>
                <span v-if="isSsoLocked" class="text-xs font-bold text-white bg-blue-600 px-2 py-1 rounded">SSO MODE</span>
            </h3>

            <!-- SSO Search Box -->
            <div class="p-4 mb-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cari User SSO (Samarinda SSO)</label>
                <div class="flex gap-2">
                    <AutoComplete 
                        v-model="selectedSsoUser" 
                        :suggestions="ssoUsers" 
                        @complete="searchSso" 
                        @item-select="onSsoSelect" 
                        field="text" 
                        placeholder="Ketik Nama / NIP / Email..." 
                        class="w-full"
                        inputClass="w-full"
                        :disabled="isSsoLocked"
                    >
                        <template #item="slotProps">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{ slotProps.item.text || slotProps.item.name }}</span>
                                <span v-if="slotProps.item.email" class="text-xs text-gray-500">{{ slotProps.item.email }}</span>
                            </div>
                        </template>
                    </AutoComplete>
                    <Button v-if="isSsoLocked" icon="pi pi-times" severity="secondary" @click="clearSso" v-tooltip="'Reset Form'" />
                </div>
                <small class="text-gray-500">Pilih user dari list untuk mengisi otomatis data.</small>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                  <InputText v-model="form.name" :disabled="isSsoLocked" class="w-full" placeholder="Nama Lengkap" />
              </div>
              
              <div class="flex flex-col gap-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                  <InputText v-model="form.email" :disabled="isSsoLocked" class="w-full" placeholder="Email Address" type="email" />
              </div>
              
              <div v-if="!isSsoLocked" class="flex flex-col gap-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                  <InputText v-model="form.password" class="w-full" placeholder="Password" type="password" />
              </div>
              
              <div v-if="!isSsoLocked" class="flex flex-col gap-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                  <InputText v-model="form.password_confirmation" class="w-full" placeholder="Konfirmasi Password" type="password" />
              </div>

               <div v-if="isSsoLocked" class="col-span-2 p-3 bg-blue-50 text-blue-700 text-sm rounded border border-blue-200">
                  <Icon icon="solar:info-circle-bold" class="inline w-4 h-4 mr-1"/>
                  Password tidak diperlukan untuk user SSO. User akan login menggunakan akun SSO mereka.
              </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                 <Button type="submit" label="Simpan Data" icon="pi pi-save" class="w-full md:w-auto" :loading="form.processing" />
            </div>
          </div>
          
          <!-- Select Role -->
          <div class="md:col-span-1 space-y-4 bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100 h-fit">
            <h3 class="font-semibold mb-4 border-b pb-2">Role & Hak Akses</h3>
            
            <fwb-select 
                v-model="form.role_id" 
                :options="roleOptions" 
                label="Pilih Role" 
                placeholder="Pilih salah satu role"
                required 
            />
            
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
              Role menentukan hak akses pengguna dalam aplikasi. Pastikan memilih role yang sesuai.
            </p>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
