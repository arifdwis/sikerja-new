<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    data: Object
});
</script>

<template>
    <div class="space-y-6">
        <!-- OPD Terkait — Req 12 -->
        <div v-if="data.opds && data.opds.length" class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                    <Icon icon="solar:buildings-bold" class="w-4 h-4" />
                </span>
                OPD Terkait
            </h3>
            <p class="text-xs text-gray-500 mb-3">
                Organisasi Perangkat Daerah Pemkot Samarinda yang terlibat dalam kerjasama ini.
            </p>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="opd in data.opds"
                    :key="opd.id"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-700/40 dark:text-gray-200"
                >
                    <Icon icon="solar:buildings-2-bold" class="w-4 h-4" />
                    <span>{{ opd.nama }}</span>
                    <span v-if="opd.singkatan" class="text-xs opacity-75">({{ opd.singkatan }})</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm h-full flex flex-col">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                        <Icon icon="solar:user-circle-bold" class="w-4 h-4" />
                    </span>
                    Pihak Kesatu
                </h3>
                
                <div class="space-y-4 flex-1">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nama Pemohon</p>
                        <p class="font-bold text-base text-gray-900 dark:text-white leading-snug">{{ data.pemohon1?.name || '-' }}</p>
                    </div>
                    <div v-if="data.pemohon1?.jabatan">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jabatan</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ data.pemohon1.jabatan }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Instansi</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ data.nama_instansi || '-' }}</p>
                    </div>
                    <div v-if="data.alamat">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Alamat</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.alamat }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-auto">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Penanggung Jawab</p>
                            <p class="text-sm font-medium">{{ data.operator?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kontak</p>
                            <p class="text-sm break-all">{{ data.telepon || data.email || '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm h-full flex flex-col">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                        <Icon icon="solar:buildings-2-bold" class="w-4 h-4" />
                    </span>
                    Pihak Kedua
                </h3>

                <div class="space-y-4 flex-1">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nama Pemohon</p>
                        <p class="font-bold text-base text-gray-900 dark:text-white leading-snug">
                            {{ data.pemohon2?.name || '-' }}
                        </p>
                    </div>
                    <div v-if="data.pemohon2?.jabatan">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jabatan</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ data.pemohon2.jabatan }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Unit Kerja / Instansi</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ data.pemohon2?.unit_kerja || 'Pemerintah Kota Samarinda' }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-auto">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Email</p>
                            <p class="text-sm break-all">{{ data.pemohon2?.email || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kontak</p>
                            <p class="text-sm break-all">{{ data.pemohon2?.phone || '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
