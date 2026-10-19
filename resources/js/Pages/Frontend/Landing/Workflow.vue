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

const steps = [
    { 
        label: "1. Pengajuan Usulan", 
        by: "Pemohon / Perangkat Daerah", 
        desc: "Pengajuan usulan kerja sama melalui sistem SIKERJA dengan melengkapi profil instansi, pimpinan, 2 PIC, serta dokumen permohonan, proposal/KAK, draf kerja sama, dan legalitas.", 
        icon: "solar:document-add-bold-duotone" 
    },
    { 
        label: "2. Verifikasi & Telaah Administrasi", 
        by: "Bagian Kerja Sama", 
        desc: "Pemeriksaan kelengkapan berkas administrasi oleh Staf Admin serta validasi dan disposisi oleh Kepala Bagian Kerja Sama (Disetujui, Perlu Revisi, atau Ditolak).", 
        icon: "solar:checklist-minimalistic-bold-duotone" 
    },
    { 
        label: "3. Pembahasan Draf KB", 
        by: "TKKSD & Bagian Kerja Sama", 
        desc: "Forum kajian substansi dan penyelarasan draf Kesepakatan Bersama (KB) bersama Tim Koordinasi Kerja Sama Daerah (TKKSD) dan mitra hingga final.", 
        icon: "solar:users-group-rounded-bold-duotone" 
    },
    { 
        label: "4. Penjadwalan & Penandatanganan KB", 
        by: "Walikota Samarinda & Mitra", 
        desc: "Penyesuaian jadwal penandatanganan dengan agenda pimpinan/Walikota, penandatanganan Kesepakatan Bersama (MoU), dan pengunggahan naskah KB bertanda tangan sah.", 
        icon: "solar:pen-bold-duotone" 
    },
    { 
        label: "5. Pembahasan Draf PKS Turunan", 
        by: "Perangkat Daerah (OPD) & Mitra", 
        desc: "Penyusunan dan pembahasan draf teknis Perjanjian Kerja Sama (PKS) sebagai operasional pelaksanaan dari Kesepakatan Bersama yang telah aktif.", 
        icon: "solar:documents-minimalistic-bold-duotone" 
    },
    { 
        label: "6. Penandatanganan PKS", 
        by: "Kepala OPD Pengampu & Mitra", 
        desc: "Penandatanganan dokumen naskah PKS oleh Kepala Perangkat Daerah bersama mitra dan penerbitan nomor registrasi kerja sama resmi.", 
        icon: "solar:diploma-verified-bold-duotone" 
    },
    { 
        label: "7. Pelaksanaan Kerja Sama", 
        by: "OPD Pengampu & Mitra", 
        desc: "Realisasi butir-butir kerja sama di lapangan dan penyampaian laporan progres kegiatan, berita acara, serta dokumentasi secara berkala per semester.", 
        icon: "solar:running-2-bold-duotone" 
    },
    { 
        label: "8. Pengendalian, Evaluasi & Monev", 
        by: "TKKSD & Bagian Kerja Sama", 
        desc: "Pengawasan kepatuhan, pemantauan masa berlaku (H-90, H-60, H-30), evaluasi capaian manfaat kerja sama, serta rekomendasi perpanjangan atau pengakhiran.", 
        icon: "solar:clipboard-check-bold-duotone" 
    }
]
</script>

<template>
    <Head title="Alur Kerjasama Sikerja" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 overflow-x-hidden transition-colors duration-300">
        <PageHeader 
            title="Alur Kerjasama" 
            subtitle="Tahapan proses pengajuan hingga pelaksanaan kerjasama daerah secara berkesinambungan"
            :breadcrumbs="[{ label: 'Alur Proses', url: '/alur' }]"
        />

        <section class="py-20 bg-slate-100/50 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="relative">
                    <!-- Central Line -->
                    <div class="absolute left-8 md:left-1/2 top-0 bottom-0 w-0.5 bg-slate-350 dark:bg-slate-800 transform md:-translate-x-1/2"></div>

                    <div v-for="(step, index) in steps" :key="index" class="relative mb-16 last:mb-0 group">
                        <div class="flex md:items-center flex-col md:flex-row gap-8">
                            
                            <!-- Left Side (Desktop Even) -->
                            <div class="flex-1 md:text-right order-2 md:order-1 pl-20 md:pl-0 hidden md:block md:pr-12">
                                <div v-if="index % 2 === 0" class="reveal">
                                    <div class="inline-block px-4 py-1.5 mb-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wider">
                                        {{ step.by }}
                                    </div>
                                    <h3 class="font-['Outfit'] font-black text-2xl text-slate-900 dark:text-white mb-3">{{ step.label }}</h3>
                                    <p class="text-slate-650 dark:text-slate-400 leading-relaxed text-justify md:text-right">{{ step.desc }}</p>
                                </div>
                            </div>

                            <!-- Marker -->
                            <div class="absolute left-8 md:left-1/2 w-12 h-12 -ml-6 bg-slate-950 border-4 border-amber-400 rounded-full z-10 flex items-center justify-center shadow-lg transform transition-transform group-hover:scale-115 order-1 md:order-2">
                                <span class="font-black text-amber-400 text-sm">{{ index + 1 }}</span>
                            </div>

                            <!-- Right Side (Desktop Odd + Mobile All) -->
                            <div class="flex-1 order-3 pl-20" :class="index % 2 !== 0 ? 'md:pl-12' : 'md:pl-0'">
                                <!-- Desktop Odd Content -->
                                <div v-if="index % 2 !== 0" class="hidden md:block reveal">
                                    <div class="inline-block px-4 py-1.5 mb-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wider">
                                        {{ step.by }}
                                    </div>
                                    <h3 class="font-['Outfit'] font-black text-2xl text-slate-900 dark:text-white mb-3">{{ step.label }}</h3>
                                    <p class="text-slate-650 dark:text-slate-400 leading-relaxed text-justify">{{ step.desc }}</p>
                                </div>

                                <!-- Mobile Content (Shows for ALL items on mobile) -->
                                <div class="md:hidden reveal">
                                     <div class="inline-block px-4 py-1.5 mb-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wider">
                                        {{ step.by }}
                                    </div>
                                    <h3 class="font-['Outfit'] font-black text-2xl text-slate-900 dark:text-white mb-3">{{ step.label }}</h3>
                                    <p class="text-slate-650 dark:text-slate-400 leading-relaxed text-justify">{{ step.desc }}</p>
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
</style>
