<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import { Icon } from '@iconify/vue'
import { onMounted, onUnmounted, computed } from 'vue'
import DOMPurify from 'dompurify'

const props = defineProps({
    page: Object
})

const safeContent = computed(() => {
    return props.page?.content ? DOMPurify.sanitize(props.page.content) : ''
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
    <Head :title="page ? page.label : 'Tentang Sikerja'" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 overflow-x-hidden transition-colors duration-300">
        <PageHeader 
            :title="page ? page.label : 'Tentang Sikerja'" 
            :subtitle="page ? '' : 'Mengenal lebih dalam platform inovasi kerjasama daerah Kota Samarinda'"
            :breadcrumbs="[{ label: 'Tentang', url: '/tentang' }]"
        />

        <section class="py-24">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Data from Database -->
                <div v-if="page" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <!-- Main Content -->
                    <div class="lg:col-span-8">
                        <div class="bg-white dark:bg-slate-900/40 rounded-3xl shadow-sm border border-slate-200/60 dark:border-slate-800/85 p-8 md:p-12 transition-all hover:border-amber-400/20 duration-300">
                            <article class="prose prose-lg prose-amber dark:prose-invert max-w-none">
                                <div v-html="safeContent" class="text-justify text-slate-650 dark:text-slate-350"></div>
                            </article>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <aside class="lg:col-span-4 space-y-8">
                        <div class="bg-slate-950/70 backdrop-blur-md rounded-3xl p-8 text-white shadow-xl relative overflow-hidden border border-slate-900">
                            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:16px_16px] opacity-25"></div>
                            <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-400/10 rounded-full blur-2xl"></div>
                            <h3 class="text-xl font-bold mb-4 relative z-10 font-['Outfit'] text-amber-350">Visi Kami</h3>
                            <p class="text-slate-300 mb-6 text-sm relative z-10 leading-relaxed text-justify">
                                Mewujudkan Samarinda sebagai kota pusat peradaban yang maju, mandiri, dan berkelanjutan melalui keselarasan sinergi kerjasama lintas sektoral.
                            </p>
                        </div>
                    </aside>
                </div>

                <!-- Static Fallback -->
                <div v-else class="grid lg:grid-cols-2 gap-16 items-center mb-24">
                    <div class="reveal">
                        <span class="inline-block py-1 px-3 rounded-full bg-amber-100 dark:bg-amber-950/45 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wider mb-4">
                            Visi & Misi
                        </span>
                        <h2 class="font-['Outfit'] text-3xl lg:text-4xl font-black mt-2 mb-6 text-slate-900 dark:text-white leading-tight">
                            Mewujudkan Kerjasama Daerah yang Berkualitas dan Berdaya Saing
                        </h2>
                        <p class="text-base text-slate-600 dark:text-slate-400 leading-relaxed mb-8 text-justify">
                            Sikerja dibangun dengan visi untuk menciptakan ekosistem kerjasama daerah yang transparan, akuntabel, dan memberikan manfaat nyata bagi percepatan pembangunan Kota Samarinda.
                        </p>
                        <div class="space-y-6">
                            <div class="flex gap-4 group">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 flex-shrink-0 flex items-center justify-center transition-colors group-hover:bg-amber-400/20">
                                    <Icon icon="solar:target-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1 text-slate-950 dark:text-white font-['Outfit']">Strategis</h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Fokus pada kerjasama yang memberikan dampak signifikan bagi akselerasi daerah.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 group">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 flex-shrink-0 flex items-center justify-center transition-colors group-hover:bg-amber-400/20">
                                    <Icon icon="solar:users-group-rounded-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1 text-slate-950 dark:text-white font-['Outfit']">Kolaboratif</h3>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Melibatkan berbagai pemangku kepentingan secara inklusif dalam pembangunan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative reveal reveal-d1">
                        <div class="aspect-square bg-slate-100 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800 rounded-3xl overflow-hidden relative shadow-lg">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-400/15 via-amber-400/0 to-sky-400/10 backdrop-blur-[2px]"></div>
                            <!-- Logo SiKerja -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <img src="/foto/logo-sikerja.svg" alt="SiKerja" class="w-48 h-48 opacity-85 drop-shadow-2xl transition-transform duration-500 hover:scale-105" />
                            </div>
                        </div>
                        <!-- Floating Card -->
                        <div class="absolute -bottom-8 -left-8 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-6 rounded-2xl shadow-2xl border border-slate-150 dark:border-slate-800/80 max-w-xs transition-all duration-300 hover:scale-[1.03]">
                            <div class="flex items-center gap-3 mb-2">
                                <Icon icon="solar:verified-check-bold-duotone" class="w-6 h-6 text-amber-500" />
                                <span class="font-black font-['Outfit'] text-slate-955 dark:text-white text-sm">Teruji & Terpercaya</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Telah memfasilitasi ratusan kesepakatan kerjasama strategis Pemkot Samarinda.</p>
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

.reveal.reveal-d1 { transition-delay: 0.15s; }
.reveal.reveal-d2 { transition-delay: 0.3s; }
</style>
