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

const priorityBenefits = benefits.slice(0, 3)
const supportBenefits = benefits.slice(3)
</script>

<template>
    <section class="py-20 bg-slate-50 dark:bg-slate-950/40 font-['Inter'] transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12 reveal">
                <span class="inline-block py-1 px-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 font-bold tracking-wider uppercase text-xs mb-4">
                    Keunggulan Sistem
                </span>
                <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight mb-3 text-slate-900 dark:text-white">
                    Keunggulan Sistem
                </h2>
                <p class="text-base text-slate-600 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
                    Bukan sekadar digitalisasi formulir. Sikerja dirancang untuk mempercepat kolaborasi lintas unit dan memastikan hasil kerja sama bisa dilacak secara tuntas.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 reveal reveal-d1">
                <div class="lg:col-span-2 bg-white dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800 rounded-3xl p-8 lg:p-10 shadow-sm transition-all duration-300 hover:border-amber-400/20">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 mb-5">
                        Nilai Utama Sikerja
                    </span>
                    <h3 class="font-['Outfit'] text-3xl font-black text-slate-900 dark:text-white mb-4 leading-tight">
                        Platform koordinasi kerja sama daerah yang fokus pada kejelasan proses dan dampak.
                    </h3>
                    <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
                        Setiap tahapan punya pemilik proses yang jelas, jejak audit yang utuh, dan notifikasi otomatis agar tindak lanjut antar perangkat daerah tidak terputus. Dengan begitu, keputusan dapat diambil lebih cepat dan berbasis data yang sama.
                    </p>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div v-for="(benefit, i) in supportBenefits" :key="`support-${i}`" class="rounded-2xl border border-slate-200/50 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 p-4 transition-all duration-300 hover:scale-[1.02] hover:border-amber-400/20 group">
                            <div class="w-11 h-11 bg-slate-950 dark:bg-slate-800 rounded-xl flex items-center justify-center mb-3 border border-white/5 transition-colors group-hover:bg-amber-500/20">
                                <Icon :icon="benefit.icon" class="w-5 h-5 text-amber-300 transition-transform group-hover:scale-110" />
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1">{{ benefit.title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ benefit.desc }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-3xl p-6 bg-slate-950 dark:bg-slate-900/40 border border-slate-900 dark:border-slate-800 shadow-xl">
                        <h3 class="text-xl font-black text-white mb-2">Fitur Prioritas</h3>
                        <p class="text-sm text-slate-400 mb-5 leading-relaxed">Tiga kapabilitas inti yang paling berdampak untuk operasi harian.</p>
                        <div class="space-y-3">
                            <div v-for="(benefit, i) in priorityBenefits" :key="`priority-${i}`" class="rounded-2xl border border-slate-800 dark:border-slate-800/80 bg-white/5 p-4 transition-all duration-300 hover:bg-white/10 group">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center shrink-0 shadow-lg shadow-amber-400/10">
                                        <Icon :icon="benefit.icon" class="w-5 h-5 transition-transform group-hover:scale-105" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-extrabold text-white mb-1">{{ benefit.title }}</h4>
                                        <p class="text-xs text-slate-400 leading-relaxed">{{ benefit.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
