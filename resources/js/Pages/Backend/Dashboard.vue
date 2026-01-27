<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import TrendChart from '@/Pages/Backend/Dashboard/Charts/TrendChart.vue';
import StatusChart from '@/Pages/Backend/Dashboard/Charts/StatusChart.vue';
import CategoryChart from '@/Pages/Backend/Dashboard/Charts/CategoryChart.vue';
import TopAgenciesTable from '@/Pages/Backend/Dashboard/Charts/TopAgenciesTable.vue';
import ComparisonChart from '@/Pages/Backend/Dashboard/Charts/ComparisonChart.vue';
import LokasiChart from '@/Pages/Backend/Dashboard/Charts/LokasiChart.vue';
import StackedTrendChart from '@/Pages/Backend/Dashboard/Charts/StackedTrendChart.vue';
import Header from '@/Pages/Backend/Dashboard/Components/Header.vue';
import StagesStatusCount from '@/Pages/Backend/Dashboard/Components/StagesStatusCount.vue';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    stats: Object,
    chartTrend: Array,
    chartKategori: Array,
    chartStatus: Array,
    chartComparison: Object, // New prop
    chartLokasi: Array,
    chartStacked: Object,
    permohonanTerbaru: Array,
    needsAction: Array,
    topAgencies: Array,
    tahun: Number,
    kategoriId: Number,
    bulan: String,
    availableYears: Array,
    filterCategories: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => page.props.auth.roles || []);
const isAdminOrTkksd = computed(() => roles.value.some(slug => ['superadmin', 'administrator', 'tkksd'].includes(slug)));

const selectedYear = ref(props.tahun);
const selectedKategori = ref(props.kategoriId);
const selectedBulan = ref(props.bulan);

