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
    faqs: { type: Array, default: () => [] },
    kategoriMitras: { type: Array, default: () => [] },
    galeriTkksd: { type: Array, default: () => [] }
})

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

const openModalFoto = (item) => {
    activeModalFoto.value = item
    document.body.style.overflow = 'hidden'
}

const closeModalFoto = () => {
    activeModalFoto.value = null
    document.body.style.overflow = 'auto'
}

const selectedKategoriId = ref('all')
const allMitras = computed(() => {
    if (!props.kategoriMitras) return []
    let list = []
    props.kategoriMitras.forEach(kat => {
        if (kat.active_mitras || kat.mitras) {
            const items = kat.active_mitras || kat.mitras || []
            items.forEach(m => {
                list.push({ ...m, kategori_nama: kat.nama })
            })
        }
    })
    return list
})

const filteredMitras = computed(() => {
    if (selectedKategoriId.value === 'all') {
        return allMitras.value
    }
    const kat = props.kategoriMitras.find(k => k.id === selectedKategoriId.value)
    if (!kat) return []
    const items = kat.active_mitras || kat.mitras || []
    return items.map(m => ({ ...m, kategori_nama: kat.nama }))
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
    { label: "1. Pengajuan Usulan", by: "Pemohon / Perangkat Daerah", desc: "Usulan kerja sama diajukan dengan mengisi profil instansi, data pimpinan & 2 PIC, serta berkas permohonan, proposal, dan legalitas.", icon: "solar:document-add-bold" },
    { label: "2. Verifikasi & Telaah", by: "Bagian Kerja Sama", desc: "Pemeriksaan kelengkapan berkas administrasi oleh staf admin dan verifikasi/validasi oleh Kabag Kerja Sama.", icon: "solar:checklist-minimalistic-bold" },
    { label: "3. Pembahasan Draf KB", by: "TKKSD & Bagian Kerja Sama", desc: "Sinkronisasi kebutuhan dan kajian substansi draf Kesepakatan Bersama bersama TKKSD dan mitra.", icon: "solar:users-group-rounded-bold" },
    { label: "4. Penandatanganan KB", by: "Walikota Samarinda & Mitra", desc: "Penyesuaian jadwal dan penandatanganan naskah Kesepakatan Bersama (MoU) sebagai payung hukum induk.", icon: "solar:pen-bold" },
    { label: "5. Pembahasan Draf PKS", by: "OPD Pengampu & Mitra", desc: "Penyusunan dan pembahasan teknis rancangan Perjanjian Kerja Sama turunan oleh OPD pengampu urusan.", icon: "solar:documents-minimalistic-bold" },
    { label: "6. Penandatanganan PKS", by: "Kepala OPD Pengampu & Mitra", desc: "Penandatanganan dokumen teknis kerja sama operasional oleh Kepala Perangkat Daerah dan mitra.", icon: "solar:diploma-verified-bold" },
    { label: "7. Pelaksanaan Kerja Sama", by: "OPD Pengampu & Mitra", desc: "Realisasi kegiatan program di lapangan serta penyampaian laporan kegiatan berkala per semester.", icon: "solar:running-2-bold" },
    { label: "8. Evaluasi & Monev", by: "TKKSD & Bagian Kerja Sama", desc: "Pengendalian masa aktif, evaluasi berkala capaian manfaat, serta rekomendasi perpanjangan/pengakhiran.", icon: "solar:clipboard-check-bold" }
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
                    <div class="grid grid-cols-4 gap-6">
                        <div v-for="(s, i) in steps" :key="`desktop-${i}`" class="relative group">
                            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm h-full hover:shadow-xl hover:border-amber-400/40 transition-all duration-300 hover:-translate-y-1.5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3 text-slate-500 dark:text-slate-400">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-400/10 flex items-center justify-center text-amber-500 dark:text-amber-400">
                                                <Icon :icon="s.icon" class="w-4 h-4" />
                                            </div>
                                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">Tahap {{ i + 1 }}</span>
                                        </div>
                                        <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-black flex items-center justify-center text-slate-600 dark:text-slate-300">{{ i + 1 }}</span>
                                    </div>
                                    <h3 class="font-bold text-base text-slate-900 dark:text-white mb-1 leading-snug">{{ s.label }}</h3>
                                    <p class="text-xs font-semibold text-teal-600 dark:text-teal-400 mb-2.5">{{ s.by }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed text-justify">{{ s.desc }}</p>
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



        <!-- Section: Pihak yang Bekerjasama dengan Pemkot Samarinda (Our Partners) -->
        <section id="mitra" class="py-24 bg-white dark:bg-slate-950 relative overflow-hidden">
            <!-- Background Decorative Glow -->
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-14 reveal">
                    <span class="inline-block py-1 px-3 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 font-bold tracking-wider uppercase text-xs mb-4">
                        Mitra Kerja Sama
                    </span>
                    <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight text-slate-900 dark:text-white">
                        Pihak yang Bekerjasama <br class="hidden sm:inline" />
                        <span class="text-teal-600">dengan Pemkot Samarinda</span>
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg max-w-2xl mx-auto mt-4 leading-relaxed">
                        Sinergi Pemerintah Kota Samarinda bersama pemerintah daerah, badan usaha, organisasi, perguruan tinggi, dan mitra internasional.
                    </p>
                </div>

                <!-- Category Tabs (Ref UCLG-ASPAC) -->
                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 mb-12 reveal">
                    <button 
                        @click="selectedKategoriId = 'all'"
                        :class="[
                            'px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 flex items-center gap-2 border shadow-sm',
                            selectedKategoriId === 'all'
                                ? 'bg-teal-600 text-white border-teal-600 shadow-teal-500/20 shadow-md ring-2 ring-teal-500/30'
                                : 'bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400'
                        ]"
                    >
                        <span>Semua Kemitraan</span>
                        <span :class="['px-2 py-0.5 rounded-full text-[11px] font-black', selectedKategoriId === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300']">
                            {{ allMitras.length }}
                        </span>
                    </button>

                    <button 
                        v-for="kat in kategoriMitras" 
                        :key="`tab-kat-${kat.id}`"
                        @click="selectedKategoriId = kat.id"
                        :class="[
                            'px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 flex items-center gap-2 border shadow-sm',
                            selectedKategoriId === kat.id
                                ? 'bg-teal-600 text-white border-teal-600 shadow-teal-500/20 shadow-md ring-2 ring-teal-500/30'
                                : 'bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400'
                        ]"
                    >
                        <span>{{ kat.nama }}</span>
                        <span :class="['px-2 py-0.5 rounded-full text-[11px] font-black', selectedKategoriId === kat.id ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300']">
                            {{ (kat.active_mitras || kat.mitras || []).length }}
                        </span>
                    </button>
                </div>

                <!-- Partners Logo Grid (Inspired by UCLG-ASPAC) -->
                <div v-if="filteredMitras.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 reveal">
                    <div 
                        v-for="mitra in filteredMitras" 
                        :key="`mitra-${mitra.id}`"
                        class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/90 p-5 flex flex-col justify-between shadow-sm hover:shadow-xl hover:border-teal-500/50 dark:hover:border-teal-500/40 hover:-translate-y-1.5 transition-all duration-300"
                    >
                        <!-- Category Tiny Badge -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-extrabold tracking-wide uppercase px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 line-clamp-1">
                                {{ mitra.kategori_nama }}
                            </span>
                            <a 
                                v-if="mitra.url_website" 
                                :href="mitra.url_website" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                                :title="`Kunjungi situs resmi ${mitra.nama}`"
                            >
                                <Icon icon="solar:arrow-right-up-bold-duotone" class="w-4 h-4" />
                            </a>
                        </div>

                        <!-- Logo Frame -->
                        <div class="h-28 flex items-center justify-center p-2 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 mb-3 border border-slate-100 dark:border-slate-800/60 overflow-hidden">
                            <img 
                                v-if="mitra.logo" 
                                :src="'/' + mitra.logo.replace(/^\/+/, '')" 
                                :alt="mitra.nama" 
                                class="max-h-20 max-w-full object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div v-else class="w-14 h-14 rounded-2xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center font-black text-xl">
                                {{ mitra.nama.charAt(0) }}
                            </div>
                        </div>

                        <!-- Mitra Details -->
                        <div class="text-center">
                            <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors line-clamp-2 leading-tight">
                                {{ mitra.nama }}
                            </h3>
                            <p v-if="mitra.deskripsi" class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-snug">
                                {{ mitra.deskripsi }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- View All Partners Link -->
                <div class="mt-12 text-center reveal">
                    <Link 
                        href="/mitra" 
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-slate-900 dark:bg-white text-white dark:text-slate-950 font-bold text-xs sm:text-sm shadow-lg hover:bg-teal-600 dark:hover:bg-teal-400 hover:text-white dark:hover:text-slate-950 hover:scale-[1.02] transition-all duration-300"
                    >
                        <Icon icon="solar:users-group-two-rounded-bold-duotone" class="w-4 h-4 text-amber-400 dark:text-amber-500" />
                        <span>Lihat Seluruh Katalog Mitra Kerja Sama ({{ allMitras.length }})</span>
                        <Icon icon="solar:arrow-right-line-duotone" class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Section: Galeri Kegiatan TKKSD Kota Samarinda -->
        <section class="py-20 bg-slate-50 dark:bg-slate-950/80 border-t border-slate-200/60 dark:border-slate-800/60 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Section Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-100 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 text-xs font-bold uppercase tracking-wider mb-3 border border-teal-200/50 dark:border-teal-800/50">
                            <Icon icon="solar:gallery-bold-duotone" class="w-4 h-4" />
                            Dokumentasi Visual Resmi
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-950 dark:text-white font-['Outfit'] tracking-tight">
                            Galeri Kegiatan TKKSD
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-2 max-w-2xl leading-relaxed">
                            Potret pelaksanaan koordinasi terpadu Tim Koordinasi Kerja Sama Daerah (TKKSD) Kota Samarinda dalam perumusan kebijakan, verifikasi usulan, pembahasan kesepakatan, dan pemantauan monev.
                        </p>
                    </div>

                    <!-- Direct Link to Dedicated Gallery Page -->
                    <div class="shrink-0">
                        <Link 
                            href="/galeri-tkksd"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 font-bold text-xs sm:text-sm shadow-sm transition-all hover:scale-[1.02]"
                        >
                            <span>Buka Halaman Galeri</span>
                            <Icon icon="solar:arrow-right-line-duotone" class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Gallery Cards Grid -->
                <div v-if="galeriTkksd && galeriTkksd.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="item in galeriTkksd.slice(0, 6)" 
                        :key="`home-galeri-${item.id}`"
                        @click="openModalFoto(item)"
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
                                <div class="w-11 h-11 rounded-full bg-black/60 backdrop-blur-md text-white flex items-center justify-center border border-white/30 transform scale-75 group-hover:scale-100 transition-transform">
                                    <Icon icon="solar:magnifer-zoom-in-bold" class="w-5 h-5 text-amber-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors line-clamp-2 mb-2 font-['Outfit']">
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

                <div v-else class="text-center py-16 px-6 bg-white dark:bg-slate-900/50 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-4">
                        <Icon icon="solar:gallery-wide-bold-duotone" class="w-8 h-8" />
                    </div>
                    <h4 class="text-base font-bold text-slate-700 dark:text-slate-300">Dokumentasi kegiatan sedang diperbarui</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                        Foto kegiatan terbaru Tim Koordinasi Kerja Sama Daerah Kota Samarinda akan tampil secara berkala di sini.
                    </p>
                </div>
            </div>
        </section>

        <BenefitsSection />
        <FAQSection :faqs="faqs" />
    </main>

    <!-- Modal Lightbox Preview Foto TKKSD di Home -->
    <div 
        v-if="activeModalFoto" 
        class="fixed inset-0 z-[120] flex items-center justify-center bg-black/85 backdrop-blur-md p-4 animate-fade-in"
        @click.self="closeModalFoto"
    >
        <div class="relative max-w-4xl w-full bg-slate-900 border border-slate-700/80 rounded-3xl overflow-hidden shadow-2xl">
            <!-- Close Button -->
            <button 
                @click="closeModalFoto" 
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
