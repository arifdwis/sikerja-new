<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import FAQSection from '@/Components/Landing/FAQSection.vue' 
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
    faqs: Array
})

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
</script>

<template>
    <Head title="FAQ Sikerja" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-[#f4f6f8] dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden transition-colors duration-300">
        <PageHeader 
            title="Pertanyaan Umum" 
            subtitle="Jawaban atas pertanyaan yang sering diajukan seputar Sikerja"
            :breadcrumbs="[{ label: 'FAQ', url: '/faq' }]"
        />

        <FAQSection :faqs="faqs" /> 
    </main>

    <Footer />
    <ChatWidget />
</template>

<style scoped>
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

.reveal.reveal-d1 { transition-delay: 0.15s; }
.reveal.reveal-d2 { transition-delay: 0.3s; }
</style>
