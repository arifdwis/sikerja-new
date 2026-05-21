<script setup>
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import Button from 'primevue/button';
import DetailHeader from '../../Permohonan/Components/Detail/DetailHeader.vue';
import DetailParties from '../../Permohonan/Components/Detail/DetailParties.vue';
import DetailSubstance from '../../Permohonan/Components/Detail/DetailSubstance.vue';
import DetailDocuments from '../../Permohonan/Components/Detail/DetailDocuments.vue';
import DetailContact from '../../Permohonan/Components/Detail/DetailContact.vue';
import DetailSchedule from '../../Permohonan/Components/Detail/DetailSchedule.vue';

defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object
});

defineEmits(['update:visible', 'reject', 'approve']);
</script>

<template>
    <Dialog 
        :visible="visible" 
        @update:visible="$emit('update:visible', $event)"
        modal 
        header="Detail Lengkap Permohonan" 
        :style="{ width: '1100px' }" 
        :breakpoints="{ '1199px': '95vw' }" 
        maximizable 
        :contentStyle="{ overflow: 'hidden', padding: 0, display: 'flex', flexDirection: 'column', height: '80vh' }"
    >
        <div v-if="loading" class="space-y-6 p-6">
            <div class="grid grid-cols-2 gap-6">
                <Skeleton height="15rem" class="w-full rounded-xl" />
                <Skeleton height="15rem" class="w-full rounded-xl" />
            </div>
            <Skeleton height="25rem" class="w-full rounded-xl" />
        </div>
        <div v-else-if="data" class="flex flex-col h-full">
            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
                <DetailHeader :data="data" />
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <div class="xl:col-span-2 space-y-8">
                        <DetailParties :data="data" />
                        <DetailSubstance :data="data" />
                    </div>
                    <div class="space-y-6">
                        <DetailSchedule :data="data" />
                        <DetailDocuments :data="data" />
                        <DetailContact :data="data" />
                    </div>
                </div>
            </div>
            <!-- Action Footer (sticky, always visible) -->
            <div class="shrink-0 bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div v-if="!data.penjadwalans?.length" class="flex items-center gap-2 text-amber-600 text-sm font-medium">
                    <i class="pi pi-info-circle"></i>
                    <span>Belum ada jadwal diajukan oleh pemohon</span>
                </div>
                <div v-else></div>
                <div class="flex gap-3">
                    <Button label="Tutup" severity="secondary" text @click="$emit('update:visible', false)" />
                    <Button label="Tolak Jadwal" icon="pi pi-times-circle" severity="danger" @click="$emit('reject', data)" :disabled="!data.penjadwalans?.length" />
                    <Button label="Setujui Jadwal" icon="pi pi-check-circle" severity="success" @click="$emit('approve', data)" :disabled="!data.penjadwalans?.length" />
                </div>
            </div>
        </div>
    </Dialog>
</template>
