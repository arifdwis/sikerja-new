<script setup>
import GridItem from './PembahasanGridItem.vue';
import ListItem from '../../Permohonan/Components/ListItem.vue';
import { Icon } from '@iconify/vue';

defineProps({
    groupedData: Object,
    viewMode: String,
    isAdmin: Boolean,
    hasData: Boolean
});

defineEmits(['create', 'detail', 'edit', 'schedule', 'tracking', 'upload']);
</script>

<template>
    <div>
        <div v-if="!hasData" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
                <Icon icon="solar:documents-minimalistic-bold-duotone" class="w-12 h-12 text-gray-400" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Belum ada Permohonan</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-8">Anda belum memiliki riwayat permohonan kerjasama. Buat permohonan baru untuk memulai kerjasama.</p>
            <button @click="$emit('create')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <Icon icon="lucide:plus" class="w-4 h-4" />
                Buat Permohonan Baru
            </button>
        </div>

        <div v-else v-for="(items, groupTitle) in groupedData" :key="groupTitle" class="mb-10">
            <div class="flex justify-between gap-2 mb-4">
                <div class="border-l-4 border-blue-600 pl-2">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 uppercase">{{ groupTitle }}</h3>
                </div>
                <span class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ items.length }} Permohonan</span>
            </div>

            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <GridItem 
                    v-for="item in items" 
                    :key="item.id" 
                    :item="item"
                    :isAdmin="isAdmin"
                    @detail="$emit('detail', $event)"
                    @edit="$emit('edit', $event)"
                    @schedule="$emit('schedule', $event)"
                    @tracking="$emit('tracking', $event)"
                    @upload="$emit('upload', $event)"
                />
            </div>

            <div v-else class="space-y-3">
                <ListItem 
                    v-for="item in items" 
                    :key="item.id" 
                    :item="item"
                    @detail="$emit('detail', $event)"
                    @edit="$emit('edit', $event)"
                    @tracking="$emit('tracking', $event)"
                />
            </div>
        </div>
    </div>
</template>
