<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { computed, ref } from 'vue';
import Chart from 'primevue/chart';
import Navbar from '@/Components/Landing/Navbar.vue';
import PageHeader from '@/Components/Landing/PageHeader.vue';
import Footer from '@/Components/Landing/Footer.vue';
import ChatWidget from '@/Components/Landing/ChatWidget.vue';

const props = defineProps({
    stats: Object,        // total, aktif, selesai, dalam_proses
    perKategori: Array,   // [{ kategori, total }]
    perOpd: Array,        // [{ opd, total }]
    trendTahun: Array,    // [{ tahun, total }]
    trendBulanan: Array,  // [{ bulan, total }]
    perStatus: Array,     // [{ status, total }]
    topInstansi: Array,   // [{ nama_instansi, total }]
    availableYears: Array,
    tahun: Number,
});

const totalKerjasama = computed(() => props.stats?.total ?? 0);
const selectedYear = ref(props.tahun ?? '');
const chartColors = ['#f59e0b', '#0f172a', '#64748b', '#cbd5e1', '#b45309', '#475569', '#fcd34d', '#94a3b8'];

const applyYearFilter = () => {
    router.get('/infografis', selectedYear.value ? { tahun: selectedYear.value } : {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

const axisOptions = {
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#334155',
                usePointStyle: true,
            },
        },
    },
    scales: {
        x: {
            ticks: { color: '#64748b' },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: { color: '#64748b', precision: 0 },
            grid: { color: '#e2e8f0' },
        },
    },
};

const ringOptions = {
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 10,
                color: '#334155',
                padding: 14,
                usePointStyle: true,
            },
        },
    },
};

const monthlyChart = computed(() => ({
    labels: (props.trendBulanan ?? []).map((item) => item.bulan),
    datasets: [{
        label: props.tahun ? `Kerjasama ${props.tahun}` : 'Kerjasama semua tahun',
        data: (props.trendBulanan ?? []).map((item) => item.total),
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245, 158, 11, 0.14)',
        borderWidth: 3,
        fill: true,
        pointBackgroundColor: '#0f172a',
        pointBorderColor: '#ffffff',
        pointRadius: 4,
        tension: 0.35,
    }],
}));

const statusChart = computed(() => ({
    labels: (props.perStatus ?? []).map((item) => item.status),
    datasets: [{
        data: (props.perStatus ?? []).map((item) => item.total),
        backgroundColor: chartColors,
        borderColor: '#ffffff',
        borderWidth: 2,
    }],
}));

const categoryChart = computed(() => ({
    labels: (props.perKategori ?? []).slice(0, 8).map((item) => item.kategori),
    datasets: [{
        data: (props.perKategori ?? []).slice(0, 8).map((item) => item.total),
        backgroundColor: chartColors,
        borderColor: '#ffffff',
        borderWidth: 2,
    }],
}));

const opdChart = computed(() => ({
    labels: (props.perOpd ?? []).slice(0, 9).map((item) => item.opd),
    datasets: [{
        label: 'Jumlah kerjasama',
        data: (props.perOpd ?? []).slice(0, 9).map((item) => item.total),
        backgroundColor: '#0f172a',
        borderRadius: 5,
    }],
}));

const instansiChart = computed(() => ({
    labels: (props.topInstansi ?? []).map((item) => item.nama_instansi),
    datasets: [{
        label: 'Jumlah kerjasama',
        data: (props.topInstansi ?? []).map((item) => item.total),
        backgroundColor: '#f59e0b',
        borderRadius: 5,
    }],
}));

const horizontalAxisOptions = {
    ...axisOptions,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
    },
};
</script>

