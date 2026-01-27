<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    data: Object
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <Icon icon="solar:user-circle-bold" class="text-blue-500" /> PIHAK KESATU
            </h3>
            
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Instansi</p>
                    <p class="font-bold text-lg text-gray-900 dark:text-white leading-snug">{{ data.nama_instansi || '-' }}</p>
                </div>
                <div v-if="data.alamat">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Alamat</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.alamat }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Penanggung Jawab</p>
                        <p class="text-sm font-medium">{{ data.operator?.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kontak</p>
                        <p class="text-sm">{{ data.telepon || data.email || '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <Icon icon="solar:buildings-2-bold" class="text-indigo-500" /> PIHAK KEDUA
            </h3>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nama Pejabat</p>
                    <p class="font-bold text-lg text-gray-900 dark:text-white leading-snug">
                        {{ data.pemohon2?.name || '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jabatan</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ data.pemohon2?.jabatan || '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Unit Kerja Mitra</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ data.pemohon2?.unit_kerja || 'Pemerintah Kota Samarinda' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
