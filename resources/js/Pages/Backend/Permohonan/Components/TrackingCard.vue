<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    permohonan: Object,
});

const page = usePage();

// Status Mapping (From Permohonan Model)
// 0..7 proses utama, 8 dicabut, 9 ditolak/revisi
const statusSteps = [
    { id: 0, label: 'Permohonan Masuk', icon: 'solar:file-send-linear', activeClass: 'border-amber-500 text-amber-600 ring-4 ring-amber-100' },
    { id: 1, label: 'Validasi & Pembahasan', icon: 'solar:clipboard-check-linear', activeClass: 'border-sky-500 text-sky-600 ring-4 ring-sky-100' },
    { id: 2, label: 'Penjadwalan', icon: 'solar:calendar-date-linear', activeClass: 'border-blue-500 text-blue-600 ring-4 ring-blue-100' },
    { id: 3, label: 'Upload PKS Final (Admin)', icon: 'solar:document-add-linear', activeClass: 'border-violet-500 text-violet-600 ring-4 ring-violet-100' },
    { id: 4, label: 'Menunggu Penandatanganan', icon: 'solar:pen-new-square-linear', activeClass: 'border-pink-500 text-pink-600 ring-4 ring-pink-100' },
    { id: 5, label: 'Validasi Dokumen TTD', icon: 'solar:shield-check-linear', activeClass: 'border-orange-500 text-orange-600 ring-4 ring-orange-100' },
    { id: 6, label: 'Pelaksanaan Kerjasama', icon: 'solar:buildings-3-linear', activeClass: 'border-teal-500 text-teal-600 ring-4 ring-teal-100' },
    { id: 7, label: 'Selesai', icon: 'solar:check-circle-linear', activeClass: 'border-emerald-500 text-emerald-600 ring-4 ring-emerald-100' },
];

const currentStatusId = computed(() => {
    return props.permohonan?.status !== undefined ? parseInt(props.permohonan.status) : 0;
});

const getStepState = (stepId) => {
    const current = currentStatusId.value;
    
    // Dicabut atau ditolak tidak berada di jalur progres normal
    if ([8, 9].includes(current)) return 'rejected';
    
    if (current > stepId) return 'completed';
    if (current === stepId) return 'active';
    
    return 'pending';
};

// Flatten history for display
const histories = computed(() => {
    return props.permohonan?.historis || [];
});

</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 h-full flex flex-col">
        <!-- Header -->
        <div class="flex flex-col mb-8 border-l-4 border-blue-500 pl-5">
            <h4 class="text-lg font-bold text-gray-800 dark:text-white">Timeline Permohonan</h4>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Posisi saat ini: 
                <span class="font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide">
                    {{ permohonan?.status_label?.label || 'Menunggu Validasi' }}
                </span>
            </div>
        </div>

        <!-- Timeline -->
        <div class="relative">
            <div v-for="(step, index) in statusSteps" :key="step.id" class="flex gap-4 mb-6 relative last:mb-0">
                
                <!-- Connector Line -->
                <div v-if="index < statusSteps.length - 1" 
                     class="absolute left-5 top-10 bottom-0 w-0.5 -ml-px bg-gray-200 dark:bg-gray-700"
                     :class="{'bg-green-500': getStepState(step.id) === 'completed'}"
                ></div>

                <!-- Icon/Dot -->
                <div class="relative z-10 flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center border-2 bg-white dark:bg-gray-800 transition-all duration-300"
                     :class="[
                        getStepState(step.id) === 'completed' ? 'border-green-500 text-green-500' : 
                        getStepState(step.id) === 'active' ? step.activeClass :
                        'border-gray-300 text-gray-400 dark:border-gray-600'
                     ]"
                >
                    <Icon :icon="getStepState(step.id) === 'completed' ? 'solar:check-circle-bold' : step.icon" class="w-5 h-5" />
                </div>

                <!-- Content -->
                <div class="flex-1 pt-1.5">
                    <h5 class="font-semibold text-gray-900 dark:text-white"
                        :class="{'text-gray-400 dark:text-gray-500': getStepState(step.id) === 'pending'}"
                    >
                        {{ step.label }}
                    </h5>
                    
                    <div v-if="getStepState(step.id) === 'active'" class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 animate-pulse">
                            Sedang Berlangsung
                        </span>
                    </div>

                    <!-- History logs relevant to this 'phase' could ideally be mapped here. 
                         For now, showing most recent log if active -->
                    <div v-if="getStepState(step.id) === 'active' && histories.length > 0" class="mt-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <p class="italic">"{{ histories[0].deskripsi }}"</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $formatDate(histories[0].created_at) }} • {{ histories[0].operator?.name }}</p>
                    </div>

                    <!-- Show completion info if completed -->
                     <div v-if="getStepState(step.id) === 'completed'" class="mt-1 text-xs text-green-600 dark:text-green-400 font-medium flex items-center gap-1">
                        <Icon icon="solar:check-read-linear" class="w-3 h-3" />
                        Selesai
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="currentStatusId === 9"
            class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300"
        >
            Permohonan berstatus ditolak/revisi. Silakan perbaiki data lalu ajukan ulang.
        </div>
        <div
            v-else-if="currentStatusId === 8"
            class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-300"
        >
            Kerjasama dicabut pada tahap pelaksanaan. Proses dihentikan dan tidak dapat dilanjutkan.
        </div>

        <!-- Full History List Toggle or Footer -->
        <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-700">
             <h5 class="font-semibold text-sm mb-3">Riwayat Aktivitas Lengkap</h5>
             <div class="space-y-4 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                <div v-for="log in histories" :key="log.id" class="flex gap-3 text-sm">
                    <div class="flex-shrink-0 w-2 h-2 mt-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div>
                        <p class="text-gray-800 dark:text-gray-200">{{ log.deskripsi }}</p>
                        <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                            <span>{{ $formatDate(log.created_at) }}</span>
                            <span>•</span>
                            <span class="font-medium">{{ log.operator?.name || 'Sistem' }}</span>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
