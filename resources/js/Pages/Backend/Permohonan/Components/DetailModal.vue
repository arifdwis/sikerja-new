<script setup>
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import DetailHeader from './Detail/DetailHeader.vue';
import DetailParties from './Detail/DetailParties.vue';
import DetailSubstance from './Detail/DetailSubstance.vue';
import DetailSchedule from './Detail/DetailSchedule.vue';
import DetailDocuments from './Detail/DetailDocuments.vue';
import DetailContact from './Detail/DetailContact.vue';

const props = defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['update:visible', 'open-file-diskusi', 'refresh']);

const handleUpdateVisible = (val) => {
    emit('update:visible', val);
};
</script>

<template>
    <Dialog 
        :visible="visible" 
        @update:visible="handleUpdateVisible"
        modal 
        header="Detail Lengkap Permohonan" 
        :style="{ width: '1100px' }" 
        :breakpoints="{ '1199px': '95vw' }" 
        maximizable 
        class="p-0"
        contentClass="overflow-y-auto"
    >
        <div v-if="loading" class="space-y-6 p-6">
                <div class="grid grid-cols-2 gap-6">
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                </div>
                <Skeleton height="25rem" class="w-full rounded-xl" />
        </div>
        
        <div v-else-if="data" class="p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
            <DetailHeader :data="data" />

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-2 space-y-8">
                    <DetailParties :data="data" />
                    <DetailSubstance :data="data" />
                </div>

                <div class="space-y-6">
                    <DetailSchedule :data="data" :isAdmin="isAdmin" @refresh="$emit('refresh')" />
                    <DetailDocuments :data="data" @open-file-diskusi="$emit('open-file-diskusi', $event)" />
                    <DetailContact :data="data" />
                </div>
            </div>
        </div>
    </Dialog>
</template>