<template>
    <Head title="Infografis Kerjasama Daerah" />
    <Navbar />

    <main class="min-h-screen bg-[#f4f6f8] dark:bg-gray-900 font-['Inter'] text-gray-900 dark:text-gray-100">
        <PageHeader
            title="Infografis Kerjasama Daerah"
            subtitle="Ringkasan kerjasama Pemerintah Kota Samarinda"
            :breadcrumbs="[{ label: 'Infografis', url: '/infografis' }]"
        />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
            <section class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Filter Data</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Infografis berdasarkan tahun</h2>
                    <p class="mt-1 text-sm text-gray-500">Pilih satu tahun untuk memfilter statistik, chart, OPD, dan instansi.</p>
                </div>
                <label class="w-full md:w-56">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">Tahun</span>
                    <select
                        v-model="selectedYear"
                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-slate-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                        @change="applyYearFilter"
                    >
                        <option value="">Semua Tahun</option>
                        <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                </label>
            </section>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                        <Icon icon="solar:document-bold" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ totalKerjasama }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Total Kerjasama</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                        <Icon icon="solar:check-circle-bold" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stats?.aktif ?? 0 }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Sedang Aktif</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                        <Icon icon="solar:settings-bold" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stats?.dalam_proses ?? 0 }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Dalam Proses</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                        <Icon icon="solar:archive-bold" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ stats?.selesai ?? 0 }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Telah Selesai</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 xl:col-span-2">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <Icon icon="solar:graph-up-bold" class="h-5 w-5 text-amber-600" />
                            Line Chart Trend Bulanan
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">Pergerakan jumlah data kerjasama pada setiap bulan.</p>
                    </div>
                    <div class="h-80">
                        <Chart type="line" :data="monthlyChart" :options="axisOptions" class="h-full w-full" />
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <Icon icon="solar:pie-chart-2-bold" class="h-5 w-5 text-amber-600" />
                            Komposisi Status
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">Tahapan permohonan dan data manual.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perStatus?.length" type="doughnut" :data="statusChart" :options="ringOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada data</p>
                    </div>
                </section>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <Icon icon="solar:pie-chart-bold" class="h-5 w-5 text-amber-600" />
                            Pie Chart Kategori
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">Delapan objek kerjasama dengan data terbanyak.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perKategori?.length" type="pie" :data="categoryChart" :options="ringOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada data</p>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <Icon icon="solar:buildings-2-bold" class="h-5 w-5 text-amber-600" />
                            Bar Chart OPD
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">OPD pelaksana kerjasama yang paling aktif.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perOpd?.length" type="bar" :data="opdChart" :options="horizontalAxisOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada data</p>
                    </div>
                </section>
            </div>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                        <Icon icon="solar:users-group-rounded-bold" class="h-5 w-5 text-amber-600" />
                        Instansi Mitra Teratas
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Perbandingan lima instansi mitra dengan data kerjasama terbanyak.</p>
                </div>
                <div class="h-80">
                    <Chart v-if="topInstansi?.length" type="bar" :data="instansiChart" :options="horizontalAxisOptions" class="h-full w-full" />
                    <p v-else class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada data</p>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <Icon icon="solar:chart-bold" class="w-5 h-5 text-amber-600" />
                    Distribusi per Kategori
                </h2>
                <div v-if="perKategori?.length" class="space-y-3">
                    <div v-for="k in perKategori" :key="k.kategori" class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ k.kategori }}</span>
                            <span class="text-gray-500">{{ k.total }} kerjasama</span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-2.5 rounded-full bg-amber-500 transition-all"
                                :style="{ width: ((k.total / totalKerjasama) * 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-400 text-center py-6">Belum ada data</p>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <Icon icon="solar:building-bold" class="w-5 h-5 text-amber-600" />
                    Top OPD Pelaksana Kerjasama
                </h2>
                <div v-if="perOpd?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="(o, idx) in perOpd.slice(0, 9)" :key="o.opd"
                        class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 font-bold text-white dark:bg-amber-400 dark:text-slate-900">
                            {{ idx + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ o.opd }}</p>
                            <p class="text-xs text-gray-500">{{ o.total }} kerjasama</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-400 text-center py-6">Belum ada data</p>
            </section>

        </div>
    </main>
    <Footer />
    <ChatWidget />
</template>
