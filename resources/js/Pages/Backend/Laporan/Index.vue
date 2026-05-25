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
import StatsCards from './Components/StatsCards.vue';
import DetailModal from '../Permohonan/Components/DetailModal.vue';

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

        <section class="py-8">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <!-- Stats Cards -->
                <StatsCards :summary="summary" />

                <!-- Control Bar -->
                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                                <Icon icon="solar:monitor-bold" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Monitoring Kerjasama</h3>
                                <p class="text-sm text-slate-500 dark:text-gray-300">Pantau kerjasama aktif, mendekati akhir masa berlaku, dan yang sudah berakhir.</p>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:flex-row xl:w-auto">
                        <div class="relative w-full sm:w-80">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari laporan..." class="h-11 w-full rounded-lg border border-gray-300 py-0 pl-10 pr-4 text-sm focus:border-slate-500 focus:ring-slate-500 dark:bg-gray-700" />
                        </div>
                        <a :href="route('laporan.cetak-semua')" target="_blank" class="flex h-11 w-full items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-slate-900 px-4 text-sm font-medium text-white transition-colors hover:bg-slate-700 sm:w-auto">
                            <Icon icon="solar:printer-bold" /> Cetak Detail (Semua)
                        </a>
                    </div>
                </div>
                </div>

                <!-- Main Content -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                    <TabView class="w-full">
                        
                        <!-- Segera Berakhir Tab -->
                        <TabPanel>
                             <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:bell-bing-bold" class="w-4 h-4 text-slate-500" />
                                    <span>Segera Berakhir</span>
                                    <Tag :value="laporan.segera_berakhir.length" severity="warning" />
                                </div>
                            </template>
                            
                            <div class="mb-5 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4" v-if="laporan.segera_berakhir.length > 0">
                                <div class="rounded-lg bg-white p-2 text-slate-600 shadow-sm">
                                    <Icon icon="solar:bell-bing-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-slate-900">Perhatian Diperlukan</h4>
                                    <p class="mt-1 text-sm text-slate-600">Terdapat <span class="font-bold">{{ laporan.segera_berakhir.length }}</span> kerjasama yang akan berakhir dalam waktu kurang dari 90 hari.</p>
                                </div>
                            </div>
                            
                            <DataTable :value="laporan.segera_berakhir" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="p-datatable-sm mt-4">
                                <template #empty>Tidak ada data.</template>
                                <Column field="judul" header="Judul Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="cursor-pointer text-sm font-semibold text-slate-900 hover:text-slate-600 dark:text-white" @click="openDetailModal(data.uuid)">{{ data.judul }}</div>
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
                                    <Icon icon="solar:play-bold" class="w-4 h-4 text-slate-500" />
                                    <span>Berlangsung</span>
                                    <Tag :value="laporan.berlangsung.length" severity="success" />
                                </div>
                            </template>

                            <DataTable :value="laporan.berlangsung" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Tidak ada data kerjasama berlangsung.</template>
                                <Column field="judul" header="Judul Kerjasama" sortable>
                                        <template #body="{ data }">
                                        <div class="cursor-pointer text-sm font-semibold text-slate-900 hover:text-slate-600 dark:text-white" @click="openDetailModal(data.uuid)">{{ data.judul }}</div>
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
                                    <Icon icon="solar:close-circle-bold" class="w-4 h-4 text-slate-500" />
                                    <span>Telah Berakhir</span>
                                    <Tag :value="laporan.berakhir.length" severity="danger" />
                                </div>
                            </template>

                            <div class="mb-5 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="rounded-lg bg-white p-2 text-slate-600 shadow-sm">
                                    <Icon icon="solar:close-circle-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-slate-900">Kerjasama Berakhir</h4>
                                    <p class="mt-1 text-sm text-slate-600">Daftar kerjasama yang telah melewati masa berlaku.</p>
                                </div>
                            </div>
                            
                            <DataTable :value="laporan.berakhir" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Tidak ada data.</template>
                                <Column field="judul" header="Judul Kerjasama" sortable>
                                        <template #body="{ data }">
                                        <div class="cursor-pointer text-sm font-semibold text-slate-900 hover:text-slate-600 dark:text-white" @click="openDetailModal(data.uuid)">{{ data.judul }}</div>
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

        <!-- Detail Modal -->
        <DetailModal 
            v-model:visible="detailDialog" 
            :loading="loadingDetail" 
            :data="detailData" 
            :isAdmin="false"
        />
    </AuthenticatedLayout>
</template>
