<script setup>
import { Icon } from '@iconify/vue';
import Tag from 'primevue/tag';

defineProps({
    data: Object
});

defineEmits(['open-file-diskusi']);
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
        <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <Icon icon="solar:folder-with-files-bold" /> Dokumen Kerjasama
        </h5>
        <div v-if="data.files?.length" class="space-y-3">
            <div v-for="file in data.files" :key="file.id"
                class="border rounded-lg p-3 transition hover:shadow-md bg-white"
                :class="{
                    'border-green-200 bg-green-50/20': file.status === 1,
                    'border-red-200 bg-red-50/20': file.status === 2,
                    'border-gray-200 bg-gray-50/20': file.status === 0
                }"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600">
                        <Icon icon="solar:file-text-bold-duotone" class="w-6 h-6" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ file.label }}</p>
                        <a :href="file.file_url" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 mt-0.5">
                            Lihat Dokumen <Icon icon="solar:arrow-right-up-linear" class="w-3 h-3" />
                        </a>
                    </div>
                </div>
                
                <div class="mt-3 flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                        <Tag :value="file.status_label?.label" :severity="file.status_label?.color" class="text-[10px]" />
                        <button @click="$emit('open-file-diskusi', file)" class="text-xs font-medium text-gray-500 hover:text-blue-600 flex items-center gap-1">
                        <Icon icon="solar:chat-round-dots-bold" /> Diskusi
                        </button>
                </div>

                <div v-if="file.komentar" class="mt-2 bg-red-50 text-red-700 p-2 rounded text-xs flex gap-2">
                    <Icon icon="solar:info-circle-bold" class="shrink-0 mt-0.5" />
                    <span>{{ file.komentar }}</span>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8">
            <Icon icon="solar:folder-error-linear" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
            <p class="text-xs text-gray-400">Belum ada dokumen yang diupload.</p>
        </div>
    </div>
</template>