const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const applyFilters = () => {
    router.get(route('dashboard'), { 
        tahun: selectedYear.value,
        kategori_id: selectedKategori.value,
        bulan: selectedBulan.value
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const statusColors = {
    0: 'bg-teal-50 text-teal-700 border-teal-200', // Baru
    1: 'bg-cyan-50 text-cyan-700 border-cyan-200', // Pembahasan
    2: 'bg-blue-50 text-blue-700 border-blue-200', // Penjadwalan
    3: 'bg-indigo-50 text-indigo-700 border-indigo-200', // Disetujui
    4: 'bg-green-50 text-green-700 border-green-200', // Selesai
    9: 'bg-red-50 text-red-700 border-red-200', // Ditolak
};

const statusLabels = {
    0: 'Permohonan',
    1: 'Pembahasan',
    2: 'Penjadwalan',
    3: 'Persetujuan', 
    4: 'Selesai',
    9: 'Ditolak',
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const showNewPermohonanAlert = ref(false);

onMounted(() => {
    // Check if there are new permohonan (status 0/Baru usually) or pending items
    // Using stats.permohonan which typically represents the 'Baru' count based on the chart logic
    const hasNewPermohonan = props.stats.permohonan > 0;
    
    // Check session storage to ensure we only show this once per session/login
    const hasShownAlert = sessionStorage.getItem('newPermohonanAlertShown');
    
    if (hasNewPermohonan && !hasShownAlert) {
        showNewPermohonanAlert.value = true;
        sessionStorage.setItem('newPermohonanAlertShown', 'true');
    }
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Ported Header (Hero) -->
                <Header />
                
                <!-- Filters Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block mb-2 text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Tahun</label>
                            <Dropdown 
                                v-model="selectedYear" 
                                :options="availableYears" 
                                placeholder="Pilih Tahun" 
                                class="w-full"
                                @change="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block mb-2 text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Bulan</label>
                            <Dropdown 
                                v-model="selectedBulan" 
                                :options="months" 
                                optionLabel="label" 
                                optionValue="value"
                                placeholder="Semua Bulan" 
                                showClear
                                class="w-full"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="md:col-span-2">
                             <label class="block mb-2 text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Objek Kerjasama (Kategori)</label>
                             <Dropdown 
                                v-model="selectedKategori" 
                                :options="filterCategories" 
                                optionLabel="label" 
                                optionValue="id"
                                placeholder="Semua Kategori" 
                                filter
                                showClear
                                class="w-full"
                                @change="applyFilters"
                            />
                        </div>
                    </div>
                </div>

                <!-- Ported Stats Cards -->
                <StagesStatusCount :counts="stats" />

                <!-- Main Content Grid -->
                <div class="space-y-8">
                    
                    <!-- Row 1: Trend & Status -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Trend (Left 2/3) -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
                            <div class="mb-6 flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-lg">Statistik Kerjasama Daerah</h3>
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

                    <!-- Row 2: Comparison & Top Instansi -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Comparison (Left 2/3) -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
                            <div class="mb-4">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Perbandingan Tahunan</h3>
                                <p class="text-sm text-gray-500">Trend tahun ini vs tahun lalu</p>
                            </div>
                            <div class="h-80 w-full relative">
                                <ComparisonChart :data="chartComparison" :dark-mode="false" />
                            </div>
                        </div>

                        <!-- Top Instansi (Right 1/3 - Table) -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden flex flex-col">
                            <div class="h-full overflow-hidden">
                                <TopAgenciesTable :data="topAgencies" />
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Categories -->
                    <div class="grid grid-cols-1 gap-8">
                        <!-- Categories (Full Width) -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
                            <div class="mb-4">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Objek Kerjasama (Top 5)</h3>
                                <p class="text-sm text-gray-500">Kategori paling aktif</p>
                            </div>
                            <div class="h-96 w-full relative">
                                <CategoryChart :data="chartKategori" :dark-mode="false" />
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Additional Analytics (Stacked & Location) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Stacked Trends (Left 2/3) -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
                             <div class="mb-4">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Tren Status per Bulan</h3>
                                <p class="text-sm text-gray-500">Komposisi status permohonan</p>
                            </div>
                            <div class="h-80 w-full relative">
                                <StackedTrendChart :data="chartStacked" :dark-mode="false" />
                            </div>
                        </div>

                        <!-- Location (Right 1/3) -->
                        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
                             <div class="mb-4">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Top Lokasi</h3>
                                <p class="text-sm text-gray-500">Asal wilayah kerjasama</p>
                            </div>
                            <div class="h-80 w-full relative">
                                <LokasiChart :data="chartLokasi" :dark-mode="false" />
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Lists -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Needs Action & Recent List -->
                        <div class="lg:col-span-3 space-y-8">
                             <!-- Needs Action (Priority) -->
                            <div v-if="needsAction.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-yellow-50/50">
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                                            <i class="pi pi-exclamation-circle text-yellow-600"></i> Perlu Tindakan
                                        </h3>
                                        <p class="text-sm text-gray-500">Menunggu validasi atau respon Anda</p>
                                    </div>
                                    <Link :href="route('permohonan.index', { status: 'pending' })" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        Lihat Semua <i class="pi pi-arrow-right text-xs ml-1"></i>
                                    </Link>
                                </div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div v-for="item in needsAction" :key="item.id" class="p-4 hover:bg-gray-50 transition flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                                                {{ item.operator?.name?.charAt(0) || 'U' }}
                                            </div>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <div class="flex justify-between items-start mb-1">
                                                <h4 class="font-bold text-gray-900 truncate pr-2">{{ item.nama_instansi }}</h4>
                                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 line-clamp-1 mb-2">{{ item.label }}</p>
                                            <div class="flex items-center gap-3">
                                                <span :class="statusColors[item.status]" class="px-2 py-0.5 rounded text-xs font-semibold border">
                                                    {{ statusLabels[item.status] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 self-center">
                                            <Link :href="route('permohonan.show', item.uuid)" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition">
                                                <i class="pi pi-chevron-right"></i>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent List -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">Permohonan Terbaru</h3>
                                        <p class="text-sm text-gray-500">Update aktivitas terakhir</p>
                                    </div>
                                    <Link :href="route('permohonan.index')" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        Semua Data <i class="pi pi-arrow-right text-xs ml-1"></i>
                                    </Link>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr class="bg-gray-50">
                                                <th scope="col" class="px-6 py-3">Instansi / Pemohon</th>
                                                <th scope="col" class="px-6 py-3">Objek Kerjasama</th>
                                                <th scope="col" class="px-6 py-3">Status</th>
                                                <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr v-for="item in permohonanTerbaru" :key="item.id" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                <td class="px-6 py-4">
                                                    <div class="font-semibold text-gray-900">{{ item.nama_instansi }}</div>
                                                    <div class="text-xs text-gray-500">{{ item.nomor_permohonan || '-' }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ item.kategori?.label }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span :class="statusColors[item.status]" class="px-2 py-0.5 rounded text-xs font-semibold border inline-flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                        {{ statusLabels[item.status] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <Link :href="route('permohonan.show', item.uuid)" class="text-blue-600 hover:text-blue-900 font-medium text-xs uppercase tracking-wider">Detail</Link>
                                                </td>
                                            </tr>
                                            <tr v-if="permohonanTerbaru.length === 0">
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                                    <i class="pi pi-inbox text-3xl mb-2"></i>
                                                    <p>Belum ada data permohonan terbaru.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <Dialog v-model:visible="showNewPermohonanAlert" modal header="Permohonan Baru" :style="{ width: '450px' }" :breakpoints="{ '960px': '75vw', '640px': '90vw' }">
            <div class="flex flex-col items-center justify-center text-center pt-4 pb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4 text-yellow-600 animate-pulse">
                    <i class="pi pi-bell text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Permohonan Baru Terdeteksi!</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6 px-4">
                    Terdapat <span class="font-bold text-yellow-600 text-lg">{{ stats.permohonan }}</span> permohonan baru yang memerlukan pemeriksaan dan validasi Anda.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 w-full px-4">
                    <Button label="Nanti Saja" icon="pi pi-times" severity="secondary" outlined @click="showNewPermohonanAlert = false" class="flex-1" />
                    <Link :href="route('permohonan.index', { status: 'pending' })" class="flex-1 w-full">
                        <Button label="Lihat Sekarang" icon="pi pi-arrow-right" class="w-full" />
                    </Link>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Optional: Custom scrollbar functionality if needed */
</style>
