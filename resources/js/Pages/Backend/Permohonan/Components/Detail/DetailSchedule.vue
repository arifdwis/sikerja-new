<script setup>
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const props = defineProps({
    data: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['refresh']);
const toast = useToast();

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

const handleJadwalApproval = (jadwal, status) => {
    if (confirm(`Apakah anda yakin ingin ${status === 1 ? 'menyetujui' : 'menolak'} jadwal ini?`)) {
        router.put(route('penjadwalan.update', jadwal.uuid), {
            status: status
        }, {
            onSuccess: () => {
                toast.success(`Jadwal berhasil ${status === 1 ? 'disetujui' : 'ditolak'}`);
                emit('refresh');
            }
        });
    }
};
</script>

<template>
    <div v-if="data.penjadwalans && data.penjadwalans.length > 0" 
         class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 p-5 shadow-sm dark:border-gray-700"
    >
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-4 flex items-center justify-between gap-2">
            <span class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                    <Icon icon="solar:calendar-date-bold" class="w-4 h-4" />
                </span>
                {{ data.penjadwalans[0].status === 1 ? 'Jadwal Disetujui' : (data.penjadwalans[0].status === 2 ? 'Jadwal Ditolak' : 'Status Jadwal') }}
            </span>

            <div v-if="isAdmin && data.penjadwalans[0].status === 0" class="flex gap-2">
                <button @click="handleJadwalApproval(data.penjadwalans[0], 2)" class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded text-xs font-bold transition normal-case">
                    Tolak
                </button>
                <button @click="handleJadwalApproval(data.penjadwalans[0], 1)" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-bold transition shadow-sm normal-case">
                    Setujui
                </button>
            </div>
        </h3>

        <div class="space-y-3 text-sm flex-1">
            <div class="flex items-start gap-3">
                <Icon icon="solar:clock-circle-bold" class="w-4 h-4 text-gray-400 mt-0.5" />
                <div>
                    <p class="text-gray-500 text-xs">Waktu</p>
                    <p class="font-semibold">{{ formatDate(data.penjadwalans[0].tanggal) }}</p>
                    <p>{{ formatTime(data.penjadwalans[0].waktu) }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <Icon icon="solar:map-point-bold" class="w-4 h-4 text-gray-400 mt-0.5" />
                <div>
                    <p class="text-gray-500 text-xs">Lokasi</p>
                    <p class="font-medium">{{ data.penjadwalans[0].lokasi }}</p>
                </div>
            </div>
            <div v-if="data.penjadwalans[0].agenda">
                <div class="bg-gray-50 p-2 rounded text-xs text-gray-600 italic">
                    "{{ data.penjadwalans[0].agenda }}"
                </div>
            </div>
            <div v-if="data.penjadwalans[0].admin_comment" class="bg-yellow-50 p-2 rounded text-xs text-yellow-800 border border-yellow-100">
                <strong>Catatan Admin:</strong> {{ data.penjadwalans[0].admin_comment }}
            </div>
        </div>
    </div>
</template>
