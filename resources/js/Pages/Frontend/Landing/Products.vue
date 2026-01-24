<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import FeatureCard from '@/Components/Landing/FeatureCard.vue' // Reusing reused comp if available or structure manually
import { Icon } from '@iconify/vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { onMounted } from 'vue'

onMounted(() => {
    AOS.init({ duration: 600, once: true, offset: 50 })
})

const products = [
    { name: "Kesepakatan Bersama (MoU)", desc: "Dokumen kesepakatan antara Pemerintah Daerah dengan Pihak Ketiga yang memuat rencana kerjasama.", icon: "solar:handshake-bold" },
    { name: "Perjanjian Kerjasama (PKS)", desc: "Tindak lanjut teknis dari Kesepakatan Bersama yang mengatur hak dan kewajiban para pihak secara rinci.", icon: "solar:document-text-bold" },
    { name: "Kerjasama Daerah Dengan Daerah Lain (KSDD)", desc: "Kerjasama antar Pemerintah Daerah untuk penyelenggaraan urusan pemerintahan.", icon: "solar:city-bold" },
    { name: "Kerjasama Daerah Dengan Pihak Ketiga (KSDPK)", desc: "Kerjasama dengan badan hukum, organisasi, atau lembaga di dalam negeri.", icon: "solar:buildings-bold" },
    { name: "Kerjasama Pemerintah Dengan Badan Usaha (KPBU)", desc: "Kerjasama penyediaan infrastruktur untuk kepentingan umum.", icon: "solar:construction-bold" },
    { name: "Kerjasama Daerah Dengan Lembaga Luar Negeri", desc: "Kerjasama strategis dengan mitra internasional.", icon: "solar:globe-bold" }
]
</script>

<template>
    <Head title="Bentuk Kerjasama Sikerja" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        <PageHeader 
            title="Bentuk Kerjasama" 
            subtitle="Jenis-jenis dokumen dan bentuk kerjasama yang difasilitasi"
            :breadcrumbs="[{ label: 'Bentuk Kerjasama', url: '/jenis-kerjasama' }]"
        />

        <section class="py-24">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(item, i) in products" :key="i" class="group bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-2 transition-all duration-300" data-aos="fade-up" :data-aos-delay="i*50">
                        <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-500 transition-colors duration-300">
                            <Icon :icon="item.icon" class="w-8 h-8 text-emerald-600 group-hover:text-white transition-colors" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ item.name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <Footer />
</template>
