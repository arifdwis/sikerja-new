<script setup>
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'
import DOMPurify from 'dompurify'

const props = defineProps({
    faqs: {
        type: Array,
        default: () => []
    }
})

const activeFaq = ref(0)
const toggle = (index) => {
    activeFaq.value = index
}

// Fallback static data if no dynamic data provided
const staticFaqs = [
    { label: "Bagaimana cara mengajukan kerjasama?", jawaban: "Silahkan login sebagai pemohon dan klik menu 'Permohonan Baru'." },
    { label: "Apa syarat pengajuan kerjasama?", jawaban: "Syarat lengkap dapat dilihat pada dokumen persyaratan di setiap jenis kerjasama." },
    { label: "Berapa lama proses verifikasi?", jawaban: "Verifikasi administrasi memakan waktu maksimal 3 hari kerja." }
]

const displayFaqs = computed(() => {
    return props.faqs.length > 0 ? props.faqs : staticFaqs
})

const safeJawaban = computed(() => {
    const jawaban = displayFaqs.value[activeFaq.value]?.jawaban
    return jawaban ? DOMPurify.sanitize(jawaban) : ''
})
</script>

<template>
    <section id="faq" class="py-20 bg-white dark:bg-slate-950 font-['Inter'] transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-10 reveal">
                <span class="inline-block py-1 px-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 font-bold tracking-wider uppercase text-xs mb-4">
                    Pertanyaan Umum
                </span>
                <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight mb-3 text-slate-900 dark:text-white">
                    Pertanyaan Umum
                </h2>
                <p class="text-base text-slate-600 dark:text-slate-400">Pilih pertanyaan di sisi kiri, lalu baca jawaban detailnya tanpa membuka-tutup banyak panel.</p>
            </div>

            <div class="grid lg:grid-cols-5 gap-8 reveal reveal-d1 items-start">
                <div class="lg:col-span-2 bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800 rounded-3xl p-4">
                    <div class="space-y-2">
                        <button
                            v-for="(faq, index) in displayFaqs"
                            :key="index"
                            @click="toggle(index)"
                            :class="[
                                'w-full text-left px-4 py-4 rounded-2xl border transition-all duration-250 flex items-center justify-between gap-3 group',
                                activeFaq === index
                                    ? 'bg-slate-950 dark:bg-amber-400 border-slate-950 dark:border-amber-400 text-white dark:text-slate-950 shadow-xl shadow-slate-900/10 dark:shadow-amber-400/10 scale-[1.01]'
                                    : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 hover:border-amber-400 dark:hover:border-amber-400/50 hover:bg-amber-500/5'
                            ]"
                        >
                            <span class="text-sm font-bold leading-snug">{{ faq.label }}</span>
                            <Icon :icon="activeFaq === index ? 'solar:alt-arrow-right-bold-duotone' : 'solar:alt-arrow-down-bold-duotone'" class="w-5 h-5 shrink-0 transition-transform group-hover:scale-105" />
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-3 rounded-3xl border border-slate-200/60 dark:border-slate-800 bg-white dark:bg-slate-900/40 shadow-xl shadow-slate-900/5 dark:shadow-slate-950/20 p-8 lg:p-10 transition-all duration-300 hover:border-amber-450/30 sticky top-28 self-start overflow-hidden relative">
                    <!-- Premium top accent line -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-400 to-amber-500 z-10"></div>

                    <!-- Subtle quotation double watermark -->
                    <Icon icon="solar:double-quotes-l-bold" class="absolute -right-6 -bottom-10 w-44 h-44 text-slate-100/60 dark:text-slate-800/20 pointer-events-none z-0" />

                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wider mb-2">
                            Jawaban Terpilih
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-4 leading-snug font-['Outfit']">
                            {{ displayFaqs[activeFaq]?.label }}
                        </h3>
                        <div class="text-sm text-slate-650 dark:text-slate-350 leading-relaxed prose prose-sm max-w-none prose-headings:text-slate-900 dark:prose-headings:text-white prose-a:text-sky-600 dark:prose-a:text-sky-400" v-html="safeJawaban"></div>
                        
                        <!-- Premium Interactive Help Center feedback widget -->
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500 relative z-10">
                            <span class="font-medium">Apakah jawaban ini membantu?</span>
                            <div class="flex items-center gap-1.5">
                                <button class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/10 border border-slate-150 dark:border-slate-850 hover:border-emerald-500 dark:hover:border-emerald-500/30 text-slate-450 hover:text-emerald-500 dark:hover:text-emerald-400 transition-all shadow-sm" title="Membantu">
                                    <Icon icon="solar:like-bold" class="w-4 h-4" />
                                </button>
                                <button class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-red-500/10 dark:hover:bg-red-500/10 border border-slate-150 dark:border-slate-850 hover:border-red-500 dark:hover:border-red-500/30 text-slate-450 hover:text-red-500 dark:hover:text-red-400 transition-all shadow-sm" title="Kurang Membantu">
                                    <Icon icon="solar:dislike-bold" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
