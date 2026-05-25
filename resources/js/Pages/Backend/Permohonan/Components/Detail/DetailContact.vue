<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    data: Object
});

const formatPhone = (phone) => {
    if (!phone) return '';
    return phone.replace(/^0/, '62').replace(/[^0-9]/g, '');
};
</script>

<template>
    <div v-if="data.pemohon1 || data.pemohon2" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-4 flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                <Icon icon="solar:phone-bold" class="w-4 h-4" />
            </span>
            Kontak Pemohon
        </h3>
        <div class="space-y-3 flex-1">
            <!-- Pemohon 1 -->
            <div v-if="data.pemohon1" class="border border-gray-100 rounded-lg p-3 bg-gray-50/50">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Pihak Kesatu</p>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ data.pemohon1.name }}</p>
                    </div>
                    <a 
                        v-if="data.pemohon1.phone"
                        :href="`https://wa.me/${formatPhone(data.pemohon1.phone)}`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-700 text-white text-xs font-medium rounded-lg transition-colors shrink-0"
                    >
                        <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
                        Hubungi
                    </a>
                    <span v-else class="text-xs text-gray-400 shrink-0">Tidak ada HP</span>
                </div>
            </div>
            <!-- Pemohon 2 -->
            <div v-if="data.pemohon2" class="border border-gray-100 rounded-lg p-3 bg-gray-50/50">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Pihak Kedua</p>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ data.pemohon2.name }}</p>
                    </div>
                    <a 
                        v-if="data.pemohon2.phone"
                        :href="`https://wa.me/${formatPhone(data.pemohon2.phone)}`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-700 text-white text-xs font-medium rounded-lg transition-colors shrink-0"
                    >
                        <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
                        Hubungi
                    </a>
                    <span v-else class="text-xs text-gray-400 shrink-0">Tidak ada HP</span>
                </div>
            </div>
        </div>
    </div>
</template>
