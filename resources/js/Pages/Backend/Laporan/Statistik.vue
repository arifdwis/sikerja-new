<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const props = defineProps({
    filters: Object,
    akumulatifData: Array,
    rekapMitraData: Array,
    persentaseOpdData: Array,
    persentaseBidangData: Array,
});

const handlePrint = () => {
    window.print();
};

const activeTab = ref('akumulatif');

const tabs = [
    { id: 'akumulatif', name: 'Akumulatif Kerjasama', icon: 'solar:chart-square-linear' },
    { id: 'rekap_mitra', name: 'Rekapitulasi Mitra', icon: 'solar:users-group-two-rounded-linear' },
    { id: 'persentase_opd', name: 'Persentase Daerah', icon: 'solar:pie-chart-2-linear' },
    { id: 'persentase_bidang', name: 'Persentase Bidang', icon: 'solar:graph-new-linear' },
];

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
    button, .no-print, .print-hide {
        display: none !important;
    }
    body {
        background-color: white !important;
    }
    .print-only {
        display: block !important;
    }
}
</style>

<template>
    <Head title="Statistik & Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Statistik Laporan</h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6 print-hide" />

                <!-- Wrap Tabs and Content in a unified modern Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl">
                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/80 px-4 pt-4 print-hide">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400 gap-2">
                            <li v-for="tab in tabs" :key="tab.id" class="me-1">
                                <button 
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'inline-flex items-center justify-center px-5 py-3 border-b-2 rounded-t-lg group transition-all duration-200',
                                        activeTab === tab.id 
                                            ? 'text-blue-700 border-blue-600 bg-blue-50/50 dark:bg-gray-800 dark:text-blue-500 dark:border-blue-500 font-semibold' 
                                            : 'border-transparent hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                    ]"
                                >
                                    <Icon :icon="tab.icon" class="w-5 h-5 mr-3 transition-colors" :class="activeTab === tab.id ? 'text-blue-600 dark:text-blue-500' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300'" />
                                    {{ tab.name }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- Tab Content: Akumulatif -->
                        <div v-show="activeTab === 'akumulatif'" class="transition-opacity duration-300 animate-in fade-in">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Trend Kerjasama Tahunan</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan total kerjasama dan status aktif per tahun</p>
                                </div>
                                <button @click="handlePrint" class="inline-flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-300/50 transition-all print-hide">
                                    <Icon icon="solar:printer-bold" /> Cetak Laporan
                                </button>
                            </div>

                            <DataTable :value="akumulatifData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <Column field="tahun" header="Tahun" sortable></Column>
                                <Column field="total_kerjasama" header="Total Kerjasama" sortable>
                                    <template #body="slotProps">
                                        <span class="font-bold text-blue-700 dark:text-blue-400">{{ slotProps.data.total_kerjasama }}</span>
                                    </template>
                                </Column>
                                <Column field="aktif" header="Aktif">
                                     <template #body="slotProps">
                                        <Tag severity="success" :value="slotProps.data.aktif" class="px-3" />
                                    </template>
                                </Column>
                                <Column field="selesai" header="Selesai">
                                     <template #body="slotProps">
                                        <Tag severity="info" :value="slotProps.data.selesai" class="px-3" />
                                    </template>
                                </Column>
                            </DataTable>
                        </div>

                        <!-- Tab Content: Rekap Mitra -->
                        <div v-show="activeTab === 'rekap_mitra'" class="transition-opacity duration-300 animate-in fade-in">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Top 20 Mitra Kerjasama</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Institusi yang paling banyak menjalin kerjasama</p>
                                </div>
                                <button @click="handlePrint" class="inline-flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-300/50 transition-all print-hide">
                                    <Icon icon="solar:printer-bold" /> Cetak Laporan
                                </button>
                            </div>

                            <DataTable :value="rekapMitraData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <Column field="id" header="No" sortable style="width: 5%"></Column>
                                <Column field="mitra" header="Nama Mitra" sortable></Column>
                                <Column field="total_kerjasama" header="Total Kerjasama" sortable>
                                    <template #body="slotProps">
                                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ slotProps.data.total_kerjasama }}</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>

                        <!-- Tab Content: Persentase OPD -->
                        <div v-show="activeTab === 'persentase_opd'" class="transition-opacity duration-300 animate-in fade-in">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Capaian Kerjasama Per Perangkat Daerah</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi dan persentase kontribusi kerjasama tiap fungsi unit kerja</p>
                                </div>
                                <button @click="handlePrint" class="inline-flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-300/50 transition-all print-hide">
                                    <Icon icon="solar:printer-bold" /> Cetak Laporan
                                </button>
                            </div>

                            <DataTable :value="persentaseOpdData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <Column field="opd" header="Perangkat Daerah" sortable></Column>
                                <Column field="realisasi" header="Jumlah Kerjasama" sortable></Column>
                                <Column field="persentase" header="Capaian (%)" sortable>
                                    <template #body="slotProps">
                                        <div class="flex items-center gap-4">
                                            <div class="w-full bg-gray-100 rounded-full h-2 shadow-inner dark:bg-gray-700/50 max-w-[200px] border border-gray-200 dark:border-gray-600">
                                                <div class="bg-blue-500 h-2 rounded-full shadow-sm" :style="{ width: slotProps.data.persentase + '%' }"></div>
                                            </div>
                                            <span class="text-sm font-bold text-blue-700 dark:text-blue-400 w-12">{{ slotProps.data.persentase }}%</span>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>

                        <!-- Tab Content: Persentase Bidang -->
                        <div v-show="activeTab === 'persentase_bidang'" class="transition-opacity duration-300 animate-in fade-in">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Detail Bidang Kerjasama</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sebaran permohonan kerjasama berdasarkan sektor/bidang</p>
                                </div>
                                <button @click="handlePrint" class="inline-flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-300/50 transition-all print-hide">
                                    <Icon icon="solar:printer-bold" /> Cetak Laporan
                                </button>
                            </div>

                            <DataTable :value="persentaseBidangData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <Column field="bidang" header="Bidang Kerjasama" sortable></Column>
                                <Column field="jumlah" header="Jumlah Kerjasama" sortable></Column>
                                <Column field="persentase" header="Persentase" sortable>
                                    <template #body="slotProps">
                                        <span class="font-bold px-3 py-1.5 bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-md border border-blue-100 dark:border-blue-800">{{ slotProps.data.persentase }}%</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>
                </div> <!-- End of inner card wrapper -->

            </div>
        </section>
    </AuthenticatedLayout>
</template>