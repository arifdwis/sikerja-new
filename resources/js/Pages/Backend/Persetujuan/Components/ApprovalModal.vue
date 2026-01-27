<script setup>
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object
});

defineEmits(['update:visible', 'reject', 'approve']);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
         day: 'numeric', month: 'short', year: 'numeric'
    }).toUpperCase();
};

const formatTime = (timeString) => {
   if (!timeString) return '-';
   if (timeString.includes('T') || timeString.includes(' ')) {
      const date = new Date(timeString);
      return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
   }
   return timeString.substring(0, 5);
};
</script>

<template>
    <Dialog 
        :visible="visible" 
        @update:visible="$emit('update:visible', $event)"
        modal 
        header="Detail Permohonan" 
        :style="{ width: '1100px' }" 
        :breakpoints="{ '1199px': '95vw' }" 
        maximizable 
        class="p-0 overflow-hidden"
    >
        <div v-if="loading" class="p-6 space-y-4"><Skeleton height="20rem" /></div>
        <div v-else-if="data" class="flex flex-col h-[85vh]">
            <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-indigo-200 dark:border-indigo-900 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/50 dark:bg-indigo-900/20 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
                        <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-2">
                            <Tag :value="data.kategori?.label" severity="info" class="text-xs px-2 py-1" />
                            <span class="text-xs font-mono text-gray-500 border border-gray-200 rounded px-1.5 py-0.5">{{ data.nomor_permohonan || data.kode }}</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ data.label }}</h1>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span>{{ data.nama_instansi }}</span>
                            <div>
                                <span class="font-bold text-indigo-600">Jadwal Diajukan:</span> 
                                <span v-if="data.penjadwalans?.[0]">{{ formatDate(data.penjadwalans[0].tanggal) }} {{ formatTime(data.penjadwalans[0].waktu) }}</span>
                            </div>
                        </div>
                        </div>
                </div>

                <!-- Layout -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <div class="xl:col-span-2 space-y-6">
                            <!-- Schedule Info Card -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-xl p-5 border border-indigo-200 dark:border-indigo-700">
                            <h4 class="font-bold text-indigo-800 dark:text-indigo-200 mb-4 flex items-center gap-2">
                                <Icon icon="solar:calendar-mark-bold" /> Detail Jadwal Pembahasan
                            </h4>
                            <div class="grid grid-cols-2 gap-4 text-sm" v-if="data.penjadwalans?.[0]">
                                <div>
                                    <p class="text-xs font-bold uppercase text-gray-500 mb-1">Tanggal</p>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ formatDate(data.penjadwalans[0].tanggal) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase text-gray-500 mb-1">Waktu</p>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ formatTime(data.penjadwalans[0].waktu) }} WITA</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs font-bold uppercase text-gray-500 mb-1">Lokasi</p>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ data.penjadwalans[0].lokasi }}</p>
                                </div>
                                <div class="col-span-2" v-if="data.penjadwalans[0].agenda">
                                    <p class="text-xs font-bold uppercase text-gray-500 mb-1">Agenda</p>
                                    <p class="italic text-gray-700 dark:text-gray-300">"{{ data.penjadwalans[0].agenda }}"</p>
                                </div>
                            </div>
                            </div>

                            <!-- Substansi -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><Icon icon="solar:document-text-bold" class="text-indigo-600" /> Substansi Kerjasama</h3>
                            <div class="space-y-4 text-sm text-gray-700">
                                <div v-if="data.latar_belakang"><span class="font-bold block mb-1">Latar Belakang:</span> {{ data.latar_belakang }}</div>
                                <div v-if="data.maksud_tujuan"><span class="font-bold block mb-1">Maksud & Tujuan:</span> {{ data.maksud_tujuan }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Metadata Right -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Informasi Kontak</h5>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Nama</p>
                                    <p class="font-semibold">{{ data.operator?.name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="font-semibold">{{ data.operator?.email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Action Footer -->
            <div class="bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-xl z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                <Button label="Tutup" severity="secondary" text @click="$emit('update:visible', false)" />
                <Button label="Tolak Jadwal" icon="pi pi-times-circle" severity="danger" @click="$emit('reject', data)" />
                <Button label="Setujui Jadwal" icon="pi pi-check-circle" severity="success" @click="$emit('approve', data)" />
            </div>
        </div>
    </Dialog>
</template>
