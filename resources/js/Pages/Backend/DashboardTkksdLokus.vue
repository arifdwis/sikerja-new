<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Header from '@/Pages/Backend/Dashboard/Components/Header.vue';

const props = defineProps({
    stats: Object,
    opd: Object,
    pendingMonev: Array,
    perluDimonev: Array,
});

const formatDate = (s) => s ? new Date(s).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

const cards = [
    {
        key: 'kerjasama_aktif',
        label: 'Kerjasama Aktif',
        labelShort: 'Aktif',
        icon: 'solar:rocket-broken',
        gradientClass: 'bg-gradient-to-tr from-teal-800 to-teal-600',
    },
    {
        key: 'kerjasama_selesai',
        label: 'Kerjasama Selesai',
        labelShort: 'Selesai',
        icon: 'solar:check-circle-broken',
        gradientClass: 'bg-gradient-to-tr from-emerald-800 to-emerald-600',
    },
];
</script>

<template>
    <Head title="Dashboard TKKSD Lokus" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Hero Header (sama seperti dashboard admin) -->
                <Header />

                <!-- Banner OPD -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white">TKKSD Lokus Kerjasama</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <span v-if="opd">
                            Anda memantau monev untuk OPD <strong>{{ opd.nama }}</strong>{{ opd.singkatan ? ` (${opd.singkatan})` : '' }}.
                        </span>
                        <span v-else class="text-amber-600">
                            <Icon icon="solar:danger-triangle-bold" class="inline w-4 h-4 -mt-1" />
                            OPD belum diatur. Hubungi admin untuk menetapkan OPD pada akun Anda.
                        </span>
                    </p>
                </div>

                <!-- Stats Cards bergaya sama dengan StagesStatusCount -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template v-for="card in cards" :key="card.key">
                        <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
                            <div :class="card.gradientClass" class="px-6 py-4 h-full">
                                <div class="flex justify-between items-start">
                                    <div class="text-left">
                                        <h1 class="text-3xl font-bold tracking-tight text-white/90">{{ stats[card.key] || 0 }}</h1>
                                        <p class="mt-2 text-xs uppercase font-semibold text-white/80">{{ card.labelShort }}</p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <Icon :icon="card.icon" class="w-10 h-10 text-white/60" />
                                    </div>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-black/60 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center px-4 rounded-xl backdrop-blur-sm">
                                <p class="text-center text-sm font-medium text-white">{{ card.label }}</p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Two-column action lists -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Monev menunggu persetujuan -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                                    <Icon icon="solar:clipboard-check-bold" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">Monev Menunggu Persetujuan</h4>
                                    <p class="text-xs text-gray-500">Hasil evaluasi pemohon yang perlu Anda setujui</p>
                                </div>
                            </div>
                            <Link :href="route('monev.index')" class="text-xs text-blue-600 hover:underline">Lihat semua</Link>
                        </div>
                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            <li v-if="!pendingMonev?.length" class="p-6 text-sm text-gray-500 text-center">Tidak ada monev yang perlu Anda setujui.</li>
                            <li v-for="m in pendingMonev" :key="m.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <Link :href="route('monev.show', m.uuid)" class="block px-6 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-mono text-xs text-gray-500">{{ m.kode_monev }}</div>
                                            <div class="font-medium text-gray-800 dark:text-gray-100 text-sm line-clamp-1">{{ m.permohonan?.label }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ m.permohonan?.nama_instansi }}</div>
                                        </div>
                                        <div class="text-xs text-amber-600 whitespace-nowrap">{{ formatDate(m.created_at) }}</div>
                                    </div>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Kerjasama belum dimonev -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-rose-100 dark:bg-rose-900/40 rounded-lg">
                                    <Icon icon="solar:bell-bing-bold" class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">Kerjasama Belum Dimonev</h4>
                                    <p class="text-xs text-gray-500">Sudah lewat tanggal berakhir, pemohon perlu didorong</p>
                                </div>
                            </div>
                        </div>
                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            <li v-if="!perluDimonev?.length" class="p-6 text-sm text-gray-500 text-center">Semua kerjasama OPD Anda sudah dimonev.</li>
                            <li v-for="p in perluDimonev" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <Link :href="route('tkksd-lokus.kerjasama', { tipe: 'aktif' }) + '?detail=' + p.uuid" class="block px-6 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-800 dark:text-gray-100 text-sm line-clamp-1">{{ p.label }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ p.kategori?.label }} · {{ p.nama_instansi }}</div>
                                        </div>
                                        <div class="text-xs text-rose-600 whitespace-nowrap">Berakhir {{ formatDate(p.tanggal_berakhir) }}</div>
                                    </div>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
