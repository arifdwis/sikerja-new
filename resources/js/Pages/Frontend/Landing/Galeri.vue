<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ref } from 'vue'
import Navbar from '@/Components/Landing/Navbar.vue'
import Footer from '@/Components/Landing/Footer.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'

const props = defineProps({
    galeris: {
        type: Object,
        default: () => ({ data: [] })
    },
    categories: {
        type: Array,
        default: () => []
    },
    availableYears: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const selectedKategori = ref(props.filters?.kategori || '')
const selectedYear = ref(props.filters?.tahun || '')
const searchQuery = ref(props.filters?.search || '')
const activeModalFoto = ref(null)

const formatTanggal = (dateStr) => {
    if (!dateStr) return '-'
    const d = new Date(dateStr)
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const applyFilters = () => {
    router.get('/galeri-tkksd', {
        kategori: selectedKategori.value || undefined,
        tahun: selectedYear.value || undefined,
        search: searchQuery.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const resetFilters = () => {
    selectedKategori.value = ''
    selectedYear.value = ''
    searchQuery.value = ''
    router.get('/galeri-tkksd', {}, {
        preserveState: true,
        preserveScroll: true
    })
}

const openModal = (item) => {
    activeModalFoto.value = item
    document.body.style.overflow = 'hidden'
}

const closeModal = () => {
    activeModalFoto.value = null
    document.body.style.overflow = 'auto'
}
</script>

<template>
    <Head title="Galeri Kegiatan TKKSD - SIKERJA Kota Samarinda" />
    <Navbar />

    <main class="min-h-screen bg-slate-50 dark:bg-slate-950 font-['Inter'] text-slate-900 dark:text-slate-100 transition-colors duration-300">
        <PageHeader
            title="Galeri Kegiatan TKKSD"
            subtitle="Dokumentasi visual kegiatan Tim Koordinasi Kerja Sama Daerah (TKKSD) Kota Samarinda dalam perumusan kebijakan, verifikasi, pembahasan naskah, seremonial penandatanganan, dan monitoring lapangan."
            :breadcrumbs="[{ label: 'Galeri TKKSD', url: '/galeri-tkksd' }]"
        />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
            <!-- Filter Bar -->
            <section class="rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/50 p-6 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Search Input -->
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Pencarian Kegiatan
                        </label>
                        <form @submit.prevent="applyFilters" class="relative">
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari judul kegiatan, materi, atau lokasi..."
                                class="w-full pl-10 pr-10 py-2.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-amber-500 focus:ring-amber-500 transition-colors"
                            />
                            <Icon icon="solar:magnifer-linear" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                            <button 
                                v-if="searchQuery" 
                                type="button" 
                                @click="searchQuery = ''; applyFilters()"
                                class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            >
                                <Icon icon="solar:close-circle-bold" class="w-4 h-4" />
                            </button>
                        </form>
                    </div>

                    <!-- Category Filter -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Kategori Tahapan
                        </label>
                        <select 
                            v-model="selectedKategori"
                            @change="applyFilters"
                            class="w-full py-2.5 px-4 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-colors"
                        >
                            <option value="">Semua Kategori Kegiatan</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Tahun
                        </label>
                        <select 
                            v-model="selectedYear"
                            @change="applyFilters"
                            class="w-full py-2.5 px-4 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 transition-colors"
                        >
                            <option value="">Semua Tahun</option>
                            <option v-for="yr in availableYears" :key="yr" :value="yr">{{ yr }}</option>
                        </select>
                    </div>

                    <!-- Reset Button -->
                    <div class="md:col-span-1">
                        <button 
                            type="button"
                            @click="resetFilters"
                            class="w-full py-2.5 px-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold text-xs flex items-center justify-center gap-1 transition-colors"
                            title="Reset Filter"
                        >
                            <Icon icon="solar:restart-bold" class="w-4 h-4" />
                            Reset
                        </button>
                    </div>
                </div>
            </section>

            <!-- Gallery Grid -->
            <section v-if="galeris.data && galeris.data.length > 0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="item in galeris.data" 
                        :key="item.id"
                        @click="openModal(item)"
                        class="group cursor-pointer rounded-3xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/60 overflow-hidden shadow-sm hover:shadow-xl hover:border-teal-500/40 transition-all duration-300 hover:-translate-y-1.5 flex flex-col justify-between"
                    >
                        <!-- Image Container with Aspect Ratio -->
                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-slate-900">
                            <img 
                                v-if="item.foto" 
                                :src="'/' + item.foto.replace(/^\/+/, '')" 
                                :alt="item.judul"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-500">
                                <Icon icon="solar:camera-broken" class="w-12 h-12" />
                            </div>

                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20 opacity-80 group-hover:opacity-90 transition-opacity"></div>

                            <!-- Category Badge -->
                            <div class="absolute top-3.5 left-3.5 z-10">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-900/80 backdrop-blur-md text-amber-300 text-[11px] font-bold border border-amber-400/30 flex items-center gap-1">
                                    <Icon icon="solar:tag-bold-duotone" class="w-3 h-3 text-amber-400" />
                                    {{ item.kategori_kegiatan || 'Rapat TKKSD' }}
                                </span>
                            </div>

                            <!-- Zoom Hint Icon on Hover -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                <div class="w-12 h-12 rounded-full bg-black/60 backdrop-blur-md text-white flex items-center justify-center border border-white/30 transform scale-75 group-hover:scale-100 transition-transform">
                                    <Icon icon="solar:magnifer-zoom-in-bold" class="w-6 h-6 text-amber-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors line-clamp-2 mb-2 font-['Outfit']">
                                    {{ item.judul }}
                                </h3>
                                <p v-if="item.deskripsi" class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
                                    {{ item.deskripsi }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1.5 font-medium">
                                    <Icon icon="solar:calendar-bold-duotone" class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                    {{ formatTanggal(item.tanggal_kegiatan) }}
                                </span>
                                <span v-if="item.lokasi" class="flex items-center gap-1 truncate max-w-[150px] font-medium" :title="item.lokasi">
                                    <Icon icon="solar:map-point-bold-duotone" class="w-4 h-4 text-amber-500" />
                                    {{ item.lokasi }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Links -->
                <div v-if="galeris.links && galeris.links.length > 3" class="mt-12 flex justify-center items-center gap-2">
                    <template v-for="(link, idx) in galeris.links" :key="idx">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-colors',
                                link.active 
                                    ? 'bg-amber-400 text-slate-950 shadow-md shadow-amber-400/20' 
                                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800'
                            ]"
                            v-html="link.label"
                        />
                        <span 
                            v-else 
                            class="px-3 py-2 text-xs text-slate-400 select-none"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </section>

            <!-- Empty State -->
            <section v-else class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900/40 p-16 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-500 flex items-center justify-center mb-4">
                    <Icon icon="solar:gallery-wide-bold-duotone" class="w-8 h-8" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Belum Ada Dokumentasi Kegiatan</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                    Kriteria pencarian atau kategori kegiatan yang Anda tentukan belum memiliki berkas foto dokumentasi.
                </p>
                <button 
                    @click="resetFilters"
                    class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-400 text-slate-950 text-xs font-bold shadow-md shadow-amber-400/20 hover:bg-amber-300 transition-colors"
                >
                    <Icon icon="solar:restart-bold" class="w-4 h-4" />
                    Reset Pencarian
                </button>
            </section>
        </div>
    </main>

    <!-- Modal Lightbox Preview Foto TKKSD -->
    <div 
        v-if="activeModalFoto" 
        class="fixed inset-0 z-[120] flex items-center justify-center bg-black/85 backdrop-blur-md p-4 animate-fade-in"
        @click.self="closeModal"
    >
        <div class="relative max-w-4xl w-full bg-slate-900 border border-slate-700/80 rounded-3xl overflow-hidden shadow-2xl">
            <!-- Close Button -->
            <button 
                @click="closeModal" 
                class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-black/60 text-white hover:bg-white hover:text-black flex items-center justify-center transition-colors"
                title="Tutup Preview (ESC)"
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
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-teal-500/20 text-teal-300 text-xs font-bold border border-teal-500/30">
                        {{ activeModalFoto.kategori_kegiatan || 'Rapat TKKSD' }}
                    </span>
                    <span v-if="activeModalFoto.tanggal_kegiatan" class="text-xs text-slate-400 flex items-center gap-1">
                        <Icon icon="solar:calendar-bold-duotone" class="w-3.5 h-3.5 text-teal-400" />
                        {{ formatTanggal(activeModalFoto.tanggal_kegiatan) }}
                    </span>
                    <span v-if="activeModalFoto.lokasi" class="text-xs text-slate-400 flex items-center gap-1">
                        <Icon icon="solar:map-point-bold-duotone" class="w-3.5 h-3.5 text-amber-400" />
                        {{ activeModalFoto.lokasi }}
                    </span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2 font-['Outfit']">
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
