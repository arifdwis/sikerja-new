<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import Button from 'primevue/button';
import FileDiskusiSection from '../../Permohonan/Components/FileDiskusiSection.vue';

const props = defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object,
    isUserAdmin: Boolean,
    selectedFile: Object
});

const emit = defineEmits(['update:visible', 'select-file', 'confirm-finish', 'status-update']);

const allFilesApproved = computed(() => props.data?.all_files_approved || false);

const getFileStatusClass = (file) => {
    if (file.status === 1) return 'border-green-500 bg-green-50 dark:bg-green-900/20';
    if (file.status === 2) return 'border-red-500 bg-red-50 dark:bg-red-900/20';
    return 'border-gray-200 bg-white dark:bg-gray-800';
};
</script>

<template>
    <Dialog 
        :visible="visible" 
        @update:visible="$emit('update:visible', $event)"
        modal 
        header="Review & Diskusi Dokumen" 
        :style="{ width: '1200px' }" 
        :breakpoints="{ '1199px': '95vw' }" 
        maximizable 
        class="p-0 overflow-hidden"
    >
        <div v-if="loading" class="p-6 space-y-4"><Skeleton height="20rem" /></div>
        <div v-else-if="data" class="flex flex-col h-[85vh]">
            <!-- Header Info -->
            <div class="p-4 bg-white dark:bg-gray-800 border-b flex justify-between items-center shadow-sm z-10">
            <div>
                <h2 class="font-bold text-lg text-gray-900">{{ data.label }}</h2>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="font-mono">{{ data.nomor_permohonan || data.kode }}</span>
                        <span>•</span>
                        <span>{{ data.nama_instansi }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                    <div v-if="allFilesApproved" class="flex items-center gap-2 text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold border border-green-100">
                        <Icon icon="solar:check-circle-bold" /> Semua Dokumen Disetujui
                    </div>
                    <div v-else class="flex items-center gap-2 text-orange-600 bg-orange-50 px-3 py-1 rounded-full text-xs font-bold border border-orange-100">
                        <Icon icon="solar:clock-circle-bold" /> Dokumen Belum Lengkap
                    </div>

                    <Button 
                    v-if="isUserAdmin"
                    label="Selesai & Lanjut Jadwal" 
                    icon="pi pi-check-circle" 
                    severity="success"
                    @click="$emit('confirm-finish', data)" 
                />
            </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-hidden grid grid-cols-1 lg:grid-cols-3 bg-gray-50 dark:bg-gray-900">
                <!-- Left: Files List -->
                <div class="lg:col-span-1 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col">
                    <div class="p-3 border-b border-gray-100 bg-gray-50/50">
                        <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Dadaftar Dokumen</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2">
                        <button
                        v-for="file in data.files" 
                        :key="file.id"
                        @click="$emit('select-file', file)"
                        class="w-full text-left p-3 rounded-lg border-2 transition-all group"
                        :class="[
                            getFileStatusClass(file),
                            selectedFile?.id === file.id ? 'ring-2 ring-blue-500 ring-offset-2' : 'hover:border-blue-300'
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <Icon 
                                :icon="file.status === 1 ? 'solar:check-circle-bold' : (file.status === 2 ? 'solar:close-circle-bold' : 'solar:file-text-bold')" 
                                :class="file.status === 1 ? 'text-green-600' : (file.status === 2 ? 'text-red-600' : 'text-gray-400')"
                                class="w-5 h-5 mt-0.5 shrink-0"
                            />
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-800 dark:text-white truncate group-hover:text-blue-600 transition-colors">{{ file.label }}</p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">
                                    Status: <span class="font-medium">{{ file.status === 1 ? 'Disetujui' : (file.status === 2 ? 'Ditolak' : 'Review') }}</span>
                                </p>
                            </div>
                        </div>
                    </button>
                    </div>
                </div>

                <!-- Right: Chat/Discussion -->
                <div class="lg:col-span-2 h-full flex flex-col bg-white dark:bg-gray-800">
                    <div v-if="selectedFile" class="h-full">
                    <FileDiskusiSection 
                        :file="selectedFile"
                        :permohonan="data"
                        :isAdmin="true"
                        @statusUpdated="$emit('status-update', $event)"
                    />
                </div>
                <div v-else class="h-full flex flex-col items-center justify-center text-gray-400 p-8">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <Icon icon="solar:cursor-bold-duotone" class="w-8 h-8 text-gray-300" />
                        </div>
                        <p>Pilih dokumen di sebelah kiri untuk melihat detail dan memulai diskusi.</p>
                </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>
