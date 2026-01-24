<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import FAQSection from '@/Components/Landing/FAQSection.vue' 
import { onMounted } from 'vue'
import AOS from 'aos'
import 'aos/dist/aos.css'

const props = defineProps({
    faqs: Array
})

onMounted(() => {
    AOS.init({ duration: 600, once: true, offset: 50 })
})
</script>

<template>
    <Head title="FAQ Sikerja" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        <PageHeader 
            title="Pertanyaan Umum" 
            subtitle="Jawaban atas pertanyaan yang sering diajukan seputar Sikerja"
            :breadcrumbs="[{ label: 'FAQ', url: '/faq' }]"
        />

        <FAQSection :faqs="faqs" /> 
    </main>

    <Footer />
</template>
