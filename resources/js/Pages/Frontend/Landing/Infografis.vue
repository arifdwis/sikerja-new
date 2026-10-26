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
    stats: Object,        // total, total_permohonan, sedang_berproses, daftar_tunggu, aktif, selesai
    perOpd: Array,        // [{ opd, total }]
    trendTahun: Array,    // [{ tahun, total }]
    trendBulanan: Array,  // [{ bulan, total }]
    perStatus: Array,     // [{ status, total }]
    topInstansi: Array,   // [{ nama_instansi, total }]
    galeriTkksd: Array,   // Galeri kegiatan TKKSD
    availableYears: Array,
    tahun: Number,
});

const totalPermohonan = computed(() => props.stats?.total_permohonan ?? props.stats?.total ?? 0);
const sedangBerproses = computed(() => props.stats?.sedang_berproses ?? props.stats?.dalam_proses ?? 0);
const daftarTunggu = computed(() => props.stats?.daftar_tunggu ?? 0);
const aktifKerjasama = computed(() => props.stats?.aktif ?? 0);
const selesaiKerjasama = computed(() => props.stats?.selesai ?? 0);

const selectedYear = ref(props.tahun ?? '');
const chartColors = ['#0d9488', '#0f172a', '#f59e0b', '#64748b', '#cbd5e1', '#b45309', '#475569', '#fcd34d', '#94a3b8'];

// Lightbox Modal for Galeri TKKSD
const activeModalFoto = ref(null);
const openModalFoto = (item) => {
    activeModalFoto.value = item;
};
const closeModalFoto = () => {
    activeModalFoto.value = null;
};

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
        label: props.tahun ? `Permohonan ${props.tahun}` : 'Permohonan semua tahun',
        data: (props.trendBulanan ?? []).map((item) => item.total),
        borderColor: '#0d9488',
        backgroundColor: 'rgba(13, 148, 136, 0.12)',
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

const opdChart = computed(() => ({
    labels: (props.perOpd ?? []).slice(0, 9).map((item) => item.opd),
    datasets: [{
        label: 'Jumlah program kerja sama',
        data: (props.perOpd ?? []).slice(0, 9).map((item) => item.total),
        backgroundColor: '#0f172a',
        borderRadius: 6,
    }],
}));

const instansiChart = computed(() => ({
    labels: (props.topInstansi ?? []).map((item) => item.nama_instansi),
    datasets: [{
        label: 'Jumlah kerja sama',
        data: (props.topInstansi ?? []).map((item) => item.total),
        backgroundColor: '#0d9488',
        borderRadius: 6,
    }],
}));

const horizontalAxisOptions = {
    ...axisOptions,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
    },
};

const formatTanggal = (d) => {
    if (!d) return '';
    try {
        const date = new Date(d);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    } catch {
        return d;
    }
};
</script>

