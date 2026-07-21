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

    <main class="min-h-screen bg-slate-50 dark:bg-slate-950 font-['Inter'] text-slate-900 dark:text-slate-100 transition-colors duration-300">
        <PageHeader
            title="Infografis Kerjasama"
            subtitle="Ringkasan dan visualisasi kerjasama Pemerintah Kota Samarinda"
            :breadcrumbs="[{ label: 'Infografis', url: '/infografis' }]"
        />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
            <section class="flex flex-col gap-4 rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm md:flex-row md:items-end md:justify-between transition-colors duration-300">
                <div>
                    <span class="inline-block py-1 px-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wider mb-2">Filter Data</span>
                    <h2 class="text-xl font-black text-slate-950 dark:text-white font-['Outfit']">Infografis berdasarkan tahun</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Pilih satu tahun untuk memfilter statistik, chart, OPD, dan instansi secara cepat.</p>
                </div>
                <label class="w-full md:w-56">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pilih Tahun</span>
                    <select
                        v-model="selectedYear"
                        class="w-full rounded-xl border-slate-250 dark:border-slate-800 bg-white dark:bg-slate-950 text-sm text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:ring-amber-400"
                        @change="applyYearFilter"
                    >
                        <option value="">Semua Tahun</option>
                        <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                </label>
            </section>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm hover:border-amber-400/20 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-450/10 text-amber-600 dark:text-amber-400 border border-amber-200/10">
                        <Icon icon="solar:document-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ totalKerjasama }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Kerjasama</p>
                </div>
                <div class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm hover:border-amber-400/20 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-450/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/10">
                        <Icon icon="solar:check-circle-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ stats?.aktif ?? 0 }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Sedang Aktif</p>
                </div>
                <div class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm hover:border-amber-400/20 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-450/10 text-sky-600 dark:text-sky-400 border border-sky-200/10">
                        <Icon icon="solar:settings-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ stats?.dalam_proses ?? 0 }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Dalam Proses</p>
                </div>
                <div class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm hover:border-amber-400/20 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-350 border border-slate-200/10">
                        <Icon icon="solar:archive-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ stats?.selesai ?? 0 }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Telah Selesai</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm xl:col-span-2">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:graph-up-bold-duotone" class="h-5 w-5 text-amber-500" />
                            Trend Kerjasama Bulanan
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Visualisasi dinamika pertumbuhan kerjasama dari bulan ke bulan.</p>
                    </div>
                    <div class="h-80">
                        <Chart type="line" :data="monthlyChart" :options="axisOptions" class="h-full w-full" />
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:pie-chart-2-bold-duotone" class="h-5 w-5 text-amber-500" />
                            Komposisi Status
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pembagian tahapan permohonan aktif dan manual.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perStatus?.length" type="doughnut" :data="statusChart" :options="ringOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                    </div>
                </section>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:pie-chart-bold-duotone" class="h-5 w-5 text-amber-500" />
                            Peta Kategori Objek
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Delapan objek klasifikasi kerjasama terbanyak.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perKategori?.length" type="pie" :data="categoryChart" :options="ringOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:buildings-2-bold-duotone" class="h-5 w-5 text-amber-500" />
                            OPD Pelaksana Kerja Sama
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sepuluh perangkat daerah dengan program teraktif.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perOpd?.length" type="bar" :data="opdChart" :options="horizontalAxisOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                    </div>
                </section>
            </div>

            <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <div class="mb-5">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                        <Icon icon="solar:users-group-rounded-bold-duotone" class="h-5 w-5 text-amber-500" />
                        Mitra Kerja Sama Eksternal Teratas
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perbandingan lima instansi mitra dengan kolaborasi terbanyak.</p>
                </div>
                <div class="h-80">
                    <Chart v-if="topInstansi?.length" type="bar" :data="instansiChart" :options="horizontalAxisOptions" class="h-full w-full" />
                    <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950 dark:text-white mb-4 flex items-center gap-2 font-['Outfit']">
                    <Icon icon="solar:chart-bold-duotone" class="w-5 h-5 text-amber-500" />
                    Distribusi Per Kategori Kerja Sama
                </h2>
                <div v-if="perKategori?.length" class="space-y-4">
                    <div v-for="k in perKategori" :key="k.kategori" class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-350">{{ k.kategori }}</span>
                            <span class="text-slate-500 font-semibold">{{ k.total }} kerjasama</span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-2.5 rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all"
                                :style="{ width: ((k.total / totalKerjasama) * 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-slate-400 text-center py-6">Belum ada data</p>
            </section>

            <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950 dark:text-white mb-6 flex items-center gap-2 font-['Outfit']">
                    <Icon icon="solar:building-bold-duotone" class="w-5 h-5 text-amber-500" />
                    Perangkat Daerah Pelaksana Utama
                </h2>
                <div v-if="perOpd?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="(o, idx) in perOpd.slice(0, 9)" :key="o.opd"
                        class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/80 rounded-2xl hover:border-amber-400/20 transition-all duration-300 group">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-950 font-black text-amber-400 dark:bg-slate-850 dark:text-amber-300 border border-white/5 group-hover:bg-amber-450 group-hover:text-slate-950 transition-colors">
                            {{ idx + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate">{{ o.opd }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-450 font-medium mt-0.5">{{ o.total }} kerjasama terdaftar</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-slate-400 text-center py-6">Belum ada data</p>
            </section>

        </div>
    </main>
    <Footer />
    <ChatWidget />
</template>
