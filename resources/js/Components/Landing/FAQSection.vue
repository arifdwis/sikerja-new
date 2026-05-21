<script setup>
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'

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
</script>

<template>
    <section id="faq" class="py-20 bg-white font-['Inter']">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-10" data-aos="fade-up">
                <h2 class="font-['Outfit'] text-4xl lg:text-5xl font-black tracking-tight mb-3 text-slate-900">
                    Pertanyaan Umum
                </h2>
                <p class="text-base text-slate-600">Pilih pertanyaan di sisi kiri, lalu baca jawaban detailnya tanpa membuka-tutup banyak panel.</p>
            </div>

            <div class="grid lg:grid-cols-5 gap-6" data-aos="fade-up" data-aos-delay="200">
                <div class="lg:col-span-2 bg-[#f8fafc] border border-slate-200 rounded-3xl p-4">
                    <div class="space-y-2">
                        <button
                            v-for="(faq, index) in displayFaqs"
                            :key="index"
                            @click="toggle(index)"
                            :class="[
                                'w-full text-left px-4 py-4 rounded-2xl border transition-all duration-200 flex items-center justify-between gap-3',
                                activeFaq === index
                                    ? 'bg-slate-900 border-slate-900 text-white shadow-lg'
                                    : 'bg-white border-slate-200 text-slate-700 hover:border-amber-300 hover:bg-amber-50'
                            ]"
                        >
                            <span class="text-sm font-bold leading-snug">{{ faq.label }}</span>
                            <Icon :icon="activeFaq === index ? 'solar:alt-arrow-right-bold' : 'solar:alt-arrow-down-bold'" class="w-5 h-5 shrink-0" />
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-3 rounded-3xl border border-slate-200 bg-white shadow-sm p-8 lg:p-10 min-h-[320px]">
                    <div class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-wider mb-4">
                        Jawaban Terpilih
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-4 leading-snug">
                        {{ displayFaqs[activeFaq]?.label }}
                    </h3>
                    <div class="text-sm text-slate-600 leading-relaxed prose prose-sm max-w-none prose-headings:text-slate-900 prose-a:text-sky-700" v-html="displayFaqs[activeFaq]?.jawaban"></div>
                </div>
            </div>
        </div>
    </section>
</template>
