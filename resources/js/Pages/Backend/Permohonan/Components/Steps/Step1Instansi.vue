<script setup>
import { Icon } from '@iconify/vue';
import SearchSelect from '@/Components/SearchSelect.vue';

defineProps({
    form: Object,
    kategoriOptions: Array,
    provinsiOptions: Array,
    kotaOptions: Array,
    loadingKotas: Boolean
});
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <Icon icon="solar:buildings-bold" class="w-5 h-5 mr-2 text-blue-500" />
            Data Instansi / Perusahaan / Lembaga
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">(<span class="text-red-500 font-bold">*</span>) Tidak boleh kosong</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Judul Kerjasama <span class="text-red-500">*</span>
                </label>
                <input v-model="form.label" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Kerja Sama Pengadaan Barang Dan Jasa..." />
                <p v-if="form.errors.label" class="text-red-500 text-sm mt-1">{{ form.errors.label }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Urusan/Kategori <span class="text-red-500">*</span>
                </label>
                <SearchSelect 
                    v-model="form.id_kategori" 
                    :options="kategoriOptions" 
                    placeholder="-- Pilih Salah Satu --"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nama Instansi <span class="text-red-500">*</span>
                </label>
                <input v-model="form.nama_instansi" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Dinas Pariwisata" />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Provinsi <span class="text-red-500">*</span>
                </label>
                <SearchSelect 
                    v-model="form.id_provinsi" 
                    :options="provinsiOptions" 
                    placeholder="-- Pilih Provinsi --"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kabupaten/Kota <span class="text-red-500">*</span>
                </label>
                <SearchSelect 
                    v-model="form.id_kota" 
                    :options="kotaOptions" 
                    :disabled="loadingKotas || !kotaOptions.length"
                    placeholder="-- Pilih Kota --"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kode Pos <span class="text-red-500">*</span>
                </label>
                <input v-model="form.kode_pos" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 75123" />
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Alamat <span class="text-red-500">*</span>
                </label>
                <textarea v-model="form.alamat" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Jl. Kusuma Bangsa No.66, Samarinda"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Telepon <span class="text-red-500">*</span>
                </label>
                <input v-model="form.telepon" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 081259xxxx" />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: email@instansi.go.id" />
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                <input v-model="form.website" type="url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="https://www.instansi.go.id" />
            </div>
        </div>
    </div>
</template>
