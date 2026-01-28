<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { onMounted, ref, computed } from 'vue'
import Navbar from '@/Components/Landing/Navbar.vue'
import BenefitsSection from '@/Components/Landing/BenefitsSection.vue'
import FAQSection from '@/Components/Landing/FAQSection.vue'
import Footer from '@/Components/Landing/Footer.vue'

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
const currentSlider = computed(() => {
    if (props.sliders.length > 0) {
        return props.sliders[currentSliderIndex.value]
    }
    return null
})

onMounted(() => {
    AOS.init({ duration: 600, once: true, offset: 50 })
    window.addEventListener('scroll', () => scrolled.value = window.scrollY > 50)

    if (props.sliders.length > 1) {
        setInterval(() => {
            currentSliderIndex.value = (currentSliderIndex.value + 1) % props.sliders.length
        }, 5000)
    }
    
    // ... stats observer logic ...
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !statsAnimated.value) {
                statsAnimated.value = true
                animateCounter(safeStats.value.documents, 'documents', 2000)
                animateCounter(safeStats.value.opds, 'opds', 2000)
                animateCounter(safeStats.value.improvement, 'improvement', 2000)
            }
        })
    }, { threshold: 0.3 })

    setTimeout(() => {
        const statsElement = document.getElementById('stats-hero')
        if (statsElement) observer.observe(statsElement)
    }, 100)
})

const steps = [
    { label: "Permohonan", by: "Pemohon", desc: "Pengajuan usulan kerjasama melalui sistem dengan melengkapi dokumen.", icon: "solar:upload-bold" },
    { label: "Verifikasi", by: "Bagian Kerjasama", desc: "Pemeriksaan kelengkapan dokumen administrasi usulan kerjasama.", icon: "solar:checklist-minimalistic-bold" },
    { label: "Pembahasan", by: "TKKSD", desc: "Pembahasan teknis, penyusunan draft, dan reviu aspek legalitas.", icon: "solar:users-group-rounded-bold" },
    { label: "Penjadwalan", by: "Bagian Kerjasama", desc: "Penjadwalan waktu dan tempat untuk penandatanganan kesepakatan.", icon: "solar:calendar-date-bold" },
    { label: "Persetujuan", by: "Walikota", desc: "Persetujuan akhir dan penandatanganan naskah kerjasama.", icon: "solar:pen-bold" },
    { label: "Pelaksanaan & Monev", by: "OPD Pengampu", desc: "Pelaksanaan butir kerjasama dan monitoring evaluasi berkala.", icon: "solar:graph-up-bold" }
]
// ... features, legalProducts ...

</script>

