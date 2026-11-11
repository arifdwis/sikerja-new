<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { getStatusTheme } from '@/utils/status';

const props = defineProps({
    item: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['detail', 'edit', 'tracking', 'upload', 'schedule']);

const getStatusColor = (status) => {
    const theme = getStatusTheme(status);
    return {
        bg: theme.cardBg || 'bg-gray-400',
        text: theme.cardText || 'text-gray-950',
        border: theme.cardBorder || 'border-gray-400',
    };
};

const canManageFiles = computed(() => {
    if (props.isAdmin) return false;
    const status = Number(props.item?.status);
    const allowedInitialStatuses = [-1, 0, 1, 9, 10, 11, 12, 20, 21];
    if (!allowedInitialStatuses.includes(status)) return false;
    if (!props.item?.files || props.item.files.length === 0) return true;
    return [9, 11].includes(status);
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center gap-4 group">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                <span class="font-bold text-sm font-mono">{{ item.nomor_permohonan || item.kode }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                    :class="item.tipe_dokumen === 'PKS' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                    {{ item.tipe_dokumen === 'PKS' ? 'PKS' : 'KB' }}
                </span>
                <span class="text-xs px-2 py-1 font-bold uppercase rounded" :class="[getStatusColor(item.status).bg, getStatusColor(item.status).text]">
                    {{ item.status_label?.label }}
                </span>
            </div>
            <h4 @click="$emit('detail', item)" class="font-semibold text-gray-950 dark:text-white hover:text-blue-600 cursor-pointer transition-colors capitalize">
                {{ item.label?.toLowerCase() }}
            </h4>
            <div class="text-sm text-gray-500 mt-1">
                Kategori: {{ item.kategori?.label || '-' }} | Instansi: {{ item.nama_instansi || '-' }}
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button 
                v-if="!isAdmin && (item.status == -1 || item.status == 0 || item.status == 9 || item.status == 11)"
                @click="$emit('edit', item)"
                class="px-3 py-2 rounded-lg text-xs font-semibold flex items-center gap-1"
                :class="(item.status == 9 || item.status == 11) ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-yellow-500 text-white hover:bg-yellow-600'"
            >
                <Icon :icon="(item.status == 9 || item.status == 11) ? 'solar:refresh-bold' : 'solar:pen-bold'" class="w-4 h-4" />
                {{ (item.status == 9 || item.status == 11) ? 'Revisi' : (item.status == -1 ? 'Draft' : 'Edit') }}
            </button>
            <button
                v-if="canManageFiles"
                @click="$emit('upload', item)"
                class="px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1"
            >
                <Icon icon="solar:upload-bold" class="w-4 h-4" />
                {{ (item.files && item.files.length > 0) ? 'Perbarui Berkas' : 'Upload Berkas' }}
            </button>
            <button
                v-if="!isAdmin && item.status == 2 && (!item.penjadwalans || item.penjadwalans.length === 0 || item.penjadwalans[0].status === 2)"
                @click="$emit('schedule', item)"
                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1"
            >
                <Icon icon="solar:calendar-add-bold" class="w-4 h-4" />
                Buat Jadwal
            </button>
            <button
                v-if="!isAdmin && item.status == 4"
                @click="$emit('detail', item)"
                class="px-3 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1"
            >
                <Icon icon="solar:pen-new-square-bold" class="w-4 h-4" />
                Upload TTD
            </button>
            <button @click="$emit('detail', item)" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700">
                Detail
            </button>
            <button @click="$emit('tracking', item)" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200">
                Lacak
            </button>
        </div>
    </div>
</template>
