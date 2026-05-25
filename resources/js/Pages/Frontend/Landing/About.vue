<script setup>
import { Head } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import { Icon } from '@iconify/vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { onMounted } from 'vue'

const props = defineProps({
    page: Object
})

onMounted(() => {
    AOS.init({ duration: 600, once: true, offset: 50 })
})
</script>

<template>
    <Head :title="page ? page.label : 'Tentang Sikerja'" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-[#f4f6f8] dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        <PageHeader 
            :title="page ? page.label : 'Tentang Sikerja'" 
            :subtitle="page ? '' : 'Mengenal lebih dalam platform inovasi kerjasama daerah Kota Samarinda'"
            :breadcrumbs="[{ label: 'Tentang', url: '/tentang' }]"
        />

        <section class="py-24">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Data from Database -->
                <!-- Data from Database -->
                <div v-if="page" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <!-- Main Content -->
                    <div class="lg:col-span-8">
                        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 p-8 md:p-12">
                            <article class="prose prose-lg prose-amber dark:prose-invert max-w-none">
                                <div v-html="page.content"></div>
                            </article>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <aside class="lg:col-span-4 space-y-8">
                        <div class="bg-slate-900 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden border border-slate-800">
                            <h3 class="text-xl font-bold mb-4 relative z-10">Visi Kami</h3>
                            <p class="text-slate-200 mb-6 text-sm relative z-10 leading-relaxed">
                                Mewujudkan Samarinda sebagai kota pusat peradaban yang maju dan berkelanjutan melalui sinergi kerjasama.
                            </p>
                        </div>
                    </aside>
                </div>

                <!-- Static Fallback -->
                <div v-else class="grid lg:grid-cols-2 gap-16 items-center mb-24">
                    <div data-aos="fade-right">
                        <span class="text-amber-600 font-bold tracking-wider uppercase text-sm">Visi & Misi</span>
                        <h2 class="font-['Outfit'] text-3xl lg:text-4xl font-black mt-2 mb-6 text-gray-900 dark:text-white">
                            Mewujudkan Kerjasama Daerah yang Berkualitas dan Berdaya Saing
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                            Sikerja dibangun dengan visi untuk menciptakan ekosistem kerjasama daerah yang transparan, akuntabel, dan memberikan manfaat nyata bagi pembangunan Kota Samarinda.
                        </p>
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-100 flex-shrink-0 flex items-center justify-center text-amber-700">
                                    <Icon icon="solar:target-bold" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1">Strategis</h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Fokus pada kerjasama yang memberikan dampak signifikan bagi daerah.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-100 flex-shrink-0 flex items-center justify-center text-amber-700">
                                    <Icon icon="solar:users-group-rounded-bold" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1">Kolaboratif</h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Melibatkan berbagai pemangku kepentingan dalam pembangunan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative" data-aos="fade-left">
                        <div class="aspect-square bg-gray-100 rounded-3xl overflow-hidden relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-300/25 to-sky-300/20"></div>
                            <!-- Logo SiKerja -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <img src="/foto/logo-sikerja.svg" alt="SiKerja" class="w-48 h-48 opacity-80 drop-shadow-lg" />
                            </div>
                        </div>
                        <!-- Floating Card -->
                        <div class="absolute -bottom-8 -left-8 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 max-w-xs">
                            <div class="flex items-center gap-3 mb-2">
                                <Icon icon="solar:verified-check-bold" class="w-6 h-6 text-amber-500" />
                                <span class="font-bold">Teruji & Terpercaya</span>
                            </div>
                            <p class="text-xs text-gray-500">Telah memfasilitasi ratusan kesepakatan kerjasama strategis.</p>
                        </div>
                    </div>
                </div>



                <!-- Stats / Milestones could go here -->
            </div>
        </section>
    </main>

    <Footer />
    <ChatWidget />
</template>
