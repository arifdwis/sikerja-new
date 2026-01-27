<script setup>
import { Icon } from '@iconify/vue';
import Tag from 'primevue/tag';

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
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-blue-100 dark:border-blue-900 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 dark:bg-blue-900/20 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <Tag :value="data.kategori?.label" severity="info" class="text-xs px-2 py-1" />
                    <span class="text-xs font-mono text-gray-400 border border-gray-200 dark:border-gray-700 rounded px-1.5 py-0.5">{{ data.nomor_permohonan || data.kode }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-2">{{ data.label }}</h1>
                <p class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <Icon icon="solar:calendar-date-bold" class="w-4 h-4" />
                    Diajukan pada {{ formatDate(data.created_at) }}
                </p>
            </div>

            <div class="flex flex-col items-end gap-3">
                <Tag :value="data.status_label?.label" :severity="data.status_label?.color || 'info'" class="text-sm px-3 py-1.5" />
                
                <div v-if="data.penjadwalans && data.penjadwalans.length > 0" class="mt-2 text-right">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium"
                        :class="data.penjadwalans[0].status === 1 ? 'bg-green-50 text-green-700 border-green-200' : 
                                (data.penjadwalans[0].status === 2 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-orange-50 text-orange-700 border-orange-200')"
                    >
                        <Icon icon="solar:calendar-bold" class="w-4 h-4" />
                        <span>
                            Jadwal: {{ data.penjadwalans[0].status === 1 ? 'Disetujui' : (data.penjadwalans[0].status === 2 ? 'Ditolak' : 'Menunggu') }}
                        </span>
                    </div>
                    <div v-if="data.penjadwalans[0].status === 1" class="text-xs text-gray-500 mt-1">
                        {{ formatDate(data.penjadwalans[0].tanggal) }} • {{ data.penjadwalans[0].waktu?.substring(0,5) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
