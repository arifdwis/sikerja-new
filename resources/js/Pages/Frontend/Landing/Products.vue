<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import { Icon } from '@iconify/vue'
import { onMounted, onUnmounted } from 'vue'

let revealObserver = null
const initRevealAnimations = () => {
    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed')
                revealObserver.unobserve(entry.target)
            }
        })
    }, { threshold: 0.15 })

    document.querySelectorAll('.reveal').forEach(el => {
        revealObserver.observe(el)
    })
}

onMounted(() => {
    initRevealAnimations()
})

onUnmounted(() => {
    if (revealObserver) revealObserver.disconnect()
})

const products = [
    { name: "Kesepakatan Bersama (MoU)", desc: "Dokumen kesepakatan antara Pemerintah Daerah dengan Pihak Ketiga yang memuat rencana kerjasama.", icon: "solar:handshake-bold-duotone" },
    { name: "Perjanjian Kerjasama (PKS)", desc: "Tindak lanjut teknis dari Kesepakatan Bersama yang mengatur hak dan kewajiban para pihak secara rinci.", icon: "solar:document-text-bold-duotone" },
    { name: "Kerjasama Daerah Dengan Daerah Lain (KSDD)", desc: "Kerjasama antar Pemerintah Daerah untuk penyelenggaraan urusan pemerintahan secara terpadu.", icon: "solar:city-bold-duotone" },
    { name: "Kerjasama Daerah Dengan Pihak Ketiga (KSDPK)", desc: "Kerjasama dengan badan hukum, organisasi, atau lembaga di dalam negeri secara produktif.", icon: "solar:buildings-bold-duotone" },
    { name: "Kerjasama Pemerintah Dengan Badan Usaha (KPBU)", desc: "Kerjasama penyediaan infrastruktur untuk kepentingan publik yang berkelanjutan.", icon: "solar:construction-bold-duotone" },
    { name: "Kerjasama Daerah Dengan Lembaga Luar Negeri", desc: "Kerjasama strategis skala internasional guna percepatan kemajuan teknologi dan SDM.", icon: "solar:globe-bold-duotone" }
]
</script>

<template>
    <Head title="Bentuk Kerjasama Sikerja" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 overflow-x-hidden transition-colors duration-300">
        <PageHeader 
            title="Bentuk Kerjasama" 
            subtitle="Jenis-jenis dokumen dan bentuk kerjasama daerah yang difasilitasi oleh sistem"
            :breadcrumbs="[{ label: 'Bentuk Kerjasama', url: '/jenis-kerjasama' }]"
        />

        <section class="py-24">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(item, i) in products" :key="i" class="group bg-white dark:bg-slate-900/40 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800 hover:shadow-xl hover:border-amber-400/20 hover:-translate-y-1.5 transition-all duration-300 reveal" :class="`reveal-d${i % 3}`">
                        <div class="w-14 h-14 bg-slate-50 dark:bg-slate-850 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-400 dark:group-hover:bg-amber-400 transition-colors duration-300 border border-slate-150 dark:border-slate-850 shadow-inner shrink-0">
                            <Icon :icon="item.icon" class="w-7 h-7 text-amber-500 group-hover:text-slate-950 transition-colors duration-350" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-955 dark:text-white mb-3 font-['Outfit']">{{ item.name }}</h3>
                        <p class="text-slate-650 dark:text-slate-400 leading-relaxed text-justify text-sm">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <Footer />
    <ChatWidget />
</template>

<style>
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

.reveal.reveal-d1 { transition-delay: 0.1s; }
.reveal.reveal-d2 { transition-delay: 0.2s; }
</style>
