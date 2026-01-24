<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    permohonan: {
        type: Object,
        required: true
    }
});

const ppksd1 = computed(() => props.permohonan.pemohon1); // id_pemohon_0 (Creator/Applicant)
const ppksd2 = computed(() => props.permohonan.pemohon2); // id_pemohon_1 (Selected Partner)
const corporate = computed(() => props.permohonan.corporate); // Assuming relation exists or data passed manually if needed (may need to adjust logic if corporate is not direct relation)

// Note: In PermohonanController::show/index, 'pemohon1' is related to id_pemohon_0, 'pemohon2' to id_pemohon_1. 
// We should check if 'corporate' is available on permohonan object or needs to be fetched.
// Typically corporate is tied to the operator/user of ppksd1. 
</script>

<template>
    <div class="p-6 space-y-6">
        
        <!-- DATA INSTANSI -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <Icon icon="solar:buildings-bold" class="w-6 h-6 mr-2 text-blue-500" />
                Data Instansi / Perusahaan
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Nama Instansi</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.nama_instansi || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Kategori</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.kategori?.label || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Provinsi</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.provinsi?.name || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Kabupaten/Kota</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.kota?.name || '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Alamat</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.alamat || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Telepon Instansi</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.telepon || '-' }}</p>
                </div>
                 <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Email Instansi</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.email || '-' }}</p>
                </div>
                 <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Website</label>
                    <a v-if="permohonan.website" :href="permohonan.website" target="_blank" class="font-semibold text-blue-600 hover:underline break-words">{{ permohonan.website }}</a>
                    <span v-else>-</span>
                </div>
                 <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Kode Pos</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ permohonan.kode_pos || '-' }}</p>
                </div>
            </div>
        </div>

        <!-- PPKSD 1 -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <Icon icon="solar:user-bold" class="w-6 h-6 mr-2 text-teal-500" />
                Pihak Pertama (PPKSD-1)
                <span class="ml-2 text-xs font-normal text-gray-500 bg-white dark:bg-gray-900 px-2 py-1 rounded border dark:border-gray-600">Pemohon</span>
            </h3>

            <div v-if="ppksd1" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Nama Lengkap</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd1.name }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">NIP</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd1.nip || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Jabatan</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd1.jabatan || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Unit Kerja</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd1.unit_kerja || '-' }}</p>
                </div>
                 <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Telepon Pribadi</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd1.phone || '-' }}</p>
                </div>
                 <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Email Pribadi</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd1.email || '-' }}</p>
                </div>
            </div>
            <div v-else class="text-center py-4 text-gray-500 italic">
                Data PPKSD-1 tidak ditemukan
            </div>
        </div>

        <!-- PPKSD 2 -->
        <div v-if="ppksd2" class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <Icon icon="solar:users-group-two-rounded-bold" class="w-6 h-6 mr-2 text-purple-500" />
                Pihak Kedua (PPKSD-2)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Nama Lengkap</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd2.name }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">NIP</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd2.nip || '-' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Jabatan</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd2.jabatan || '-' }}</p>
                </div>
                 <div>
                    <label class="block text-gray-500 dark:text-gray-400 mb-1">Unit Kerja</label>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ ppksd2.unit_kerja || '-' }}</p>
                </div>
            </div>
        </div>

    </div>
</template>
