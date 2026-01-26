<script setup>
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    item: Object
});

const emit = defineEmits(['detail', 'tracking']);

const getStatusColor = (status) => {
    const colors = {
        0: { bg: 'bg-yellow-400', text: 'text-yellow-950', border: 'border-yellow-400' },
        1: { bg: 'bg-blue-400', text: 'text-blue-950', border: 'border-blue-400' },
        2: { bg: 'bg-purple-400', text: 'text-purple-950', border: 'border-purple-400' },
        4: { bg: 'bg-green-400', text: 'text-green-950', border: 'border-green-400' },
        9: { bg: 'bg-red-400', text: 'text-red-950', border: 'border-red-400' },
    };
    return colors[status] || { bg: 'bg-gray-400', text: 'text-gray-950', border: 'border-gray-400' };
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center gap-4 group">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <span class="font-bold text-sm">{{ item.nomor_permohonan || item.kode }}</span>
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
        <div class="flex items-center gap-2">
            <button 
                 v-if="item.status == 0"
                 @click="$emit('edit', item)"
                class="px-3 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600 flex items-center gap-1"
            >
                <Icon icon="solar:pen-bold" class="w-4 h-4" />
                Edit
            </button>
            <button @click="$emit('detail', item)" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Detail
            </button>
            <button @click="$emit('tracking', item)" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
                Lacak
            </button>
        </div>
    </div>
</template>