<template>
    <Head title="Infografis Kerjasama Daerah Kota Samarinda" />
    <Navbar />

    <main class="min-h-screen bg-slate-50 dark:bg-slate-950 font-['Inter'] text-slate-900 dark:text-slate-100 transition-colors duration-300">
        <PageHeader
            title="Infografis Kerjasama"
            subtitle="Ringkasan visualisasi statistik dan data permohonan kerjasama Pemerintah Kota Samarinda"
            :breadcrumbs="[{ label: 'Infografis', url: '/infografis' }]"
        />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
            <!-- Filter Bar -->
            <section class="flex flex-col gap-4 rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm md:flex-row md:items-end md:justify-between transition-colors duration-300">
                <div>
                    <span class="inline-block py-1 px-3 rounded-full bg-teal-100 dark:bg-teal-950/45 text-teal-700 dark:text-teal-300 text-xs font-bold uppercase tracking-wider mb-2">
                        Filter Waktu
                    </span>
                    <h2 class="text-xl font-black text-slate-950 dark:text-white font-['Outfit']">
                        Statistik Kemitraan Daerah
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Pilih satu tahun untuk meninjau data permohonan, proses alur, dan keterlibatan perangkat daerah.
                    </p>
                </div>
                <label class="w-full md:w-56">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pilih Tahun</span>
                    <select
                        v-model="selectedYear"
                        class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-sm text-slate-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        @change="applyYearFilter"
                    >
                        <option value="">Semua Tahun</option>
                        <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                </label>
            </section>

            <!-- 5 Metric Cards (Penyesuaian Dokumen: Total, Sedang Berproses, Daftar Tunggu, Aktif, Selesai) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
                <!-- 1. Total Permohonan Kerja Sama -->
                <div class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm hover:border-teal-500/30 transition-all duration-300 hover:-translate-y-1">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border border-teal-200/30">
                        <Icon icon="solar:document-text-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ totalPermohonan }}</p>
                    <p class="mt-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Jumlah Permohonan
                    </p>
                </div>

                <!-- 2. Sedang Berproses -->
                <div class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm hover:border-amber-500/30 transition-all duration-300 hover:-translate-y-1">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/30">
                        <Icon icon="solar:refresh-circle-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ sedangBerproses }}</p>
                    <p class="mt-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Sedang Berproses
                    </p>
                </div>

                <!-- 3. Daftar Tunggu -->
                <div class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm hover:border-sky-500/30 transition-all duration-300 hover:-translate-y-1">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/30">
                        <Icon icon="solar:clock-circle-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ daftarTunggu }}</p>
                    <p class="mt-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Masuk Daftar Tunggu
                    </p>
                </div>

                <!-- 4. Sedang Aktif (Pelaksanaan) -->
                <div class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm hover:border-emerald-500/30 transition-all duration-300 hover:-translate-y-1">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/30">
                        <Icon icon="solar:play-circle-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ aktifKerjasama }}</p>
                    <p class="mt-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Sedang Aktif
                    </p>
                </div>

                <!-- 5. Telah Selesai -->
                <div class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm hover:border-indigo-500/30 transition-all duration-300 hover:-translate-y-1 col-span-2 sm:col-span-1">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/30">
                        <Icon icon="solar:check-read-bold-duotone" class="h-6 w-6" />
                    </div>
                    <p class="text-3xl font-black text-slate-950 dark:text-white font-['Outfit']">{{ selesaiKerjasama }}</p>
                    <p class="mt-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Telah Selesai
                    </p>
                </div>
            </div>

            <!-- Charts Section: Trend & Komposisi Status -->
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm xl:col-span-2">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:graph-up-bold-duotone" class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            Trend Permohonan Kerja Sama Bulanan
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dinamika volume permohonan kerja sama daerah yang masuk sepanjang tahun.</p>
                    </div>
                    <div class="h-80">
                        <Chart type="line" :data="monthlyChart" :options="axisOptions" class="h-full w-full" />
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:pie-chart-2-bold-duotone" class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            Komposisi Status Alur
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sebaran tahapan kerja sama di lingkungan Pemkot Samarinda.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perStatus?.length" type="doughnut" :data="statusChart" :options="ringOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                    </div>
                </section>
            </div>

            <!-- OPD & Instansi Teratas -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:buildings-2-bold-duotone" class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            OPD Pelaksana Kerja Sama
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perangkat daerah dengan program kolaborasi teraktif.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="perOpd?.length" type="bar" :data="opdChart" :options="horizontalAxisOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                    <div class="mb-5">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-950 dark:text-white font-['Outfit']">
                            <Icon icon="solar:users-group-rounded-bold-duotone" class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            Mitra Kerja Sama Eksternal Teratas
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Instansi mitra dengan frekuensi dan skala kolaborasi terbanyak.</p>
                    </div>
                    <div class="h-80">
                        <Chart v-if="topInstansi?.length" type="bar" :data="instansiChart" :options="horizontalAxisOptions" class="h-full w-full" />
                        <p v-else class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data</p>
                    </div>
                </section>
            </div>

            <!-- Perangkat Daerah Pelaksana Utama Grid -->
            <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950 dark:text-white mb-6 flex items-center gap-2 font-['Outfit']">
                    <Icon icon="solar:buildings-bold-duotone" class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                    Peringkat OPD Pelaksana Kerja Sama
                </h2>
                <div v-if="perOpd?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div 
                        v-for="(o, idx) in perOpd.slice(0, 9)" 
                        :key="o.opd"
                        class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/80 rounded-2xl hover:border-teal-500/30 transition-all duration-300 group"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 font-black text-teal-400 dark:bg-slate-800 dark:text-teal-300 border border-white/5 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                            {{ idx + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate">{{ o.opd }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ o.total }} program terdaftar</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-slate-400 text-center py-6">Belum ada data</p>
            </section>

            <!-- SECTION BARU: Galeri Foto Kegiatan TKKSD (Penyesuaian Dokumen sikerja.docx) -->
            <section class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-8 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 text-xs font-bold uppercase tracking-wider mb-2 border border-teal-200/40">
                            <Icon icon="solar:camera-bold-duotone" class="w-4 h-4 text-teal-600" />
                            Dokumentasi Resmi
                        </span>
                        <h2 class="text-2xl font-black text-slate-950 dark:text-white font-['Outfit']">
                            Galeri Kegiatan TKKSD
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                            Dokumentasi kegiatan Tim Koordinasi Kerja Sama Daerah (TKKSD) Kota Samarinda dalam pembahasan, verifikasi, evaluasi, dan perumusan naskah kemitraan.
                        </p>
                    </div>
                    <span v-if="galeriTkksd?.length" class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ galeriTkksd.length }} Foto Kegiatan
                    </span>
                </div>

                <div v-if="galeriTkksd?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="item in galeriTkksd" 
                        :key="`galeri-${item.id}`"
                        @click="openModalFoto(item)"
                        class="group cursor-pointer bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 overflow-hidden shadow-sm hover:shadow-xl hover:border-teal-500/50 transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between"
                    >
                        <!-- Foto Frame -->
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-200 dark:bg-slate-800">
                            <img 
                                :src="'/' + item.foto.replace(/^\/+/, '')" 
                                :alt="item.judul"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                <span class="text-white text-xs font-bold flex items-center gap-1.5">
                                    <Icon icon="solar:maximize-square-bold-duotone" class="w-4 h-4" />
                                    Lihat Ukuran Penuh
                                </span>
                            </div>
                            <!-- Category Badge -->
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[11px] font-bold border border-white/20">
                                    {{ item.kategori_kegiatan || 'Rapat TKKSD' }}
                                </span>
                            </div>
                        </div>

                        <!-- Info Content -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors line-clamp-2 mb-2">
                                    {{ item.judul }}
                                </h3>
                                <p v-if="item.deskripsi" class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                                    {{ item.deskripsi }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <Icon icon="solar:calendar-bold-duotone" class="w-3.5 h-3.5 text-teal-600" />
                                    {{ formatTanggal(item.tanggal_kegiatan) }}
                                </span>
                                <span v-if="item.lokasi" class="flex items-center gap-1 truncate max-w-[140px]" :title="item.lokasi">
                                    <Icon icon="solar:map-point-bold-duotone" class="w-3.5 h-3.5 text-amber-500" />
                                    {{ item.lokasi }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 px-6 bg-slate-50/50 dark:bg-slate-950/40 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                        <Icon icon="solar:gallery-bold-duotone" class="w-8 h-8" />
                    </div>
                    <h4 class="text-base font-bold text-slate-700 dark:text-slate-300">Belum ada dokumentasi foto kegiatan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                        Dokumentasi visual foto kegiatan TKKSD akan segera diunggah oleh Administrator Bagian Kerja Sama.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Lightbox Preview Foto TKKSD -->
    <div 
        v-if="activeModalFoto" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 animate-fade-in"
        @click.self="closeModalFoto"
    >
        <div class="relative max-w-4xl w-full bg-slate-900 border border-slate-700 rounded-3xl overflow-hidden shadow-2xl">
            <!-- Close Button -->
            <button 
                @click="closeModalFoto" 
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-black/60 text-white hover:bg-white hover:text-black flex items-center justify-center transition-colors"
                title="Tutup"
            >
                <Icon icon="solar:close-circle-bold" class="w-6 h-6" />
            </button>

            <!-- Image View -->
            <div class="relative bg-black flex items-center justify-center max-h-[70vh] overflow-hidden">
                <img 
                    :src="'/' + activeModalFoto.foto.replace(/^\/+/, '')" 
                    :alt="activeModalFoto.judul"
                    class="max-h-[70vh] w-auto max-w-full object-contain"
                />
            </div>

            <!-- Detail Modal -->
            <div class="p-6 bg-slate-900 text-white">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-teal-500/20 text-teal-300 text-xs font-bold border border-teal-500/30">
                        {{ activeModalFoto.kategori_kegiatan || 'Rapat TKKSD' }}
                    </span>
                    <span v-if="activeModalFoto.tanggal_kegiatan" class="text-xs text-slate-400 flex items-center gap-1">
                        <Icon icon="solar:calendar-bold-duotone" class="w-3.5 h-3.5" />
                        {{ formatTanggal(activeModalFoto.tanggal_kegiatan) }}
                    </span>
                    <span v-if="activeModalFoto.lokasi" class="text-xs text-slate-400 flex items-center gap-1">
                        <Icon icon="solar:map-point-bold-duotone" class="w-3.5 h-3.5 text-amber-400" />
                        {{ activeModalFoto.lokasi }}
                    </span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">
                    {{ activeModalFoto.judul }}
                </h3>
                <p v-if="activeModalFoto.deskripsi" class="text-sm text-slate-300 leading-relaxed">
                    {{ activeModalFoto.deskripsi }}
                </p>
            </div>
        </div>
    </div>

    <Footer />
    <ChatWidget />
</template>
