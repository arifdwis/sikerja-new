<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { onMounted, onUnmounted, ref, computed, nextTick } from 'vue'
import Navbar from '@/Components/Landing/Navbar.vue'
import BenefitsSection from '@/Components/Landing/BenefitsSection.vue'
import FAQSection from '@/Components/Landing/FAQSection.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'

const props = defineProps({
    stats: { type: Object, default: () => ({ documents: 150, opds: 35, improvement: 85, uptime: '24/7' }) },
    sliders: { type: Array, default: () => [] },
    faqs: { type: Array, default: () => [] }
})

const safeStats = computed(() => {
    return props.stats || { documents: 150, opds: 35, improvement: 85, uptime: '24/7' }
})

const scrolled = ref(false)
const animatedStats = ref({
    documents: 0,
    opds: 0,
    improvement: 0
})
const statsAnimated = ref(false)

const animateCounter = (target, key, duration = 2000) => {
    const increment = target / (duration / 16)
    let current = 0
    const timer = setInterval(() => {
        current += increment
        if (current >= target) {
            animatedStats.value[key] = target
            clearInterval(timer)
        } else {
            animatedStats.value[key] = Math.floor(current)
        }
    }, 16)
}

const currentSliderIndex = ref(0)
let sliderInterval = null
const currentSlider = computed(() => {
    if (props.sliders.length > 0) {
        return props.sliders[currentSliderIndex.value]
    }
    return null
})

const nextSlide = () => {
    if (props.sliders.length < 2) return
    currentSliderIndex.value = (currentSliderIndex.value + 1) % props.sliders.length
}

const prevSlide = () => {
    if (props.sliders.length < 2) return
    currentSliderIndex.value = (currentSliderIndex.value - 1 + props.sliders.length) % props.sliders.length
}

const goToSlide = (index) => {
    if (props.sliders.length < 2) return
    currentSliderIndex.value = index
}

// Video lazy-load: show thumbnail, load iframe on click
const activeVideos = ref({})
const activateVideo = (id) => {
    activeVideos.value[id] = true
}

// Lightweight reveal animation (replaces AOS)
let revealObserver = null
const initRevealAnimations = () => {
    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed')
                revealObserver.unobserve(entry.target)
            }
        })
    }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' })

    document.querySelectorAll('.reveal').forEach(el => {
        revealObserver.observe(el)
    })
}

onMounted(() => {
    window.addEventListener('scroll', () => scrolled.value = window.scrollY > 50, { passive: true })

    if (props.sliders.length > 1) {
        sliderInterval = setInterval(() => {
            nextSlide()
        }, 5500)
    }
    
    // Stats counter observer
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !statsAnimated.value) {
                statsAnimated.value = true
                animateCounter(safeStats.value.documents, 'documents', 2000)
                animateCounter(safeStats.value.opds, 'opds', 2000)
                animateCounter(safeStats.value.improvement, 'improvement', 2000)
            }
        })
    }, { threshold: 0.3 })

    nextTick(() => {
        const statsElement = document.getElementById('stats-hero')
        if (statsElement) statsObserver.observe(statsElement)
        initRevealAnimations()
    })
})

onUnmounted(() => {
    if (sliderInterval) clearInterval(sliderInterval)
    if (revealObserver) revealObserver.disconnect()
})

const steps = [
    { label: "Inisiasi", by: "Perangkat Daerah / Mitra", desc: "Usulan kerjasama diajukan beserta ruang lingkup dan target keluaran.", icon: "solar:upload-bold" },
    { label: "Validasi", by: "Bagian Kerjasama", desc: "Kelengkapan administrasi, legalitas, dan kesesuaian dokumen diverifikasi.", icon: "solar:checklist-minimalistic-bold" },
    { label: "Konsolidasi", by: "Tim Teknis", desc: "Sinkronisasi kebutuhan lintas OPD untuk memastikan dampak program terukur.", icon: "solar:users-group-rounded-bold" },
    { label: "Penjadwalan", by: "Sekretariat", desc: "Agenda pembahasan dan penandatanganan disusun secara terkoordinasi.", icon: "solar:calendar-date-bold" },
    { label: "Pengesahan", by: "Pimpinan Daerah", desc: "Naskah final disetujui sebagai dasar pelaksanaan dan pengendalian.", icon: "solar:pen-bold" },
    { label: "Implementasi & Monev", by: "OPD Pengampu", desc: "Pelaksanaan berjalan dengan monitoring berkala dan pelaporan berbasis data.", icon: "solar:graph-up-bold" }
]

