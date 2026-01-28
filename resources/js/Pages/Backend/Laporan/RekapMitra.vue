<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';

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
    <Head title="Rekapitulasi Mitra" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Rekapitulasi Mitra</h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                     <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top 10 Mitra Kerjasama</h3>
                         <button @click="handlePrint" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <Icon icon="solar:printer-bold" /> Cetak Laporan
                        </button>
                    </div>

                    <DataTable :value="data" stripedRows tableStyle="min-width: 50rem">
                        <Column field="mitra" header="Nama Mitra" sortable class="font-bold"></Column>
                        <Column field="total_kerjasama" header="Total Kerjasama" sortable class="text-center"></Column>
                    </DataTable>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
