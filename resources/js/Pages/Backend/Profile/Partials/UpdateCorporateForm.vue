<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import { useToast } from "vue-toastification";
import { ref, watch, onMounted } from 'vue';

const toast = useToast();
const page = usePage();
const corporate = page.props.corporate;

// Load all kotas grouped by provinsi
const kotasByProvinsi = ref([]);
const loadingKotas = ref(true);

const form = useForm({
    name: corporate?.name || '',
    kota_id: corporate?.kota_id || null,
    postal_code: corporate?.postal_code || '',
    address: corporate?.address || '',
    phone: corporate?.phone || '',
    email: corporate?.email || '',
    website: corporate?.website || '',
});

// Load kotas on mount
onMounted(async () => {
    try {
        const response = await fetch('/api/kotas-all');
        const data = await response.json();
        kotasByProvinsi.value = data;
    } catch (error) {
        console.error('Failed to load kotas:', error);
    } finally {
        loadingKotas.value = false;
    }
});

const submit = () => {
    form.put(route('profile.corporate.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Data Satuan Kerja berhasil diperbarui'),
        onError: (errors) => {
            Object.values(errors).forEach(msg => toast.error(msg));
        }
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Data Satuan Kerja / Instansi
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Lengkapi informasi satuan kerja atau instansi Anda untuk keperluan permohonan kerjasama.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Instansi -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Instansi <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.name" class="w-full" placeholder="Contoh: Dinas Pariwisata" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <!-- Kabupaten/Kota dengan optgroup -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Kabupaten/Kota <span class="text-red-500">*</span>
                    </label>
                    <Dropdown 
                        v-model="form.kota_id" 
                        :options="kotasByProvinsi" 
                        optionLabel="name" 
                        optionValue="id"
                        optionGroupLabel="provinsi"
                        optionGroupChildren="kotas"
                        class="w-full" 
                        placeholder="Pilih Kabupaten/Kota"
                        :loading="loadingKotas"
                        filter
                        showClear
                    >
                        <template #optiongroup="slotProps">
                            <div class="font-bold text-gray-600 dark:text-gray-300 py-1">
                                {{ slotProps.option.provinsi }}
                            </div>
                        </template>
                    </Dropdown>
                    <small v-if="form.errors.kota_id" class="text-red-500">{{ form.errors.kota_id }}</small>
                </div>

                <!-- Kode Pos -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Kode Pos <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.postal_code" class="w-full" placeholder="Contoh: 75123" />
                    <small v-if="form.errors.postal_code" class="text-red-500">{{ form.errors.postal_code }}</small>
                </div>

                <!-- Telepon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Telepon <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.phone" class="w-full" placeholder="Contoh: (0541) 123456" />
                    <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone }}</small>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.email" type="email" class="w-full" placeholder="email@instansi.go.id" />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

                <!-- Website -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Website
                    </label>
                    <InputText v-model="form.website" class="w-full" placeholder="https://www.instansi.go.id" />
                    <small v-if="form.errors.website" class="text-red-500">{{ form.errors.website }}</small>
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <Textarea v-model="form.address" rows="3" class="w-full" placeholder="Contoh: Jl. Kusuma Bangsa No.66, Samarinda" />
                    <small v-if="form.errors.address" class="text-red-500">{{ form.errors.address }}</small>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" label="Simpan Data Satuan Kerja" :loading="form.processing" />

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