</script>

<template>
    <Head title="Sikerja - Sistem Informasi Kerjasama Daerah" />

    <Navbar />

    <main class="font-['Inter'] antialiased bg-[#f4f6f8] text-gray-900 overflow-x-hidden">
        <section id="hero" class="relative min-h-screen flex items-center overflow-hidden pt-20">
            <div v-if="currentSlider" class="absolute inset-0 transition-opacity duration-1000 ease-in-out">
                <img :src="'/storage/' + currentSlider.file.replace(/^storage\//, '')" class="w-full h-full object-cover" alt="Hero Background">
            </div>
            <div v-else class="absolute inset-0 bg-slate-900"></div>

            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/80 to-slate-900/65"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_25%,rgba(250,204,21,0.18),transparent_42%),radial-gradient(circle_at_80%_70%,rgba(56,189,248,0.18),transparent_42%)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:48px_48px] opacity-20"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 py-20 lg:py-24 grid lg:grid-cols-[1.25fr_0.95fr] gap-12 items-center">
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 mb-8 text-xs sm:text-sm font-bold tracking-[0.18em] text-amber-100 bg-white/10 backdrop-blur-md rounded-full border border-white/20 uppercase shadow-inner">
                        <Icon icon="solar:star-fall-bold-duotone" class="w-4 h-4 text-amber-400" />
                        {{ currentSlider ? 'Highlight Terkini' : 'Kolaborasi Pemkot Samarinda' }}
                    </div>

                    <h1 class="font-['Outfit'] text-5xl sm:text-6xl xl:text-7xl font-black tracking-tight mb-4 leading-[1.02] text-white drop-shadow-2xl">
                        Sikerja
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-amber-400 to-amber-200">Samarinda</span>
                    </h1>

                    <p class="text-slate-300 text-base sm:text-lg font-medium max-w-2xl mb-8 leading-relaxed">
                        Sistem Informasi Kerjasama Daerah Kota Samarinda. Satu wadah digital terintegrasi untuk mendata, memantau, dan mengevaluasi kolaborasi pembangunan daerah secara transparan, akuntabel, dan efisien.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <Link href="/login" class="group relative inline-flex items-center justify-center px-8 py-4 text-base lg:text-lg font-semibold rounded-2xl bg-amber-400 text-slate-900 shadow-xl shadow-amber-500/10 hover:shadow-amber-400/30 hover:-translate-y-0.5 transition-all duration-300 border border-amber-300/40">
                            Masuk Aplikasi
                            <Icon icon="solar:login-3-bold" class="ml-2 w-5 h-5 transition-transform group-hover:translate-x-1" />
                        </Link>
                        <Link href="/tentang" class="inline-flex items-center justify-center px-8 py-4 text-base lg:text-lg font-semibold rounded-2xl bg-white/5 border border-white/15 backdrop-blur-md text-white hover:bg-white/10 hover:border-white/30 transition-all duration-300">
                            Lihat Cerita Sikerja
                        </Link>
                    </div>

                    <div v-if="props.sliders.length > 1" class="flex items-center gap-3 mb-12">
                        <button @click="prevSlide" type="button" class="w-10 h-10 rounded-xl bg-white/5 border border-white/15 text-white hover:bg-white/10 hover:border-white/30 transition-all" aria-label="Slide sebelumnya">
                            <Icon icon="solar:alt-arrow-left-bold" class="w-5 h-5 mx-auto" />
                        </button>
                        <div class="flex items-center gap-2">
                            <button
                                v-for="(item, idx) in props.sliders"
                                :key="`dot-${idx}`"
                                type="button"
                                @click="goToSlide(idx)"
                                class="h-2.5 rounded-full transition-all duration-300"
                                :class="idx === currentSliderIndex ? 'w-10 bg-amber-400' : 'w-2.5 bg-white/30 hover:bg-white/60'"
                                :aria-label="`Pindah ke slide ${idx + 1}`"
                            ></button>
                        </div>
                        <button @click="nextSlide" type="button" class="w-10 h-10 rounded-xl bg-white/5 border border-white/15 text-white hover:bg-white/10 hover:border-white/30 transition-all" aria-label="Slide selanjutnya">
                            <Icon icon="solar:alt-arrow-right-bold" class="w-5 h-5 mx-auto" />
                        </button>
                    </div>

                    <div id="stats-hero" class="grid grid-cols-2 sm:grid-cols-4 gap-4 lg:gap-6" v-if="safeStats && animatedStats">
                        <div class="relative bg-white/5 border border-white/10 rounded-2xl p-4 text-center group transition-all duration-300 hover:bg-white/10 hover:border-amber-400/40 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/5 reveal">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400/0 via-amber-400/0 to-amber-400/5 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                            <div class="w-12 h-12 lg:w-14 lg:h-14 mx-auto mb-3 bg-white/5 rounded-xl flex items-center justify-center border border-white/10 group-hover:border-amber-400/40 group-hover:bg-amber-400/10 transition-all duration-300">
                                <Icon icon="solar:document-bold-duotone" class="w-6 h-6 lg:w-7 lg:h-7 text-amber-300 group-hover:scale-110 transition-transform duration-300" />
                            </div>
                            <div class="text-3xl lg:text-4xl font-['Outfit'] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-amber-200 drop-shadow-sm mb-0.5">{{ animatedStats.documents }}+</div>
                            <div class="text-[10px] lg:text-[11px] font-bold text-slate-300 uppercase tracking-widest">Kerjasama</div>
                        </div>
                        <div class="relative bg-white/5 border border-white/10 rounded-2xl p-4 text-center group transition-all duration-300 hover:bg-white/10 hover:border-amber-400/40 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/5 reveal reveal-d1">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400/0 via-amber-400/0 to-amber-400/5 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                            <div class="w-12 h-12 lg:w-14 lg:h-14 mx-auto mb-3 bg-white/5 rounded-xl flex items-center justify-center border border-white/10 group-hover:border-amber-400/40 group-hover:bg-amber-400/10 transition-all duration-300">
                                <Icon icon="solar:buildings-bold-duotone" class="w-6 h-6 lg:w-7 lg:h-7 text-amber-300 group-hover:scale-110 transition-transform duration-300" />
                            </div>
                            <div class="text-3xl lg:text-4xl font-['Outfit'] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-amber-200 drop-shadow-sm mb-0.5">{{ animatedStats.opds }}+</div>
                            <div class="text-[10px] lg:text-[11px] font-bold text-slate-300 uppercase tracking-widest">Mitra</div>
                        </div>
                        <div class="relative bg-white/5 border border-white/10 rounded-2xl p-4 text-center group transition-all duration-300 hover:bg-white/10 hover:border-amber-400/40 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/5 reveal reveal-d2">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400/0 via-amber-400/0 to-amber-400/5 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                            <div class="w-12 h-12 lg:w-14 lg:h-14 mx-auto mb-3 bg-white/5 rounded-xl flex items-center justify-center border border-white/10 group-hover:border-amber-400/40 group-hover:bg-amber-400/10 transition-all duration-300">
                                <Icon icon="solar:check-circle-bold-duotone" class="w-6 h-6 lg:w-7 lg:h-7 text-amber-300 group-hover:scale-110 transition-transform duration-300" />
                            </div>
                            <div class="text-3xl lg:text-4xl font-['Outfit'] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-amber-200 drop-shadow-sm mb-0.5">{{ animatedStats.improvement }}+</div>
                            <div class="text-[10px] lg:text-[11px] font-bold text-slate-300 uppercase tracking-widest">Selesai</div>
                        </div>
                        <div class="relative bg-white/5 border border-white/10 rounded-2xl p-4 text-center group transition-all duration-300 hover:bg-white/10 hover:border-amber-400/40 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/5 reveal reveal-d3">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400/0 via-amber-400/0 to-amber-400/5 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                            <div class="w-12 h-12 lg:w-14 lg:h-14 mx-auto mb-3 bg-white/5 rounded-xl flex items-center justify-center border border-white/10 group-hover:border-amber-400/40 group-hover:bg-amber-400/10 transition-all duration-300">
                                <Icon icon="solar:clock-circle-bold-duotone" class="w-6 h-6 lg:w-7 lg:h-7 text-amber-300 group-hover:scale-110 transition-transform duration-300" />
                            </div>
                            <div class="text-3xl lg:text-4xl font-['Outfit'] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-amber-200 drop-shadow-sm mb-0.5">{{ safeStats?.uptime || '24/7' }}</div>
                            <div class="text-[10px] lg:text-[11px] font-bold text-slate-300 uppercase tracking-widest">Layanan</div>
                        </div>
                    </div>
                </div>

                <div class="reveal reveal-d2">
                    <div class="relative max-w-xl mx-auto lg:mx-0">
                        <div class="absolute -top-8 -right-8 w-44 h-44 bg-amber-300/10 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-10 -left-8 w-48 h-48 bg-sky-300/10 rounded-full blur-3xl"></div>

                        <div class="relative bg-slate-950/60 backdrop-blur-xl border border-white/10 rounded-[32px] overflow-hidden shadow-2xl">
                            <div class="p-6 sm:p-7 border-b border-white/10 flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] font-bold text-amber-300 mb-2">Jalur Kerja Sama</p>
                                    <h3 class="font-['Outfit'] text-2xl font-bold text-white leading-tight">Peta Kolaborasi Sikerja</h3>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-300 to-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/20 shrink-0">
                                    <Icon icon="solar:route-bold-duotone" class="w-7 h-7 text-slate-950" />
                                </div>
                            </div>

                            <div class="p-6 sm:p-7 space-y-6">
                                <div class="relative pl-10 pb-6 border-l border-white/10">
                                    <span class="absolute -left-[9px] top-0 w-4.5 h-4.5 rounded-full bg-emerald-500 border-4 border-slate-950 ring-4 ring-emerald-400/20"></span>
                                    <p class="text-sm font-semibold text-white">Inisiasi Program</p>
                                    <p class="text-xs text-slate-400 mt-1">Kebutuhan bersama dirumuskan lintas OPD dan mitra secara taktis.</p>
                                </div>
                                <div class="relative pl-10 pb-6 border-l border-white/10">
                                    <span class="absolute -left-[9px] top-0 w-4.5 h-4.5 rounded-full bg-sky-400 border-4 border-slate-950 ring-4 ring-sky-300/20"></span>
                                    <p class="text-sm font-semibold text-white">Konsolidasi & Legal Review</p>
                                    <p class="text-xs text-slate-400 mt-1">Draft disinkronkan dengan regulasi daerah dan sasaran pembangunan.</p>
                                </div>
                                <div class="relative pl-10">
                                    <span class="absolute -left-[9px] top-0 w-4.5 h-4.5 rounded-full bg-amber-400 border-4 border-slate-950 ring-4 ring-amber-400/30 animate-pulse"></span>
                                    <p class="text-sm font-semibold text-white">Implementasi Terukur</p>
                                    <p class="text-xs text-slate-400 mt-1">Progres dipantau real-time hingga monitoring evaluasi berkala.</p>
                                </div>
                            </div>

                            <div class="px-6 sm:px-7 pb-6 sm:pb-7 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-white/5 border border-white/10 p-4 transition-colors hover:bg-white/10">
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Status</p>
                                    <p class="text-white text-sm font-bold">Aktif & Terpantau</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 border border-white/10 p-4 transition-colors hover:bg-white/10">
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Update</p>
                                    <p class="text-white text-sm font-bold">Realtime Dashboard</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Video Gallery Section -->
        <section id="video-gallery" class="py-24 bg-slate-950 text-white relative overflow-hidden">
            <!-- Background Accents (lightweight) -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-teal-500/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-500/5 rounded-full blur-3xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="text-center mb-16 reveal">
                    <span class="inline-block py-1.5 px-4 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-teal-400 font-bold tracking-wider uppercase text-xs mb-4 shadow-lg shadow-black/10">
                        Media Center
                    </span>
                    <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight mb-6 leading-tight">
                        Galeri <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-amber-200">Video</span>
                    </h2>
                    <p class="text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
                        Dokumentasi visual mengenai panduan penggunaan sistem dan pesona Kota Samarinda.
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Video 1: Manual Kerjasama -->
                    <div class="group relative reveal">
                        <div class="relative bg-slate-900/60 backdrop-blur-md rounded-[2rem] border border-white/10 overflow-hidden shadow-2xl transition-all duration-500 hover:border-amber-400/35 hover:-translate-y-2">
                            <div class="relative aspect-video overflow-hidden">
                                <!-- Lazy-load: show thumbnail, load iframe on click -->
                                <iframe 
                                    v-if="activeVideos['video1']"
                                    class="w-full h-full object-cover"
                                    src="https://www.youtube.com/embed/RkE7RkPr7Og?si=hE6BcnUXVZkJyJkF&autoplay=1" 
                                    title="Manual Kerjasama" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    referrerpolicy="strict-origin-when-cross-origin" 
                                    allowfullscreen>
                                </iframe>
                                <div v-else @click="activateVideo('video1')" class="w-full h-full cursor-pointer relative group/thumb">
                                    <img src="https://img.youtube.com/vi/RkE7RkPr7Og/maxresdefault.jpg" alt="Manual Kerjasama" class="w-full h-full object-cover transition-transform duration-700 group-hover/thumb:scale-105" />
                                    <div class="absolute inset-0 bg-black/45 flex items-center justify-center hover:bg-black/55 transition-colors">
                                        <div class="w-20 h-20 bg-amber-400 text-slate-955 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform duration-300">
                                            <Icon icon="solar:play-bold" class="w-10 h-10 ml-1" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Overlay Label -->
                                <div class="absolute top-4 left-4 z-10">
                                     <span class="px-3 py-1.5 bg-slate-950/75 backdrop-blur-md text-white/90 text-xs font-bold rounded-lg border border-white/10 flex items-center gap-2">
                                        <Icon icon="solar:book-bookmark-bold-duotone" class="text-teal-400 w-4 h-4" />
                                        Panduan
                                     </span>
                                </div>
                            </div>
                            <div class="p-8">
                                <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-amber-300 transition-colors">
                                    Manual Kerjasama
                                </h3>
                                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                                    Pelajari langkah-langkah pengajuan dan pengelolaan kerjasama daerah. Panduan lengkap untuk memaksimalkan penggunaan Sikerja.
                                </p>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 group-hover:text-amber-300 transition-colors cursor-pointer">
                                    <Icon icon="solar:play-circle-bold-duotone" class="w-5 h-5" />
                                    <span>Tonton Sekarang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video 2: Jelajah Samarinda -->
                    <div class="group relative reveal reveal-d1">
                        <div class="relative bg-slate-900/60 backdrop-blur-md rounded-[2rem] border border-white/10 overflow-hidden shadow-2xl transition-all duration-500 hover:border-amber-400/35 hover:-translate-y-2">
                            <div class="relative aspect-video overflow-hidden">
                                <!-- Lazy-load: show thumbnail, load iframe on click -->
                                <iframe 
                                    v-if="activeVideos['video2']"
                                    class="w-full h-full object-cover"
                                    src="https://www.youtube.com/embed/aQIZWUcM2jo?si=PrktvIb-vtV8Nr8k&autoplay=1" 
                                    title="Jelajah Samarinda" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    referrerpolicy="strict-origin-when-cross-origin" 
                                    allowfullscreen>
                                </iframe>
                                <div v-else @click="activateVideo('video2')" class="w-full h-full cursor-pointer relative group/thumb">
                                    <img src="https://img.youtube.com/vi/aQIZWUcM2jo/maxresdefault.jpg" alt="Jelajah Samarinda" class="w-full h-full object-cover transition-transform duration-700 group-hover/thumb:scale-105" />
                                    <div class="absolute inset-0 bg-black/45 flex items-center justify-center hover:bg-black/55 transition-colors">
                                        <div class="w-20 h-20 bg-amber-400 text-slate-955 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform duration-300">
                                            <Icon icon="solar:play-bold" class="w-10 h-10 ml-1" />
                                        </div>
                                    </div>
                                </div>
                                 <!-- Overlay Label -->
                                <div class="absolute top-4 left-4 z-10">
                                     <span class="px-3 py-1.5 bg-slate-950/75 backdrop-blur-md text-white/90 text-xs font-bold rounded-lg border border-white/10 flex items-center gap-2">
                                        <Icon icon="solar:map-point-bold-duotone" class="text-orange-400 w-4 h-4" />
                                        Wisata
                                     </span>
                                </div>
                            </div>
                            <div class="p-8">
                                <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-amber-300 transition-colors">
                                    Jelajah Kota Samarinda
                                </h3>
                                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                                    Pusat Peradaban, Jantung Perdagangan. Temukan pesona keindahan Sungai Mahakam, keragaman budaya, dan dinamika pembangunan Kota Tepian.
                                </p>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 group-hover:text-amber-300 transition-colors cursor-pointer">
                                    <Icon icon="solar:play-circle-bold-duotone" class="w-5 h-5" />
                                    <span>Tonton Sekarang</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tentang" class="py-24 bg-white dark:bg-slate-950">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="reveal">
                        <span class="inline-block py-1 px-3 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 font-bold tracking-wider uppercase text-xs mb-4">
                            Tentang Aplikasi
                        </span>
                        <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight mb-6 text-slate-900 dark:text-white leading-tight">
                            Apa itu <span class="text-teal-600">SIKERJA?</span>
                        </h2>
                        
                        <div class="prose prose-lg text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
                            <p class="mb-4 text-justify">
                                <span class="font-bold text-slate-900 dark:text-white">Sistem Informasi Kerjasama Daerah (SIKERJA)</span> adalah platform digital yang dibangun oleh Pemerintah Kota Samarinda untuk mendukung penyelenggaraan Sistem Pemerintahan Berbasis Elektronik (SPBE).
                            </p>
                            <p class="mb-4 text-justify">
                                Sistem ini dirancang untuk mendata, memonitor, dan mengevaluasi seluruh kerjasama daerah secara terintegrasi. Dikelola oleh <span class="font-semibold text-teal-600">Bagian Kerja Sama Sekretariat Daerah Kota Samarinda</span>, SIKERJA memfasilitasi efisiensi layanan melalui digitalisasi proses untuk seluruh Perangkat Daerah dan Mitra.
                            </p>
                            <p class="text-justify bg-teal-50/50 dark:bg-slate-900/40 p-4 rounded-2xl border border-teal-100/50 dark:border-teal-900/20 italic text-base text-slate-700 dark:text-slate-300">
                                "Wujud nyata keterbukaan informasi publik yang akuntabel dan transparan dalam mendukung tercapainya Good Governance di Kota Samarinda."
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-4">
                             <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
                                <Icon icon="solar:monitor-smartphone-bold-duotone" class="text-teal-500 w-5 h-5" />
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Digitalisasi</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
                                <Icon icon="solar:graph-up-bold-duotone" class="text-blue-500 w-5 h-5" />
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Monitoring</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
                                <Icon icon="solar:shield-check-bold-duotone" class="text-orange-500 w-5 h-5" />
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Evaluasi</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 reveal">
                        <div class="space-y-6 mt-8">
                            <div class="bg-slate-50 dark:bg-slate-900/40 p-6 rounded-2xl border border-slate-100 dark:border-slate-900 hover:border-teal-500/50 dark:hover:border-teal-500/40 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                                <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-sm flex items-center justify-center mb-4 text-teal-600 border border-slate-100 dark:border-slate-700 group-hover:scale-105 transition-transform">
                                    <Icon icon="solar:cloud-upload-bold-duotone" class="w-6 h-6" />
                                </div>
                                <h3 class="font-extrabold text-lg mb-2 text-slate-900 dark:text-white">Digital</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Paperless office, hemat biaya dan ramah lingkungan.</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-900/40 p-6 rounded-2xl border border-slate-100 dark:border-slate-900 hover:border-teal-500/50 dark:hover:border-teal-500/40 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                                <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-sm flex items-center justify-center mb-4 text-teal-600 border border-slate-100 dark:border-slate-700 group-hover:scale-105 transition-transform">
                                    <Icon icon="solar:shield-check-bold-duotone" class="w-6 h-6" />
                                </div>
                                <h3 class="font-extrabold text-lg mb-2 text-slate-900 dark:text-white">Aman</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Keamanan data terjamin dengan enkripsi standar industri.</p>
                            </div>
                        </div>
                         <div class="space-y-6">
                            <div class="bg-slate-50 dark:bg-slate-900/40 p-6 rounded-2xl border border-slate-100 dark:border-slate-900 hover:border-teal-500/50 dark:hover:border-teal-500/40 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                                <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-sm flex items-center justify-center mb-4 text-teal-600 border border-slate-100 dark:border-slate-700 group-hover:scale-105 transition-transform">
                                    <Icon icon="solar:rocket-2-bold-duotone" class="w-6 h-6" />
                                </div>
                                <h3 class="font-extrabold text-lg mb-2 text-slate-900 dark:text-white">Cepat</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Memangkas birokrasi, mempercepat proses persetujuan.</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-900/40 p-6 rounded-2xl border border-slate-100 dark:border-slate-900 hover:border-teal-500/50 dark:hover:border-teal-500/40 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                                <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-sm flex items-center justify-center mb-4 text-teal-600 border border-slate-100 dark:border-slate-700 group-hover:scale-105 transition-transform">
                                    <Icon icon="solar:archive-check-bold-duotone" class="w-6 h-6" />
                                </div>
                                <h3 class="font-extrabold text-lg mb-2 text-slate-900 dark:text-white">Terdata</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">Database lengkap untuk pengambilan keputusan yang lebih baik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="alur" class="py-24 bg-slate-50 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <span class="inline-block py-1 px-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 font-bold tracking-wider uppercase text-xs mb-4">
                        Jalur Kolaborasi
                    </span>
                    <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight mt-2 mb-4 text-slate-900 dark:text-white">
                        Alur Kerjasama
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
                        Alur berlapis dengan titik kendali yang jelas, agar kerja sama tidak berhenti di dokumen tetapi sampai hasil nyata.
                    </p>
                </div>

                <div class="hidden lg:block reveal">
                    <div class="relative">
                        <div class="absolute left-0 right-0 top-10 h-[3px] bg-slate-200 dark:bg-slate-800"></div>
                        <div class="absolute left-0 top-10 h-[3px] bg-gradient-to-r from-amber-400 to-amber-500 w-full opacity-80"></div>
                        <div class="grid grid-cols-6 gap-5">
                            <div v-for="(s, i) in steps" :key="`desktop-${i}`" class="relative pt-16 group">
                                <div class="absolute top-6 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-slate-950 border-4 border-amber-400 shadow shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300 z-10"></div>
                                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm h-full hover:shadow-xl hover:border-amber-400/30 transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex items-center gap-2 mb-3 text-slate-500 dark:text-slate-400">
                                        <Icon :icon="s.icon" class="w-5 h-5 text-amber-500" />
                                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Tahap {{ i + 1 }}</span>
                                    </div>
                                    <h3 class="font-bold text-base text-slate-900 dark:text-white mb-1">{{ s.label }}</h3>
                                    <p class="text-xs font-semibold text-teal-600 dark:text-teal-400 mb-2">{{ s.by }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ s.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:hidden space-y-4 reveal">
                    <div v-for="(s, i) in steps" :key="`mobile-${i}`" class="relative pl-12">
                        <div class="absolute left-4 top-0 bottom-0 w-[2px] bg-slate-200 dark:bg-slate-800" v-if="i !== steps.length - 1"></div>
                        <div class="absolute left-0 top-1 w-8 h-8 rounded-full bg-amber-400 text-slate-955 font-black text-sm flex items-center justify-center shadow shadow-amber-400/20 z-10">{{ i + 1 }}</div>
                        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <Icon :icon="s.icon" class="w-5 h-5 text-amber-500" />
                                <h3 class="font-bold text-slate-900 dark:text-white">{{ s.label }}</h3>
                            </div>
                            <p class="text-xs font-semibold text-teal-600 dark:text-teal-400 mb-2">{{ s.by }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ s.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <BenefitsSection />
        <FAQSection :faqs="faqs" />
    </main>

    <Footer />
    <ChatWidget />
</template>

<style>
/* Lightweight reveal animations (replaces AOS) */
.reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform, opacity;
}

.reveal.revealed {
    opacity: 1;
    transform: translateY(0);
}

/* Staggered delays */
.reveal.reveal-d1 { transition-delay: 0.1s; }
.reveal.reveal-d2 { transition-delay: 0.2s; }
.reveal.reveal-d3 { transition-delay: 0.3s; }


</style>
