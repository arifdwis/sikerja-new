<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Header from '@/Pages/Backend/Dashboard/Components/Header.vue';
import StagesStatusCount from '@/Pages/Backend/Dashboard/Components/StagesStatusCount.vue';
import CreateForm from '@/Pages/Backend/Permohonan/Components/CreateForm.vue';
import TrendChart from '@/Pages/Backend/Dashboard/Charts/TrendChart.vue';
import StatusChart from '@/Pages/Backend/Dashboard/Charts/StatusChart.vue';
import Dialog from 'primevue/dialog';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    chartTrend: Array,
    chartStatus: Array,
    permohonanTerbaru: Array,
    kategoris: Array,
    provinsis: Array,
    pemohon: Object,
    corporate: Object,
    pemohonanList: Array,
    alerts: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const editDialog = ref(false);
const selectedItem = ref(null);

const openEditModal = (item) => {
    selectedItem.value = item;
    editDialog.value = true;
};

const handleEditSuccess = () => {
    editDialog.value = false;
    router.reload({ only: ['permohonanTerbaru', 'stats'] });
};

const statusColors = {
    0: 'bg-teal-50 text-teal-700 border-teal-200',
    1: 'bg-cyan-50 text-cyan-700 border-cyan-200',
    2: 'bg-blue-50 text-blue-700 border-blue-200',
    3: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    4: 'bg-green-50 text-green-700 border-green-200',
    9: 'bg-red-50 text-red-700 border-red-200',
};

const statusLabels = {
    0: 'Permohonan',
    1: 'Pembahasan',
    2: 'Penjadwalan',
    3: 'Persetujuan', 
    4: 'Selesai',
    9: 'Ditolak',
};
</script>

<template>
    <Head title="Dashboard Pemohon" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Header -->
                <Header />

                 <!-- Alerts -->
                <div v-if="props.alerts && props.alerts.length > 0" class="space-y-3">
                    <div v-for="(alert, index) in props.alerts" :key="index" 
                        class="p-4 rounded-lg flex items-start gap-3 border"
                        :class="alert.type === 'error' ? 'bg-red-50 text-red-800 border-red-200' : 'bg-yellow-50 text-yellow-800 border-yellow-200'"
                    >
                         <i :class="alert.type === 'error' ? 'pi pi-times-circle text-xl mt-0.5' : 'pi pi-exclamation-triangle text-xl mt-0.5'"></i>
                         <div>
                             <p class="font-medium">{{ alert.message }}</p>
                             <Link v-if="alert.link" :href="alert.link" class="text-sm font-bold underline mt-1 inline-block">Lihat Detail</Link>
                         </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <StagesStatusCount :counts="stats" />

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Trend (Left 2/3) -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
                        <div class="mb-6 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-lg">Statistik Pengajuan Saya</h3>
                                <p class="text-sm text-gray-500">Trend data per bulan</p>
                            </div>
                        </div>
                        <div class="h-80 w-full relative">
                            <TrendChart :data="chartTrend" :dark-mode="false" />
                        </div>
                    </div>

                    <!-- Status (Right 1/3) -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-center overflow-hidden">
                        <StatusChart :data="chartStatus" :dark-mode="false" />
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex justify-end">
                    <Link 
                        :href="route('permohonan.create')" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        + Buat Permohonan Baru
                    </Link>
                </div>

                <!-- Recent List -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-700/50">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-lg">Permohonan Saya</h3>
                            <p class="text-sm text-gray-500">Status terkini pengajuan kerjasama Anda</p>
                        </div>
                        <Link :href="route('permohonan.index')" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                            Lihat Semua <i class="pi pi-arrow-right text-xs ml-1"></i>
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Instansi / Nomor</th>
                                    <th scope="col" class="px-6 py-3">Objek Kerjasama</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="item in permohonanTerbaru" :key="item.id" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ item.nama_instansi }}</div>
                                        <div class="text-xs text-gray-500">{{ item.nomor_permohonan || 'Menunggu Nomor' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                            {{ item.kategori?.label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="statusColors[item.status]" class="px-2 py-0.5 rounded text-xs font-semibold border inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ statusLabels[item.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button 
                                            v-if="item.status == 0"
                                            @click="openEditModal(item)"
                                            class="inline-flex items-center text-orange-600 hover:text-orange-900 font-medium text-xs uppercase tracking-wider bg-orange-50 px-2 py-1 rounded border border-orange-200"
                                        >
                                            <i class="pi pi-pencil mr-1"></i> Edit
                                        </button>
                                        <Link :href="route('permohonan.show', item.uuid)" class="text-blue-600 hover:text-blue-900 font-medium text-xs uppercase tracking-wider bg-blue-50 px-2 py-1 rounded border border-blue-200">
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="permohonanTerbaru.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                        <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                        <p>Belum ada pengajuan kerjasama.</p>
                                        <Link :href="route('permohonan.create')" class="text-blue-600 hover:underline mt-2 inline-block">
                                            Buat Sekarang
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <Dialog v-model:visible="editDialog" modal header="Edit Permohonan Kerjasama" :style="{ width: '1000px' }" :breakpoints="{ '960px': '95vw' }" class="p-0 overflow-hidden">
            <div class="h-[80vh] flex flex-col">
                <CreateForm 
                    v-if="selectedItem"
                    mode="edit"
                    :initialData="selectedItem"
                    :kategoris="props.kategoris || []" 
                    :provinsis="props.provinsis" 
                    :pemohon="props.pemohon"
                    :corporate="props.corporate"
                    :pemohonanList="props.pemohonanList"
                    @success="handleEditSuccess" 
                />
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
