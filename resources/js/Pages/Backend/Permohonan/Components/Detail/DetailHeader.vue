<script setup>
import { Icon } from '@iconify/vue';
import Tag from 'primevue/tag';

defineProps({
    data: Object
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '-';
    if (timeString.includes('T')) {
        const d = new Date(timeString);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WITA';
    }
    return timeString.substring(0, 5) + ' WITA';
};
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col items-start justify-between gap-6 md:flex-row">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <Tag :value="data.kategori?.label" severity="secondary" class="text-xs px-2 py-1" />
                    <span class="text-xs font-mono text-gray-400 border border-gray-200 dark:border-gray-700 rounded px-1.5 py-0.5">{{ data.nomor_permohonan || data.kode }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-2">{{ data.label }}</h1>
                <p class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <Icon icon="solar:calendar-date-bold" class="w-4 h-4" />
                    Diajukan pada {{ formatDate(data.created_at) }}
                </p>
            </div>

            <div class="flex flex-col items-end gap-3">
                <Tag :value="data.status_label?.label" :severity="data.status_label?.severity || 'info'" class="text-sm px-3 py-1.5" />
                
                <div v-if="data.penjadwalans && data.penjadwalans.length > 0" class="mt-2 text-right">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium"
                        :class="data.penjadwalans[0].status === 1 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                                (data.penjadwalans[0].status === 2 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-50 text-slate-700 border-slate-200')"
                    >
                        <Icon icon="solar:calendar-bold" class="w-4 h-4" />
                        <span>
                            Jadwal: {{ data.penjadwalans[0].status === 1 ? 'Disetujui' : (data.penjadwalans[0].status === 2 ? 'Ditolak' : 'Menunggu') }}
                        </span>
                    </div>
                    <div v-if="data.penjadwalans[0].status === 1" class="text-xs text-gray-500 mt-1">
                        {{ formatDate(data.penjadwalans[0].tanggal) }} • {{ formatTime(data.penjadwalans[0].waktu) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner alasan penolakan/pencabutan -->
        <div v-if="[8, 9].includes(Number(data.status)) && data.alasan_tolak"
            class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex items-start gap-3">
                <Icon icon="solar:danger-triangle-bold" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                    <p class="font-bold text-red-800 dark:text-red-300 mb-1">
                        {{ Number(data.status) === 8 ? 'Kerjasama Dicabut' : 'Permohonan Ditolak' }}
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-400">
                        <span class="font-semibold">{{ Number(data.status) === 8 ? 'Alasan Pencabutan' : 'Alasan' }}:</span> {{ data.alasan_tolak }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
