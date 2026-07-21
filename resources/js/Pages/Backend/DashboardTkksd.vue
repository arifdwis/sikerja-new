<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Header from '@/Pages/Backend/Dashboard/Components/Header.vue';
import StagesStatusCount from '@/Pages/Backend/Dashboard/Components/StagesStatusCount.vue';
import TrendChart from '@/Pages/Backend/Dashboard/Charts/TrendChart.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    chartTrend: Array,
    needsValidasi: Array,
    needsPembahasan: Array,
    needsJadwal: Array,
    tahun: Number,
});

// Map ke shape yang dipahami StagesStatusCount agar visualnya identik dengan dashboard admin
const stageCounts = computed(() => ({
    permohonan: props.stats?.menunggu_validasi || 0,
    pembahasan: props.stats?.pembahasan || 0,
    penjadwalan: props.stats?.persetujuan_jadwal || 0,
    disetujui: props.stats?.penandatanganan || 0,
    selesai: props.stats?.pelaksanaan || 0,
    dicabut: props.stats?.dicabut || 0,
    ditolak: props.stats?.ditolak || 0,
}));

const formatDate = (s) => s ? new Date(s).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
</script>

<template>
    <Head title="Dashboard TKKSD" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Hero Header -->
                <Header />

                <!-- Stats Cards (gaya sama dengan dashboard admin) -->
                <StagesStatusCount :counts="stageCounts" />

                <!-- Main Content Grid -->
                <div class="space-y-8">
                    <!-- Trend Chart -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <div class="mb-6 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-lg">Trend Permohonan</h3>
                                <p class="text-sm text-gray-500">Jumlah permohonan masuk per bulan ({{ tahun }})</p>
                            </div>
                        </div>
                        <TrendChart :data="chartTrend" />
                    </div>

                    <!-- Antrean Tindakan -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Validasi -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                                        <Icon icon="solar:shield-check-bold" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Antrean Validasi</h4>
                                        <p class="text-xs text-gray-500">Menunggu validasi awal</p>
                                    </div>
                                </div>
                                <Link :href="route('validasi.index')" class="text-xs text-blue-600 hover:underline">Lihat semua</Link>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <li v-if="!needsValidasi?.length" class="p-6 text-sm text-gray-500 text-center">Tidak ada antrean.</li>
                                <li v-for="p in needsValidasi" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <Link :href="route('permohonan.show', p.uuid)" class="block px-6 py-3">
                                        <div class="font-medium text-gray-800 dark:text-gray-100 text-sm line-clamp-1">{{ p.label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ p.kategori?.label }} · {{ p.nama_instansi }}</div>
                                        <div class="text-xs text-amber-600 mt-1">Diajukan {{ formatDate(p.created_at) }}</div>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Pembahasan -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-cyan-100 dark:bg-cyan-900/40 rounded-lg">
                                        <Icon icon="solar:chat-square-bold" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Antrean Pembahasan</h4>
                                        <p class="text-xs text-gray-500">Sedang dibahas TKKSD</p>
                                    </div>
                                </div>
                                <Link :href="route('pembahasan.index')" class="text-xs text-blue-600 hover:underline">Lihat semua</Link>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <li v-if="!needsPembahasan?.length" class="p-6 text-sm text-gray-500 text-center">Tidak ada antrean.</li>
                                <li v-for="p in needsPembahasan" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <Link :href="route('permohonan.show', p.uuid)" class="block px-6 py-3">
                                        <div class="font-medium text-gray-800 dark:text-gray-100 text-sm line-clamp-1">{{ p.label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ p.kategori?.label }} · {{ p.nama_instansi }}</div>
                                        <div class="text-xs text-cyan-600 mt-1">Update {{ formatDate(p.updated_at) }}</div>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Persetujuan Jadwal -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-sky-100 dark:bg-sky-900/40 rounded-lg">
                                        <Icon icon="solar:calendar-mark-bold" class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Persetujuan Jadwal</h4>
                                        <p class="text-xs text-gray-500">Menunggu approval</p>
                                    </div>
                                </div>
                                <Link :href="route('persetujuan.index')" class="text-xs text-blue-600 hover:underline">Lihat semua</Link>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <li v-if="!needsJadwal?.length" class="p-6 text-sm text-gray-500 text-center">Tidak ada antrean.</li>
                                <li v-for="p in needsJadwal" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <Link :href="route('permohonan.show', p.uuid)" class="block px-6 py-3">
                                        <div class="font-medium text-gray-800 dark:text-gray-100 text-sm line-clamp-1">{{ p.label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ p.kategori?.label }} · {{ p.nama_instansi }}</div>
                                        <div class="text-xs text-sky-600 mt-1">{{ p.penjadwalans?.length || 0 }} jadwal diajukan</div>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
