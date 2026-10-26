<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ref, computed } from 'vue'
import Navbar from '@/Components/Landing/Navbar.vue'
import Footer from '@/Components/Landing/Footer.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'

const props = defineProps({
    kategoris: {
        type: Array,
        default: () => []
    },
    mitras: {
        type: Array,
        default: () => []
    },
    totalMitra: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const selectedKategori = ref(props.filters?.kategori || '')
const searchQuery = ref(props.filters?.search || '')

const getCategoryColor = (kategoriNama = '') => {
    const lower = (kategoriNama || '').toLowerCase()
    if (lower.includes('pemerintah')) return { badge: 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200/50', icon: 'solar:buildings-bold-duotone' }
    if (lower.includes('perguruan') || lower.includes('pendidikan')) return { badge: 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200/50', icon: 'solar:diploma-bold-duotone' }
    if (lower.includes('usaha') || lower.includes('swasta') || lower.includes('bumd')) return { badge: 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200/50', icon: 'solar:case-bold-duotone' }
    return { badge: 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/50', icon: 'solar:users-group-two-rounded-bold-duotone' }
}

const applyFilter = (kategoriSlug = '') => {
    selectedKategori.value = kategoriSlug
    router.get('/mitra', {
        kategori: kategoriSlug || undefined,
        search: searchQuery.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const doSearch = () => {
    router.get('/mitra', {
        kategori: selectedKategori.value || undefined,
        search: searchQuery.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    selectedKategori.value = ''
    searchQuery.value = ''
    router.get('/mitra', {}, {
        preserveState: true,
        preserveScroll: true
    })
}
</script>

<template>
    <Head title="Mitra Kerja Sama Daerah - SIKERJA Kota Samarinda" />
    <Navbar />

    <main class="min-h-screen bg-slate-50 dark:bg-slate-950 font-['Inter'] text-slate-900 dark:text-slate-100 transition-colors duration-300">
        <PageHeader
            title="Mitra Kerja Sama Daerah"
            subtitle="Jejaring sinergi dan kolaborasi strategis Pemerintah Kota Samarinda bersama institusi pemerintah daerah lain, dunia usaha/swasta, perguruan tinggi, dan organisasi non-pemerintah."
            :breadcrumbs="[{ label: 'Mitra Kerja Sama', url: '/mitra' }]"
        />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
            <!-- Filter & Search Bar -->
            <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Header & Total -->
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 text-xs font-bold uppercase tracking-wider">
                                <Icon icon="solar:hand-stars-bold-duotone" class="w-3.5 h-3.5" />
                                Katalog Kemitraan
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">
                                Total: <strong class="text-slate-900 dark:text-white">{{ totalMitra }} Mitra Terdaftar</strong>
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-slate-950 dark:text-white font-['Outfit'] mt-1">
                            Eksplorasi Mitra Kolaborator
                        </h2>
                    </div>

                    <!-- Right: Search Input -->
                    <div class="w-full md:w-80">
                        <form @submit.prevent="doSearch" class="relative">
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama mitra atau bidang..."
                                class="w-full pl-10 pr-10 py-2.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-amber-500 focus:ring-amber-500 transition-colors shadow-inner"
                            />
                            <Icon icon="solar:magnifer-linear" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                            <button 
                                v-if="searchQuery" 
                                type="button" 
                                @click="searchQuery = ''; doSearch()"
                                class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            >
                                <Icon icon="solar:close-circle-bold" class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap gap-2">
                    <button
                        @click="applyFilter('')"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-1.5',
                            !selectedKategori 
                                ? 'bg-amber-400 text-slate-950 shadow-md shadow-amber-400/20' 
                                : 'bg-slate-100 dark:bg-slate-800/70 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Icon icon="solar:widget-2-bold" class="w-3.5 h-3.5" />
                        Semua Kategori
                    </button>
                    
                    <button
                        v-for="kat in kategoris"
                        :key="kat.id"
                        @click="applyFilter(kat.slug)"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-1.5',
                            selectedKategori === kat.slug 
                                ? 'bg-amber-400 text-slate-950 shadow-md shadow-amber-400/20' 
                                : 'bg-slate-100 dark:bg-slate-800/70 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Icon :icon="getCategoryColor(kat.nama).icon" class="w-3.5 h-3.5" />
                        {{ kat.nama }}
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-black/10 dark:bg-white/10">
                            {{ kat.active_mitras_count ?? kat.active_mitras?.length ?? 0 }}
                        </span>
                    </button>
                </div>
            </section>

            <!-- Partners Grid -->
            <section v-if="mitras.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div 
                    v-for="mitra in mitras" 
                    :key="mitra.id"
                    class="group relative rounded-3xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/60 p-6 shadow-sm hover:shadow-xl hover:border-amber-400/50 dark:hover:border-amber-400/40 transition-all duration-300 hover:-translate-y-1.5 flex flex-col justify-between"
                >
                    <div>
                        <!-- Logo Container (Inspired by UCLG-ASPAC) -->
                        <div class="h-28 w-full rounded-2xl bg-white p-4 flex items-center justify-center border border-slate-100 dark:border-slate-800/60 shadow-inner group-hover:scale-[1.02] transition-transform duration-300 mb-5">
                            <img 
                                v-if="mitra.logo" 
                                :src="'/' + mitra.logo.replace(/^\/+/, '')" 
                                :alt="mitra.nama"
                                class="max-h-20 max-w-[85%] object-contain filter group-hover:drop-shadow-md transition-all duration-300"
                                loading="lazy"
                            />
                            <div v-else class="text-slate-300 flex flex-col items-center justify-center">
                                <Icon icon="solar:buildings-bold-duotone" class="w-10 h-10 mb-1" />
                                <span class="text-[11px] font-semibold text-slate-400">SIKERJA Partner</span>
                            </div>
                        </div>

                        <!-- Badge Category -->
                        <div class="mb-2">
                            <span 
                                :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border',
                                    getCategoryColor(mitra.kategori?.nama).badge
                                ]"
                            >
                                <Icon :icon="getCategoryColor(mitra.kategori?.nama).icon" class="w-3 h-3" />
                                {{ mitra.kategori?.nama || 'Mitra Strategis' }}
                            </span>
                        </div>

                        <!-- Mitra Name -->
                        <h3 class="font-bold text-base text-slate-900 dark:text-white group-hover:text-amber-500 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                            {{ mitra.nama }}
                        </h3>

                        <!-- Description -->
                        <p v-if="mitra.deskripsi" class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed line-clamp-3">
                            {{ mitra.deskripsi }}
                        </p>
                    </div>

                    <!-- Bottom Link / Action -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-[11px] text-slate-400 font-medium">Status Kerjasama Aktif</span>
                        
                        <a 
                            v-if="mitra.url_website" 
                            :href="mitra.url_website" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 font-bold text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 hover:underline"
                            title="Kunjungi Website Resmi"
                        >
                            Website
                            <Icon icon="solar:arrow-right-up-linear" class="w-3.5 h-3.5" />
                        </a>
                        <span v-else class="text-[11px] text-slate-400 italic">Kota Samarinda</span>
                    </div>
                </div>
            </section>

            <!-- Empty State -->
            <section v-else class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-16 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center mb-4">
                    <Icon icon="solar:magnifer-broken" class="w-8 h-8" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Mitra Ditemukan</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                    Kriteria pencarian atau kategori yang Anda pilih belum memiliki data mitra kerja sama.
                </p>
                <button 
                    @click="clearFilters"
                    class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-400 text-slate-950 text-xs font-bold shadow-md shadow-amber-400/20 hover:bg-amber-300 transition-colors"
                >
                    <Icon icon="solar:restart-bold" class="w-4 h-4" />
                    Reset Filter
                </button>
            </section>

            <!-- CTA Call To Action -->
            <section class="relative rounded-3xl bg-gradient-to-r from-slate-900 via-slate-900 to-amber-950 border border-slate-800 p-8 sm:p-12 overflow-hidden shadow-xl text-white">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 max-w-3xl">
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-400/20 border border-amber-400/30 text-amber-300 text-xs font-bold uppercase tracking-wider mb-3">
                        Kolaborasi Pembangunan Daerah
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black font-['Outfit'] mb-3">
                        Tertarik Menjalin Kerja Sama dengan Pemerintah Kota Samarinda?
                    </h2>
                    <p class="text-sm text-slate-300 leading-relaxed mb-6">
                        Bagian Kerja Sama Sekretariat Daerah Kota Samarinda siap memfasilitasi inisiasi Kemitraan Antar Daerah (KSDD) maupun Kemitraan dengan Pihak Ketiga (KSDPK) secara terpadu melalui aplikasi SIKERJA.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <Link 
                            href="/login" 
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-400 text-slate-950 font-bold text-sm shadow-lg shadow-amber-400/20 hover:bg-amber-300 hover:scale-[1.02] transition-all"
                        >
                            <Icon icon="solar:letter-opened-bold" class="w-4 h-4" />
                            Ajukan Permohonan Kerja Sama
                        </Link>
                        <Link 
                            href="/alur" 
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/20 transition-all"
                        >
                            <Icon icon="solar:route-bold" class="w-4 h-4" />
                            Pelajari Alur Proses
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <Footer />
    <ChatWidget />
</template>
