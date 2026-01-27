<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    filterQuery: String,
    viewMode: String,
    groupBy: String
});

defineEmits(['update:filterQuery', 'update:viewMode', 'update:groupBy', 'create']);
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                <div class="relative w-full sm:w-80">
                    <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                        :value="filterQuery" 
                        @input="$emit('update:filterQuery', $event.target.value)"
                        type="text" 
                        placeholder="Cari kode / label permohonan..." 
                        class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700" 
                    />
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">Group By:</span>
                    <button @click="$emit('update:groupBy', 'latest')" :class="groupBy === 'latest' ? 'bg-blue-600 border-blue-500 text-blue-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600'" class="px-3 py-1.5 rounded-md text-sm font-medium border transition-colors">
                        Terbaru
                    </button>
                    <button @click="$emit('update:groupBy', 'kategori')" :class="groupBy === 'kategori' ? 'bg-blue-600 border-blue-500 text-blue-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600'" class="px-3 py-1.5 rounded-md text-sm font-medium border transition-colors">
                        Kategori
                    </button>
                    <button @click="$emit('update:groupBy', 'status')" :class="groupBy === 'status' ? 'bg-blue-600 border-blue-500 text-blue-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600'" class="px-3 py-1.5 rounded-md text-sm font-medium border transition-colors">
                        Status
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="$emit('create')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition-colors">
                    <Icon icon="lucide:plus" class="w-4 h-4" />
                    <span>Pengajuan Baru</span>
                </button>
                <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                    <button @click="$emit('update:viewMode', 'grid')" :class="viewMode === 'grid' ? 'bg-white dark:bg-gray-800 shadow-sm text-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700'" class="p-2 rounded-md transition-all">
                        <Icon icon="lucide:layout-grid" class="w-4 h-4" />
                    </button>
                    <button @click="$emit('update:viewMode', 'list')" :class="viewMode === 'list' ? 'bg-white dark:bg-gray-800 shadow-sm text-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700'" class="p-2 rounded-md transition-all">
                        <Icon icon="lucide:list" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
