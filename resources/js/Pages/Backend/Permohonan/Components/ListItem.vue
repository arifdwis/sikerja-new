<script setup>
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    item: Object
});

const emit = defineEmits(['detail', 'tracking']);

const getStatusColor = (status) => {
    const colors = {
        0: { bg: 'bg-amber-400', text: 'text-amber-950', border: 'border-amber-400' },
        1: { bg: 'bg-sky-400', text: 'text-sky-950', border: 'border-sky-400' },
        2: { bg: 'bg-blue-500', text: 'text-blue-950', border: 'border-blue-500' },
        3: { bg: 'bg-violet-400', text: 'text-violet-950', border: 'border-violet-400' },
        4: { bg: 'bg-pink-300', text: 'text-pink-950', border: 'border-pink-300' },
        5: { bg: 'bg-orange-300', text: 'text-orange-950', border: 'border-orange-300' },
        6: { bg: 'bg-teal-400', text: 'text-teal-950', border: 'border-teal-400' },
        7: { bg: 'bg-emerald-500', text: 'text-emerald-950', border: 'border-emerald-500' },
        8: { bg: 'bg-rose-500', text: 'text-rose-950', border: 'border-rose-500' },
        9: { bg: 'bg-red-500', text: 'text-red-950', border: 'border-red-500' },
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
