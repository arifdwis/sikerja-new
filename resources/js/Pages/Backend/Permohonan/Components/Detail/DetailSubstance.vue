<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    data: Object
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2 bg-gray-50/50">
            <Icon icon="solar:document-text-bold" class="text-gray-400" />
            <h3 class="font-bold text-gray-800 dark:text-gray-200">Substansi Kerjasama</h3>
        </div>
        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <h4 class="text-sm font-bold text-blue-600 mb-2">Latar Belakang</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ data.latar_belakang }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-blue-600 mb-2">Maksud & Tujuan</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ data.maksud_tujuan }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-blue-600 mb-2">Ruang Lingkup</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ data.ruang_lingkup }}</p>
                </div>
            </div>

            <div class="border-t border-dashed border-gray-200 my-6"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Jangka Waktu</h4>
                    <p class="text-sm font-medium mb-2">{{ data.jangka_waktu || '-' }}</p>
                    <div v-if="data.tanggal_mulai && data.tanggal_berakhir" class="text-xs text-gray-500 border-t border-gray-200 mt-2 pt-2 flex justify-between">
                        <span>Mulai: <b class="text-gray-700 dark:text-gray-300">{{ formatDate(data.tanggal_mulai) }}</b></span>
                        <span>S.d: <b class="text-gray-700 dark:text-gray-300">{{ formatDate(data.tanggal_berakhir) }}</b></span>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Lokasi Kerjasama</h4>
                    <p class="text-sm font-medium">{{ data.lokasi_kerjasama || '-' }}</p>
                </div>
                <div v-if="data.manfaat" class="col-span-1 md:col-span-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Manfaat</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.manfaat }}</p>
                </div>
                <div v-if="data.analisis_dampak" class="col-span-1 md:col-span-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Analisis Dampak</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.analisis_dampak }}</p>
                </div>
                <div v-if="data.pembiayaan" class="col-span-1 md:col-span-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Pembiayaan</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.pembiayaan }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
