<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    data: Object
});
</script>

<template>
    <div class="space-y-6">
        <!-- Pihak Kedua: Pemerintah Kota Samarinda & OPD Terkait -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-2 flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <Icon icon="solar:buildings-bold" class="w-4 h-4" />
                </span>
                Pihak Kedua — Pemerintah Kota Samarinda
            </h3>
            <p class="text-xs text-gray-500 mb-3">
                Organisasi Perangkat Daerah (OPD) Pemkot Samarinda yang menjadi pengampu / pihak kedua dalam kerja sama ini:
            </p>
            <div v-if="data.opds && data.opds.length" class="flex flex-wrap gap-2">
                <span
                    v-for="opd in data.opds"
                    :key="opd.id"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-1.5 text-sm font-medium text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200"
                >
                    <Icon icon="solar:buildings-2-bold" class="w-4 h-4 text-emerald-600" />
                    <span>{{ opd.nama }}</span>
                    <span v-if="opd.singkatan" class="text-xs opacity-75">({{ opd.singkatan }})</span>
                </span>
            </div>
            <div v-else class="text-xs text-gray-400 italic">
                Pemerintah Kota Samarinda (OPD teknis belum dipetakan)
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm h-full flex flex-col">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <Icon icon="solar:user-circle-bold" class="w-4 h-4" />
                    </span>
                    Pihak Kesatu (Pejabat 1)
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
                    <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <Icon icon="solar:user-circle-bold" class="w-4 h-4" />
                    </span>
                    Pihak Kesatu (Pejabat 2)
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
                            {{ data.pemohon2?.unit_kerja || data.nama_instansi || '-' }}
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

        <!-- Data Pimpinan Penandatangan & PIC (Proses Bisnis Perda 7/2020) -->
        <div v-if="data.nama_pimpinan || data.pic1_nama" class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    <Icon icon="solar:user-id-bold" class="w-4 h-4" />
                </span>
                Pimpinan & Person In Charge (PIC)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Pimpinan -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase mb-2">
                        <Icon icon="solar:crown-star-bold" class="w-4 h-4 text-amber-500" />
                        Pimpinan Tertinggi
                    </div>
                    <p class="font-bold text-sm text-gray-900 dark:text-white">{{ data.nama_pimpinan || '-' }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ data.jabatan_pimpinan || 'Jabatan tidak tercatat' }}</p>
                    <p v-if="data.nip_pimpinan" class="text-[11px] text-gray-500 font-mono mt-1">NIP: {{ data.nip_pimpinan }}</p>
                </div>

                <!-- PIC 1 -->
                <div v-if="data.pic1_nama" class="p-3.5 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-200/80 dark:border-blue-800/40">
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 uppercase mb-2">
                        <Icon icon="solar:user-bold" class="w-4 h-4 text-blue-500" />
                        PIC 1 (Penanggung Jawab Utama)
                    </div>
                    <p class="font-bold text-sm text-gray-900 dark:text-white">{{ data.pic1_nama }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ data.pic1_jabatan || '-' }}</p>
                    <div class="mt-2 text-xs space-y-0.5">
                        <p v-if="data.pic1_phone" class="text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                            <Icon icon="solar:phone-bold" class="w-3.5 h-3.5 text-blue-500" />
                            {{ data.pic1_phone }}
                        </p>
                        <p v-if="data.pic1_email" class="text-gray-600 dark:text-gray-300 flex items-center gap-1.5 truncate">
                            <Icon icon="solar:letter-bold" class="w-3.5 h-3.5 text-blue-500" />
                            {{ data.pic1_email }}
                        </p>
                    </div>
                </div>

                <!-- PIC 2 -->
                <div v-if="data.pic2_nama" class="p-3.5 bg-purple-50/50 dark:bg-purple-900/10 rounded-xl border border-purple-200/80 dark:border-purple-800/40">
                    <div class="flex items-center gap-2 text-xs font-semibold text-purple-600 uppercase mb-2">
                        <Icon icon="solar:user-bold" class="w-4 h-4 text-purple-500" />
                        PIC 2 (Pendamping / Teknis)
                    </div>
                    <p class="font-bold text-sm text-gray-900 dark:text-white">{{ data.pic2_nama }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ data.pic2_jabatan || '-' }}</p>
                    <div class="mt-2 text-xs space-y-0.5">
                        <p v-if="data.pic2_phone" class="text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                            <Icon icon="solar:phone-bold" class="w-3.5 h-3.5 text-purple-500" />
                            {{ data.pic2_phone }}
                        </p>
                        <p v-if="data.pic2_email" class="text-gray-600 dark:text-gray-300 flex items-center gap-1.5 truncate">
                            <Icon icon="solar:letter-bold" class="w-3.5 h-3.5 text-purple-500" />
                            {{ data.pic2_email }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
