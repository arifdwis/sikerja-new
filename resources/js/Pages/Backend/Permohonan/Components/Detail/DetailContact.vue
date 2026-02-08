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
        <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <Icon icon="solar:phone-bold" /> Kontak Pemohon
        </h5>
        <div class="space-y-3">
            <!-- Pemohon 1 -->
            <div v-if="data.pemohon1" class="border border-gray-100 rounded-lg p-3 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Pihak Kesatu</p>
                        <p class="text-sm font-semibold text-gray-800">{{ data.pemohon1.name }}</p>
                    </div>
                    <a 
                        v-if="data.pemohon1.phone"
                        :href="`https://wa.me/${formatPhone(data.pemohon1.phone)}`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors"
                    >
                        <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
                        Hubungi
                    </a>
                    <span v-else class="text-xs text-gray-400">No HP tidak tersedia</span>
                </div>
            </div>
            <!-- Pemohon 2 -->
            <div v-if="data.pemohon2" class="border border-gray-100 rounded-lg p-3 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Pihak Kedua</p>
                        <p class="text-sm font-semibold text-gray-800">{{ data.pemohon2.name }}</p>
                    </div>
                    <a 
                        v-if="data.pemohon2.phone"
                        :href="`https://wa.me/${formatPhone(data.pemohon2.phone)}`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors"
                    >
                        <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
                        Hubungi
                    </a>
                    <span v-else class="text-xs text-gray-400">No HP tidak tersedia</span>
                </div>
            </div>
        </div>
    </div>
</template>