<template>
    <Head title="Sikerja - Sistem Informasi Kerjasama Daerah" />

    <Navbar />

    <main class="font-['Inter'] antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        <section id="hero" class="relative min-h-screen flex items-center overflow-hidden pt-20">
            <!-- Dynamic Background from Slider or Default -->
            <!-- Dynamic Background from Slider or Default -->
            <div v-if="currentSlider" class="absolute inset-0 transition-opacity duration-1000 ease-in-out">
                 <!-- Assume file path is stored relative to public/storage or similar. Adjust src accordingly. -->
                 <!-- If file is full URL or needs storage wrapper -->
                <img :src="'/storage/' + currentSlider.file.replace(/^storage\//, '')" class="w-full h-full object-cover" alt="Hero Background">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/90 via-emerald-900/70 to-transparent"></div>
            </div>
            
            <div v-else class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-green-900 to-teal-900">
                <div class="absolute inset-0 opacity-20 mix-blend-overlay">
                    <svg class="w-full h-full" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice">
                         <defs>
                            <pattern id="city" x="0" y="0" width="200" height="200" patternUnits="userSpaceOnUse">
                                <path d="M40 160 L60 140 L60 180 Z M80 160 L100 140 L100 180 Z M120 160 L140 140 L140 180 Z" fill="none" stroke="white" stroke-width="2" opacity="0.35" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#city)" />
                    </svg>
                </div>
            </div>

            <!-- Accents -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/30 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-green-500/30 rounded-full blur-3xl animate-pulse" style="animation-delay:1s;"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 py-24 grid lg:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-up">
                    <span class="inline-flex items-center gap-2 px-5 py-2.5 mb-8 text-sm font-bold tracking-wider text-emerald-50 bg-white/10 backdrop-blur-md rounded-full border border-white/20">
                        <Icon icon="solar:star-bold" class="w-5 h-5 text-yellow-400" />
                        {{ currentSlider ? 'INFORMASI TERBARU' : 'INOVASI PEMKOT SAMARINDA' }}
                    </span>
                    <h1 class="font-['Outfit'] text-5xl lg:text-7xl font-black mb-6 leading-tight text-white drop-shadow-2xl">
                         <template v-if="currentSlider">
                            Sikerja Samarinda
                         </template>
                         <template v-else>
                            Si<span class="bg-gradient-to-r from-green-300 to-emerald-200 bg-clip-text text-transparent">Kerja</span>
                         </template>
                    </h1>
                    <p class="text-xl lg:text-2xl text-emerald-50 mb-10 max-w-2xl font-medium leading-relaxed drop-shadow-md">
                        <template v-if="currentSlider">
                             <!-- Optional: Slider description if exists, otherwise static fallback or just title -->
                            {{ currentSlider.label }}
                        </template>
                        <template v-else>
                            Sistem Informasi <span class="font-semibold text-white">Kerjasama Daerah</span> Kota Samarinda.
                            <br class="hidden lg:block"/>
                            <span class="text-lg opacity-80 mt-2 block font-normal">Kelola seluruh siklus kerjasama daerah secara terintegrasi, transparan, dan akuntabel.</span>
                        </template>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <Link href="/login" class="group relative inline-flex items-center justify-center px-9 py-4 text-lg font-semibold rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white shadow-xl shadow-emerald-500/20 hover:shadow-emerald-400/40 hover:scale-[1.02] transition-all duration-300 ease-out border border-emerald-400/50">
                            Masuk Aplikasi
                            <Icon icon="solar:login-3-bold" class="ml-2 w-5 h-5 transition-transform group-hover:translate-x-1" />
                        </Link>
                        <Link href="/tentang" class="relative inline-flex items-center justify-center px-9 py-4 text-lg font-semibold rounded-xl bg-white/5 border border-white/10 backdrop-blur-md text-white hover:bg-white/10 hover:border-white/20 transition-all duration-300 ease-out">
                            Pelajari Lebih Lanjut
                        </Link>
                    </div>
                    <div id="stats-hero" class="grid grid-cols-2 sm:grid-cols-4 gap-6" v-if="safeStats && animatedStats">
                        <div class="text-center group" data-aos="fade-up">
                            <div class="w-16 h-16 mx-auto mb-4 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 group-hover:border-emerald-500/50 transition-colors">
                                <Icon icon="solar:document-bold" class="w-7 h-7 text-emerald-300" />
                            </div>
                            <div class="text-4xl font-['Outfit'] font-black text-white drop-shadow-lg mb-1">{{ animatedStats.documents }}+</div>
                            <div class="text-sm font-medium text-emerald-100 uppercase tracking-wide">Kerjasama</div>
                        </div>
                        <div class="text-center group" data-aos="fade-up" data-aos-delay="100">
                             <div class="w-16 h-16 mx-auto mb-4 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 group-hover:border-emerald-500/50 transition-colors">
                                <Icon icon="solar:buildings-bold" class="w-7 h-7 text-emerald-300" />
                            </div>
                            <div class="text-4xl font-['Outfit'] font-black text-white drop-shadow-lg mb-1">{{ animatedStats.opds }}+</div>
                            <div class="text-sm font-medium text-emerald-100 uppercase tracking-wide">Mitra</div>
                        </div>
                        <div class="text-center group" data-aos="fade-up" data-aos-delay="200">
                             <div class="w-16 h-16 mx-auto mb-4 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 group-hover:border-emerald-500/50 transition-colors">
                                <Icon icon="solar:graph-up-bold" class="w-7 h-7 text-emerald-300" />
                            </div>
                            <div class="text-4xl font-['Outfit'] font-black text-white drop-shadow-lg mb-1">{{ animatedStats.improvement }}%</div>
                            <div class="text-sm font-medium text-emerald-100 uppercase tracking-wide">Efisiensi</div>
                        </div>
                        <div class="text-center group" data-aos="fade-up" data-aos-delay="300">
                             <div class="w-16 h-16 mx-auto mb-4 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 group-hover:border-emerald-500/50 transition-colors">
                                <Icon icon="solar:clock-circle-bold" class="w-7 h-7 text-emerald-300" />
                            </div>
                            <div class="text-4xl font-['Outfit'] font-black text-white drop-shadow-lg mb-1">{{ safeStats?.uptime || '24/7' }}</div>
                            <div class="text-sm font-medium text-emerald-100 uppercase tracking-wide">Layanan</div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block" data-aos="fade-left" data-aos-delay="300">
                    <div class="relative min-h-[500px] flex items-center justify-center">
                        <!-- Decorative shapes -->
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-br from-emerald-500/10 to-green-500/10 rounded-full blur-3xl border border-white/5 animate-[spin_10s_linear_infinite]"></div>
                        
                        <div class="relative bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 w-full max-w-md transform rotate-[-2deg] hover:rotate-0 transition-transform duration-500">
                            <div class="flex items-center gap-4 mb-6 border-b border-white/10 pb-6">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <Icon icon="solar:handshake-bold" class="w-8 h-8 text-white" />
                                </div>
                                <div>
                                    <div class="text-xs text-emerald-200 uppercase tracking-wider font-bold">Status Terbaru</div>
                                    <div class="font-bold text-lg text-white">MoU PT. Teknologi Nusantara</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                                    <Icon icon="solar:check-circle-bold" class="w-6 h-6 text-emerald-400" />
                                    <span class="text-sm font-semibold text-white">Pengajuan – Selesai</span>
                                </div>
                                <div class="flex items-center gap-3 p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                                    <Icon icon="solar:check-circle-bold" class="w-6 h-6 text-emerald-400" />
                                    <span class="text-sm font-semibold text-white">Pembahasan – Selesai</span>
                                </div>
                                <div class="flex items-center gap-3 p-4 bg-yellow-500/10 rounded-xl border border-yellow-500/20">
                                    <Icon icon="solar:refresh-circle-bold" class="w-6 h-6 text-yellow-400 animate-spin" />
                                    <span class="text-sm font-semibold text-white">Penjadwalan TTD – Proses</span>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Badges -->
                         <div class="absolute top-0 right-0 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 shadow-xl animate-bounce" style="animation-duration: 3s;">
                            <Icon icon="solar:shield-check-bold" class="w-8 h-8 text-emerald-300" />
                        </div>
                        <div class="absolute bottom-10 -left-10 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 shadow-xl animate-bounce" style="animation-duration: 4s;">
                            <Icon icon="solar:rocket-2-bold" class="w-8 h-8 text-yellow-300" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Video Gallery Section -->
        <section id="video-gallery" class="py-24 bg-gray-900 text-white relative overflow-hidden">
            <!-- Dynamic Background -->
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-emerald-500/10 rounded-full blur-[100px] animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-[800px] h-[800px] bg-blue-500/10 rounded-full blur-[100px] animate-pulse" style="animation-delay:2s;"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="text-center mb-16" data-aos="fade-up">
                    <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-emerald-400 font-bold tracking-wider uppercase text-xs mb-4 shadow-lg">
                        Media Center
                    </span>
                    <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black mb-6 leading-tight">
                        Galeri <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">Video</span>
                    </h2>
                    <p class="text-gray-400 text-lg max-w-2xl mx-auto leading-relaxed">
                        Dokumentasi visual mengenai panduan penggunaan sistem dan pesona Kota Samarinda.
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Video 1: Manual Kerjasama -->
                    <div class="group relative" data-aos="fade-right" data-aos-delay="100">
                        <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-[2rem] blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                        <div class="relative bg-gray-800 rounded-[2rem] border border-gray-700 overflow-hidden shadow-2xl transition-transform duration-500 group-hover:-translate-y-2">
                            <div class="relative aspect-video overflow-hidden">
                                <iframe 
                                    class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105"
                                    src="https://www.youtube.com/embed/RkE7RkPr7Og?si=hE6BcnUXVZkJyJkF" 
                                    title="Manual Kerjasama" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    referrerpolicy="strict-origin-when-cross-origin" 
                                    allowfullscreen>
                                </iframe>
                                <!-- Overlay Label -->
                                <div class="absolute top-4 left-4">
                                     <span class="px-3 py-1 bg-black/60 backdrop-blur-md text-white/90 text-xs font-bold rounded-lg border border-white/10 flex items-center gap-2">
                                        <Icon icon="solar:book-bookmark-bold" class="text-emerald-400" />
                                        Panduan
                                     </span>
                                </div>
                            </div>
                            <div class="p-8">
                                <h3 class="text-2xl font-bold font-['Outfit'] text-white mb-2 group-hover:text-emerald-400 transition-colors">
                                    Manual Kerjasama
                                </h3>
                                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                                    Pelajari langkah-langkah pengajuan dan pengelolaan kerjasama daerah. Panduan lengkap untuk memaksimalkan penggunaan Sikerja.
                                </p>
                                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 group-hover:text-emerald-400 transition-colors">
                                    <Icon icon="solar:play-circle-bold" class="w-5 h-5" />
                                    <span>Tonton Sekarang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video 2: Jelajah Samarinda -->
                    <div class="group relative" data-aos="fade-left" data-aos-delay="200">
                        <div class="absolute -inset-1 bg-gradient-to-r from-orange-500 to-amber-500 rounded-[2rem] blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                        <div class="relative bg-gray-800 rounded-[2rem] border border-gray-700 overflow-hidden shadow-2xl transition-transform duration-500 group-hover:-translate-y-2">
                            <div class="relative aspect-video overflow-hidden">
                                <iframe 
                                    class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105"
                                    src="https://www.youtube.com/embed/aQIZWUcM2jo?si=PrktvIb-vtV8Nr8k" 
                                    title="Jelajah Samarinda" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    referrerpolicy="strict-origin-when-cross-origin" 
                                    allowfullscreen>
                                </iframe>
                                 <!-- Overlay Label -->
                                <div class="absolute top-4 left-4">
                                     <span class="px-3 py-1 bg-black/60 backdrop-blur-md text-white/90 text-xs font-bold rounded-lg border border-white/10 flex items-center gap-2">
                                        <Icon icon="solar:map-point-bold" class="text-orange-400" />
                                        Wisata
                                     </span>
                                </div>
                            </div>
                            <div class="p-8">
                                <h3 class="text-2xl font-bold font-['Outfit'] text-white mb-2 group-hover:text-orange-400 transition-colors">
                                    Jelajah Kota Samarinda
                                </h3>
                                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                                    Pusat Peradaban, Jantung Perdagangan. Temukan pesona keindahan Sungai Mahakam, keragaman budaya, dan dinamika pembangunan Kota Tepian.
                                </p>
                                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 group-hover:text-orange-400 transition-colors">
                                    <Icon icon="solar:play-circle-bold" class="w-5 h-5" />
                                    <span>Tonton Sekarang</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full max-w-5xl pointer-events-none opacity-30">
                     <svg viewBox="0 0 100 100" fill="none" class="w-full h-full animate-[spin_20s_linear_infinite]">
                        <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="0.5" stroke-dasharray="4 4" class="text-gray-600" />
                        <circle cx="50" cy="50" r="30" stroke="currentColor" stroke-width="0.5" class="text-gray-700" />
                    </svg>
                </div>
            </div>
        </section>

        <section id="tentang" class="py-24 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div data-aos="fade-right">
                        <span class="inline-block py-1 px-3 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold tracking-wider uppercase text-xs mb-4">
                            Tentang Aplikasi
                        </span>
                        <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black mb-6 text-gray-900 dark:text-white leading-tight">
                            Apa itu <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">SIKERJA?</span>
                        </h2>
                        
                        <div class="prose prose-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            <p class="mb-4 text-justify">
                                <span class="font-bold text-gray-900 dark:text-white">Sistem Informasi Kerjasama Daerah (SIKERJA)</span> adalah platform digital yang dibangun oleh Pemerintah Kota Samarinda untuk mendukung penyelenggaraan Sistem Pemerintahan Berbasis Elektronik (SPBE).
                            </p>
                            <p class="mb-4 text-justify">
                                Sistem ini dirancang untuk mendata, memonitor, dan mengevaluasi seluruh kerjasama daerah secara terintegrasi. Dikelola oleh <span class="font-semibold text-emerald-600">Bagian Kerja Sama Sekretariat Daerah Kota Samarinda</span>, SIKERJA memfasilitasi efisiensi layanan melalui digitalisasi proses untuk seluruh Perangkat Daerah dan Mitra.
                            </p>
                            <p class="text-justify bg-emerald-50 dark:bg-gray-800 p-4 rounded-xl border border-emerald-100 dark:border-gray-700 italic text-base">
                                "Wujud nyata keterbukaan informasi publik yang akuntabel dan transparan dalam mendukung tercapainya Good Governance di Kota Samarinda."
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-4">
                             <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                <Icon icon="solar:monitor-smartphone-bold" class="text-emerald-500 w-5 h-5" />
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Digitalisasi</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                <Icon icon="solar:graph-up-bold" class="text-blue-500 w-5 h-5" />
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Monitoring</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                <Icon icon="solar:shield-check-bold" class="text-orange-500 w-5 h-5" />
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Evaluasi</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6" data-aos="fade-left">
                        <div class="space-y-6 mt-8">
                            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 transition-colors">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-4 text-emerald-600">
                                    <Icon icon="solar:cloud-upload-bold" class="w-6 h-6" />
                                </div>
                                <h3 class="font-bold text-lg mb-2 dark:text-white">Digital</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Paperless office, hemat biaya dan ramah lingkungan.</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 transition-colors">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-4 text-emerald-600">
                                    <Icon icon="solar:shield-check-bold" class="w-6 h-6" />
                                </div>
                                <h3 class="font-bold text-lg mb-2 dark:text-white">Aman</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Keamanan data terjamin dengan enkripsi standar industri.</p>
                            </div>
                        </div>
                         <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 transition-colors">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-4 text-emerald-600">
                                    <Icon icon="solar:rocket-2-bold" class="w-6 h-6" />
                                </div>
                                <h3 class="font-bold text-lg mb-2 dark:text-white">Cepat</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Memangkas birokrasi, mempercepat proses persetujuan.</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 transition-colors">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-4 text-emerald-600">
                                    <Icon icon="solar:archive-check-bold" class="w-6 h-6" />
                                </div>
                                <h3 class="font-bold text-lg mb-2 dark:text-white">Terdata</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Database lengkap untuk pengambilan keputusan yang lebih baik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="alur" class="py-24 bg-gray-50 dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16" data-aos="fade-up">
                    <span class="text-emerald-600 font-bold tracking-wider uppercase text-sm">Workflow</span>
                    <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black mt-2 mb-4 text-gray-900 dark:text-white">
                        Alur Kerjasama
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl mx-auto">
                        Proses yang sistematis untuk memastikan setiap kerjasama berjalan sesuai regulasi.
                    </p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8" data-aos="fade-up" data-aos-delay="100">
                    <div v-for="(s, i) in steps" :key="i" class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center font-bold text-xl">
                                {{ i + 1 }}
                            </div>
                             <Icon :icon="s.icon" class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-2">{{ s.label }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ s.desc }}</p>
                    </div>
                </div>
            </div>
        </section>



        <BenefitsSection />
        <FAQSection :faqs="faqs" />
    </main>

    <Footer />
</template>
