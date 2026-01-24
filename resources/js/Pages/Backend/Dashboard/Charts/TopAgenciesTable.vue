<template>
    <div class="card h-full flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top 5 Instansi Aktif</h3>
        </div>
        
        <div class="overflow-x-auto flex-grow">
            <table class="w-full text-sm text-left text-gray-500 font-sans">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 rounded-l-lg">Instansi</th>
                        <th scope="col" class="px-4 py-3 text-right rounded-r-lg">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dashed divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(item, index) in data" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white truncate max-w-[200px]" :title="item.nama_instansi">
                            {{ item.nama_instansi }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600 dark:text-blue-400">
                            {{ item.total }}
                        </td>
                    </tr>
                    <tr v-if="data.length === 0">
                        <td colspan="2" class="px-4 py-8 text-center text-gray-400">
                            Belum ada data.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
defineProps({
    data: Array
});
</script>
