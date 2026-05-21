<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import { Icon } from '@iconify/vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { onMounted } from 'vue'

onMounted(() => {
    AOS.init({ duration: 600, once: true, offset: 50 })
})

const steps = [
    { label: "Permohonan", by: "Pemohon", desc: "Pengajuan usulan kerjasama melalui sistem dengan melengkapi dokumen.", icon: "solar:upload-bold" },
    { label: "Verifikasi", by: "Bagian Kerjasama", desc: "Pemeriksaan kelengkapan dokumen administrasi usulan kerjasama.", icon: "solar:checklist-minimalistic-bold" },
    { label: "Pembahasan", by: "TKKSD", desc: "Pembahasan teknis, penyusunan draft, dan reviu aspek legalitas.", icon: "solar:users-group-rounded-bold" },
    { label: "Penjadwalan", by: "Bagian Kerjasama", desc: "Penjadwalan waktu dan tempat untuk penandatanganan kesepakatan.", icon: "solar:calendar-date-bold" },
    { label: "Persetujuan", by: "Walikota", desc: "Persetujuan akhir dan penandatanganan naskah kerjasama.", icon: "solar:pen-bold" },
    { label: "Pelaksanaan & Monev", by: "OPD Pengampu", desc: "Pelaksanaan butir kerjasama dan monitoring evaluasi berkala.", icon: "solar:graph-up-bold" }
]
</script>

<template>
    <Head title="Alur Kerjasama Sikerja" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-[#f4f6f8] text-gray-900 overflow-x-hidden">
        <PageHeader 
            title="Alur Kerjasama" 
            subtitle="Tahapan proses pengajuan hingga pelaksanaan kerjasama daerah"
            :breadcrumbs="[{ label: 'Alur Proses', url: '/alur' }]"
        />

        <section class="py-20 bg-[#eef3f7]">
            <div class="max-w-7xl mx-auto px-6">
                <div class="relative">
                    <!-- Central Line -->
                    <div class="absolute left-8 md:left-1/2 top-0 bottom-0 w-0.5 bg-slate-300 transform md:-translate-x-1/2"></div>

                    <div v-for="(step, index) in steps" :key="index" class="relative mb-16 last:mb-0 group">
                        <div class="flex md:items-center flex-col md:flex-row gap-8">
                            
                            <!-- Left Side (Desktop Even) -->
                            <div class="flex-1 md:text-right order-2 md:order-1 pl-20 md:pl-0 hidden md:block md:pr-8">
                                <div v-if="index % 2 === 0" data-aos="fade-right">
                                    <div class="inline-block px-4 py-1.5 mb-3 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-wider">
                                        {{ step.by }}
                                    </div>
                                    <h3 class="font-['Outfit'] font-bold text-2xl text-gray-900 mb-3">{{ step.label }}</h3>
                                    <p class="text-gray-600 leading-relaxed">{{ step.desc }}</p>
                                </div>
                            </div>

                            <!-- Marker -->
                            <div class="absolute left-8 md:left-1/2 w-12 h-12 -ml-6 bg-white border-4 border-amber-400 rounded-full z-10 flex items-center justify-center shadow-lg transform transition-transform group-hover:scale-110 order-1 md:order-2">
                                <span class="font-bold text-amber-600 text-sm">{{ index + 1 }}</span>
                            </div>

                            <!-- Right Side (Desktop Odd + Mobile All) -->
                            <div class="flex-1 order-3 pl-20" :class="index % 2 !== 0 ? 'md:pl-8' : 'md:pl-0'">
                                <!-- Desktop Odd Content -->
                                <div v-if="index % 2 !== 0" class="hidden md:block" data-aos="fade-left">
                                    <div class="inline-block px-4 py-1.5 mb-3 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-wider">
                                        {{ step.by }}
                                    </div>
                                    <h3 class="font-['Outfit'] font-bold text-2xl text-gray-900 mb-3">{{ step.label }}</h3>
                                    <p class="text-gray-600 leading-relaxed">{{ step.desc }}</p>
                                </div>

                                <!-- Mobile Content (Shows for ALL items on mobile) -->
                                <div class="md:hidden" data-aos="fade-left">
                                     <div class="inline-block px-4 py-1.5 mb-3 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-wider">
                                        {{ step.by }}
                                    </div>
                                    <h3 class="font-['Outfit'] font-bold text-2xl text-gray-900 mb-3">{{ step.label }}</h3>
                                    <p class="text-gray-600 leading-relaxed">{{ step.desc }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <Footer />
    <ChatWidget />
</template>
