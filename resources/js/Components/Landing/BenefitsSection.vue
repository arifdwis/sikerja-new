<script setup>
import { Icon } from '@iconify/vue'

const benefits = [
    { icon: "solar:graph-up-bold", title: "Monitoring Real-Time", desc: "Pantau perkembangan dokumen kapan pun dan di mana pun dengan dashboard yang informatif." },
    { icon: "solar:bell-bold", title: "Notifikasi Otomatis", desc: "Dapatkan pembaruan status langsung melalui dashboard dan email untuk setiap perubahan." },
    { icon: "solar:shield-check-bold", title: "Audit Trail Lengkap", desc: "Setiap perubahan terekam secara permanen untuk memastikan integritas data dan akuntabilitas." },
    { icon: "solar:documents-bold", title: "Arsip Digital Aman", desc: "Semua dokumen tersimpan rapi dan terlindungi di penyimpanan cloud yang aman." },
    { icon: "solar:clock-circle-bold", title: "Akses 24/7", desc: "Akses sistem kapan saja tanpa batasan waktu untuk produktivitas maksimal." },
    { icon: "solar:user-check-bold", title: "Multi-User Collaboration", desc: "Kolaborasi tim yang efektif dengan sistem role dan permission yang terstruktur." }
]
</script>

<template>
    <section class="py-16 bg-gradient-to-br from-green-50 via-emerald-50 to-white dark:from-gray-800 dark:via-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-10" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-black mb-3 bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                    Keunggulan Sistem
                </h2>
                <p class="text-base text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Berbagai fitur unggulan yang mendukung efisiensi dan transparansi proses pengajuan produk hukum
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5" data-aos="fade-up" data-aos-delay="200">
                <div v-for="(benefit, i) in benefits" :key="i" class="group bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 p-6 rounded-2xl border border-emerald-100 dark:border-emerald-800 hover:shadow-xl hover:border-emerald-500 hover:-translate-y-2 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform">
                        <Icon :icon="benefit.icon" class="w-7 h-7 text-white" />
                    </div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white mb-2">{{ benefit.title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ benefit.desc }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
