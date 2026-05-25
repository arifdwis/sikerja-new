<script setup>
import Button from 'primevue/button';
import DetailModal from '../../Permohonan/Components/DetailModal.vue';

defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object
});

defineEmits(['update:visible', 'reject', 'approve']);
</script>

<template>
    <DetailModal
        :visible="visible" 
        @update:visible="$emit('update:visible', $event)"
        :loading="loading"
        :data="data"
    >
        <template #footer>
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
        </template>
    </DetailModal>
</template>
