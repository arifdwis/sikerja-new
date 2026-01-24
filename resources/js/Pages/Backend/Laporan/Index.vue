<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import axios from 'axios';

const props = defineProps({
    laporan: Object, 
    summary: Object,
    filters: Object,
    availableYears: Array,
    filterCategories: Array
});

// Search filter
const filterQuery = ref(props.filters?.search || '');
let searchTimeout;

const applyFilters = () => {
    router.visit(route('laporan.index'), {
        data: { search: filterQuery.value },
        preserveState: true,
        preserveScroll: true,
        only: ['laporan']
    });
};

watch(filterQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// View mode
const viewMode = ref(localStorage.getItem('laporanViewMode') || 'grid');
watch(viewMode, (newVal) => localStorage.setItem('laporanViewMode', newVal));

// Detail Logic
const detailDialog = ref(false);
const detailData = ref(null);
const loadingDetail = ref(false);

const openDetailModal = async (uuid) => {
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        const response = await axios.get(route('permohonan.show', uuid));
        detailData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch details", error);
    } finally {
        loadingDetail.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};

const getSeverity = (alertLevel) => {
    switch (alertLevel) {
        case 'danger': return 'danger';
        case 'warning': return 'warning';
        case 'success': return 'success';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Laporan & Monitoring" />

    <AuthenticatedLayout>
        <template #header>
             <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Laporan & Monitoring
            </h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Berlangsung (Emerald) -->
                     <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
                        <div class="bg-gradient-to-tr from-emerald-800 to-emerald-600 px-6 py-4 h-full">
                            <div class="flex justify-between items-start">
                                <div class="text-left">
                                    <h1 class="text-3xl font-bold tracking-tight text-white/90">
                                        {{ summary?.berlangsung || 0 }}
                                    </h1>
                                    <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                                        Berlangsung
                                    </p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <Icon icon="solar:play-broken" class="w-10 h-10 text-white/60" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Segera Berakhir (Teal) -->
                    <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
                        <div class="bg-gradient-to-tr from-teal-800 to-teal-600 px-6 py-4 h-full">
                            <div class="flex justify-between items-start">
                                <div class="text-left">
                                    <h1 class="text-3xl font-bold tracking-tight text-white/90">
                                        {{ summary?.segera_berakhir || 0 }}
                                    </h1>
                                    <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                                        Segera Berakhir
                                    </p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <Icon icon="solar:alarm-broken" class="w-10 h-10 text-white/60" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Telah Berakhir (Red) -->
                     <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
                        <div class="bg-gradient-to-tr from-red-800 to-red-600 px-6 py-4 h-full">
                            <div class="flex justify-between items-start">
                                <div class="text-left">
                                    <h1 class="text-3xl font-bold tracking-tight text-white/90">
                                        {{ summary?.total || 0 }}
                                    </h1>
                                    <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                                        Telah Berakhir
                                    </p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <Icon icon="solar:close-circle-broken" class="w-10 h-10 text-white/60" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Control Bar -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="relative w-full">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari laporan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-green-500 rounded-lg text-sm dark:bg-gray-700" />
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <TabView class="w-full">
                        
                        <!-- Segera Berakhir Tab -->
                        <TabPanel>
                             <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:bell-bing-bold" class="w-4 h-4 text-yellow-500" />
                                    <span>Segera Berakhir</span>
                                    <Tag :value="laporan.segera_berakhir.length" severity="warning" />
                                </div>
                            </template>
                            
                            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg flex items-start gap-4" v-if="laporan.segera_berakhir.length > 0">
                                <div class="bg-yellow-100 p-2 rounded-full text-yellow-600">
                                    <Icon icon="solar:bell-bing-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-yellow-800 text-lg">Perhatian Diperlukan</h4>
                                    <p class="text-sm text-yellow-700 mt-1">Terdapat <span class="font-bold">{{ laporan.segera_berakhir.length }}</span> kerjasama yang akan berakhir dalam waktu kurang dari 90 hari.</p>
                                </div>
                            </div>
                            
                            <DataTable :value="laporan.segera_berakhir" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="p-datatable-sm mt-4">
                                <template #empty>Tidak ada data.</template>
                                <Column field="judul" header="Judul Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="font-bold text-gray-800 dark:text-white hover:text-blue-600 cursor-pointer text-base" @click="openDetailModal(data.uuid)">{{ data.judul }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.kategori }}</div>
                                    </template>
                                </Column>
                                <Column field="instansi" header="Instansi" sortable></Column>
                                <Column header="Masa Berlaku" sortable field="sisa_hari">
                                    <template #body="slotProps">
                                        <div class="text-sm">
                                            <div class="font-mono text-xs text-gray-500">{{ slotProps.data.mulai }} - {{ slotProps.data.berakhir }}</div>
                                            <div class="font-bold text-yellow-600 mt-1 flex items-center gap-1">
                                                <Icon icon="solar:clock-circle-bold" class="w-3.5 h-3.5" />
                                                {{ slotProps.data.sisa_hari }} hari lagi
                                            </div>
                                        </div>
                                    </template>
                                </Column>
                                <Column header="Progress Waktu" class="w-48">
                                        <template #body="slotProps">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-bold text-gray-600">{{ Math.round(slotProps.data.progress) }}%</span>
                                        </div>
                                        <ProgressBar :value="Math.round(slotProps.data.progress)" :showValue="false" style="height: 8px" class="!bg-gray-100"></ProgressBar>
                                        </template>
                                </Column>
                                <Column header="Status">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.status_monev" :severity="getSeverity(slotProps.data.alert_level)" />
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 100px">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined @click="openDetailModal(slotProps.data.uuid)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        
                        <!-- Berlangsung Tab -->
                        <TabPanel>
                            <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:play-bold" class="w-4 h-4 text-green-500" />
                                    <span>Berlangsung</span>
                                    <Tag :value="laporan.berlangsung.length" severity="success" />
                                </div>
                            </template>

                            <DataTable :value="laporan.berlangsung" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Tidak ada data kerjasama berlangsung.</template>
                                <Column field="judul" header="Judul Kerjasama" sortable>
                                        <template #body="{ data }">
                                        <div class="font-bold text-gray-800 dark:text-white hover:text-blue-600 cursor-pointer text-base" @click="openDetailModal(data.uuid)">{{ data.judul }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.kategori }}</div>
                                    </template>
                                </Column>
                                <Column field="instansi" header="Instansi" sortable></Column>
                                <Column header="Masa Berlaku" sortable field="sisa_hari">
                                    <template #body="slotProps">
                                        <div class="text-sm">
                                            <div class="font-mono text-xs text-gray-500">{{ slotProps.data.mulai }} - {{ slotProps.data.berakhir }}</div>
                                            <div class="text-green-600 font-bold mt-1">Sisa {{ slotProps.data.sisa_hari }} hari</div>
                                        </div>
                                    </template>
                                </Column>
                                <Column header="Progress Waktu" class="w-48">
                                        <template #body="slotProps">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-bold text-gray-600">{{ Math.round(slotProps.data.progress) }}%</span>
                                        </div>
                                        <ProgressBar :value="Math.round(slotProps.data.progress)" :showValue="false" style="height: 8px" class="!bg-gray-100"></ProgressBar>
                                        </template>
                                </Column>
                                    <Column header="Status">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.status_monev" severity="success" />
                                    </template>
                                </Column>
                                    <Column header="Aksi" style="width: 100px">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined @click="openDetailModal(slotProps.data.uuid)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>

                        <!-- Telah Berakhir Tab -->
                        <TabPanel>
                            <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:close-circle-bold" class="w-4 h-4 text-red-500" />
                                    <span>Telah Berakhir</span>
                                    <Tag :value="laporan.berakhir.length" severity="danger" />
                                </div>
                            </template>

                            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-start gap-4">
                                <div class="bg-red-100 p-2 rounded-full text-red-600">
                                    <Icon icon="solar:close-circle-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-red-800 text-lg">Kerjasama Berakhir</h4>
                                    <p class="text-sm text-red-700 mt-1">Daftar kerjasama yang telah melewati masa berlaku.</p>
                                </div>
                            </div>
                            
                            <DataTable :value="laporan.berakhir" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Tidak ada data.</template>
                                <Column field="judul" header="Judul Kerjasama" sortable>
                                        <template #body="{ data }">
                                        <div class="font-bold text-gray-800 dark:text-white hover:text-blue-600 cursor-pointer text-base" @click="openDetailModal(data.uuid)">{{ data.judul }}</div>
                                    </template>
                                </Column>
                                <Column field="instansi" header="Instansi" sortable></Column>
                                <Column header="Berakhir Pada" sortable field="berakhir">
                                    <template #body="slotProps">
                                        <div class="text-sm font-bold text-red-600">{{ slotProps.data.berakhir }}</div>
                                        <div class="text-xs text-gray-500">({{ Math.abs(slotProps.data.sisa_hari) }} hari yang lalu)</div>
                                    </template>
                                </Column>
                                    <Column header="Status">
                                    <template #body="slotProps">
                                        <Tag value="Expired" severity="danger" />
                                    </template>
                                </Column>
                                    <Column header="Aksi" style="width: 100px">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined @click="openDetailModal(slotProps.data.uuid)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
        </section>

        <!-- Detail Modal (Reusing Design) -->
        <Dialog v-model:visible="detailDialog" modal header="Detail Lengkap Kerjasama" :style="{ width: '1100px' }" :breakpoints="{ '1199px': '95vw' }" maximizable class="p-0 overflow-hidden">
             <div v-if="loadingDetail" class="space-y-6 p-6">
                 <div class="grid grid-cols-2 gap-6">
                     <Skeleton height="15rem" class="w-full rounded-xl" />
                     <Skeleton height="15rem" class="w-full rounded-xl" />
                 </div>
                 <Skeleton height="25rem" class="w-full rounded-xl" />
            </div>
            
            <div v-else-if="detailData" class="flex flex-col h-[85vh]">
                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
                    <!-- Header Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-green-200 dark:border-green-900 shadow-sm relative overflow-hidden">
                         <div class="absolute top-0 right-0 w-64 h-64 bg-green-50/50 dark:bg-green-900/20 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
                         <div class="relative z-10 flex flex-col md:flex-row justify-between items-start gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <Tag :value="detailData.kategori?.label" severity="success" class="text-xs px-2 py-1" />
                                    <span class="text-xs font-mono text-gray-500 border border-gray-200 dark:border-gray-700 rounded px-1.5 py-0.5">{{ detailData.nomor_permohonan || detailData.kode }}</span>
                                </div>
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-2">{{ detailData.label }}</h1>
                                <p class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <Icon icon="solar:calendar-date-bold" class="w-4 h-4" />
                                    Masa berlaku: {{ formatDate(detailData.tanggal_mulai) }} - {{ formatDate(detailData.tanggal_berakhir) }}
                                </p>
                            </div>
                         </div>
                    </div>

                    <!-- Grid Layout -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <!-- LEFT COLUMN: Details -->
                        <div class="xl:col-span-2 space-y-8">
                            <!-- PARA PIHAK SECTION -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- PIHAK KESATU -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-green-200 dark:border-green-700 shadow-sm relative overflow-hidden group">
                                     <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>
                                     <h3 class="text-sm font-bold text-green-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <Icon icon="solar:user-circle-bold" /> PIHAK KESATU
                                     </h3>
                                     <div class="space-y-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Instansi</p>
                                            <p class="font-bold text-lg text-gray-900 dark:text-white leading-snug">{{ detailData.nama_instansi || '-' }}</p>
                                        </div>
                                        <div v-if="detailData.pemohon1">
                                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Penanggung Jawab</p>
                                            <p class="font-bold text-gray-800">{{ detailData.pemohon1.name }}</p>
                                        </div>
                                     </div>
                                </div>

                                <!-- PIHAK KEDUA -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-green-200 dark:border-green-700 shadow-sm relative overflow-hidden group">
                                     <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                                     <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <Icon icon="solar:buildings-2-bold" /> PIHAK KEDUA
                                     </h3>
                                     <div class="space-y-4">
                                         <div v-if="detailData.pemohon2">
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Pejabat</p>
                                                <p class="font-bold text-lg text-gray-900 dark:text-white leading-snug">{{ detailData.pemohon2.name }}</p>
                                            </div>
                                             <div>
                                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jabatan</p>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ detailData.pemohon2.jabatan }}</p>
                                            </div>
                                         </div>
                                     </div>
                                </div>
                            </div>

                            <!-- DETAIL KERJASAMA -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2 bg-gray-50/50">
                                    <Icon icon="solar:document-text-bold" class="text-gray-400" />
                                    <h3 class="font-bold text-gray-800 dark:text-gray-200">Substansi Kerjasama</h3>
                                </div>
                                <div class="p-6 space-y-8">
                                    <div class="grid grid-cols-1 gap-6">
                                        <div>
                                            <h4 class="text-sm font-bold text-green-600 mb-2">Latar Belakang</h4>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ detailData.latar_belakang }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-green-600 mb-2">Maksud & Tujuan</h4>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ detailData.maksud_tujuan }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-green-600 mb-2">Ruang Lingkup</h4>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ detailData.ruang_lingkup }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Files -->
                        <div class="space-y-6">
                             <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                                <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <Icon icon="solar:folder-with-files-bold" /> Dokumen
                                </h5>
                                <div v-if="detailData.files?.length" class="space-y-3">
                                    <div v-for="file in detailData.files" :key="file.id"
                                        class="border rounded-lg p-3 transition hover:shadow-md bg-white border-gray-200 hover:border-green-300 hover:bg-green-50/20"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-green-50 text-green-600">
                                                <Icon icon="solar:file-check-bold-duotone" class="w-6 h-6" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ file.label }}</p>
                                                <div class="flex gap-2 text-xs mt-1">
                                                    <a :href="file.file_url" target="_blank" class="text-green-600 hover:text-green-800 hover:underline flex items-center gap-1">
                                                        Download <Icon icon="solar:download-linear" class="w-3 h-3" />
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-8">
                                    <Icon icon="solar:folder-error-linear" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                                    <p class="text-xs text-gray-400">Belum ada dokumen yang diupload.</p>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
