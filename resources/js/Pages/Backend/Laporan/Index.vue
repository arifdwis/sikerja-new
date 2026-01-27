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

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <!-- Stats Cards -->
                <StatsCards :summary="summary" />

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

        <!-- Detail Modal -->
        <DetailModal 
            v-model:visible="detailDialog" 
            :loading="loadingDetail" 
            :data="detailData" 
            :isAdmin="false"
        />
    </AuthenticatedLayout>
</template>
