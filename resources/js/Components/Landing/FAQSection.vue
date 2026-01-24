<script setup>
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    faqs: {
        type: Array,
        default: () => []
    }
})

const activeFaq = ref(null)
const toggle = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index
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
    <section id="faq" class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-10" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-black mb-3 bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                    Pertanyaan Umum
                </h2>
                <p class="text-base text-gray-600 dark:text-gray-400">Temukan jawaban atas pertanyaan yang sering diajukan</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" data-aos="fade-up" data-aos-delay="200">
                <div v-for="(faq, index) in displayFaqs" :key="index" class="h-fit border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transition-all hover:border-emerald-500">
                    <button @click="toggle(index)" class="w-full px-6 py-5 flex items-center justify-between text-left bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="text-base font-bold text-gray-900 dark:text-white pr-4">{{ faq.label }}</span>
                        <Icon :icon="activeFaq === index ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold'" class="w-6 h-6 text-emerald-600 flex-shrink-0 transition-transform" />
                    </button>

                    <div v-if="activeFaq === index" class="px-6 py-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm dark:prose-invert prose-emerald max-w-none" v-html="faq.jawaban"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
