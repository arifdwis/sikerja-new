<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['detail', 'tracking', 'upload', 'schedule']);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};

const diffForHumans = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) return `${diffMins} menit yang lalu`;
    if (diffHours < 24) return `${diffHours} jam yang lalu`;
    return `${diffDays} hari yang lalu`;
};

const getStatusColor = (item) => {
    // TKKSD Custom Logic
    if (item.hasOwnProperty('contributed')) {
        if (!item.contributed) {
             return { bg: 'bg-orange-400', text: 'text-orange-950', border: 'border-orange-400' };
        } else {
             return { bg: 'bg-emerald-400', text: 'text-emerald-950', border: 'border-emerald-400' };
        }
    }

    const colors = {
        0: { bg: 'bg-yellow-400', text: 'text-yellow-950', border: 'border-yellow-400' },
        1: { bg: 'bg-blue-400', text: 'text-blue-950', border: 'border-blue-400' },
        2: { bg: 'bg-purple-400', text: 'text-purple-950', border: 'border-purple-400' },
        4: { bg: 'bg-green-400', text: 'text-green-950', border: 'border-green-400' },
        9: { bg: 'bg-red-400', text: 'text-red-950', border: 'border-red-400' },
    };
    return colors[item.status] || { bg: 'bg-gray-400', text: 'text-gray-950', border: 'border-gray-400' };
};


</script>

<template>
    <div 
        class="group relative rounded-lg border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden flex flex-col h-full"
        :class="[getStatusColor(item).bg, getStatusColor(item).border]"
        @click="$emit('detail', item)"
    >
        <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/20 blur-2xl pointer-events-none"></div>
        
        <div class="p-4 flex flex-col h-full relative z-10" :class="getStatusColor(item).text">
            <div class="flex justify-between items-start gap-2 mb-3">
                <span class="bg-white/80 px-2 py-1 text-xs font-bold rounded border border-white/50 uppercase tracking-wide flex-1">
                    {{ item.kategori?.label || 'Kerjasama' }}
                </span>
                <span class="font-bold uppercase text-sm whitespace-nowrap shrink-0 text-right">
                    {{ formatDate(item.created_at) }}
                </span>
            </div>

            <div class="mb-4">
                <div class="bg-white/80 backdrop-blur-sm border border-white/50 rounded-lg px-3 py-2 shadow-sm">
                    <span class="text-xs font-mono font-bold select-all truncate block">
                        {{ item.nomor_permohonan || item.kode }}
                    </span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm border border-white/50 rounded-lg px-3 py-2 shadow-sm mb-4">
                <h4 class="font-semibold min-h-16 capitalize text-sm leading-snug line-clamp-3">
                    {{ item.label?.toLowerCase() }}
                </h4>
            </div>

            <div class="flex items-center gap-2 mb-3 text-sm">
                <Icon icon="solar:buildings-bold" class="w-5 h-5 flex-shrink-0" />
                <span class="truncate font-semibold capitalize">
                    {{ item.nama_instansi?.toLowerCase() || '-' }}
                </span>
            </div>
            <div class="flex items-center gap-2 mb-3 text-sm">
                <Icon icon="solar:user-bold" class="w-5 h-5 flex-shrink-0" />
                <span class="truncate font-semibold capitalize">
                    {{ item.operator?.name?.toLowerCase() || '-' }}
                </span>
            </div>

            <div class="mt-auto space-y-3 border-t border-gray-50/50 border-dashed pt-4">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="opacity-80 uppercase">Status :</span>
                    <span class="bg-white/80 border border-white/50 px-2 py-1 rounded uppercase tracking-wide">
                        <template v-if="item.hasOwnProperty('contributed')">
                            {{ item.contributed ? 'SUDAH ADA MASUKAN' : 'PERLU MASUKAN' }}
                        </template>
                        <template v-else>
                             {{ item.status_label?.label || 'Permohonan' }}
                        </template>
                    </span>
                </div>
                
                <div class="text-xs font-semibold">
                    {{ diffForHumans(item.created_at) }}
                </div>

                <div class="mt-4">
                    <button 
                        @click.stop="$emit('detail', item)" 
                        class="w-full flex items-center justify-center gap-2 rounded-md px-3 py-2.5 text-sm font-bold uppercase transition-all bg-gray-900 text-white hover:bg-gray-800 shadow-md"
                    >
                        <span>Detail & Diskusi</span>
                        <Icon icon="solar:alt-arrow-right-bold" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
