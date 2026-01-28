<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

defineProps({
    filters: Object,
    data: Array
});

const handlePrint = () => {
    window.print();
};
</script>

<style>
@media print {
    nav, aside, footer, header {
        display: none !important;
    }
    main {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    button, .no-print {
        display: none !important;
    }
    body {
        background-color: white !important;
    }
}
</style>

<template>
    <Head title="Akumulatif Kerjasama" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Akumulatif Kerjasama</h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Trend Kerjasama Tahunan</h3>
                        <button @click="handlePrint" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <Icon icon="solar:printer-bold" /> Cetak Laporan
                        </button>
                    </div>

                    <!-- Placeholder Chart Area -->
                    <div class="mb-8 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 text-center">
                        <Icon icon="solar:chart-square-bold-duotone" class="w-16 h-16 text-blue-500 mx-auto mb-3" />
                        <p class="text-blue-800 dark:text-blue-300 font-medium">Grafik Akumulatif Kerjasama akan ditampilkan di sini.</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Data sedang dalam integrasi.</p>
                    </div>

                    <DataTable :value="data" stripedRows tableStyle="min-width: 50rem">
                        <Column field="tahun" header="Tahun" sortable></Column>
                        <Column field="total_kerjasama" header="Total Kerjasama" sortable>
                            <template #body="slotProps">
                                <span class="font-bold">{{ slotProps.data.total_kerjasama }}</span>
                            </template>
                        </Column>
                        <Column field="aktif" header="Aktif">
                             <template #body="slotProps">
                                <Tag severity="success" :value="slotProps.data.aktif" />
                            </template>
                        </Column>
                        <Column field="selesai" header="Selesai">
                             <template #body="slotProps">
                                <Tag severity="info" :value="slotProps.data.selesai" />
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
