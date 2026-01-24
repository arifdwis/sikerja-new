<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import { computed } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    page: {
        type: Object,
        required: true
    }
})

const sharedProps = usePage().props
const allPages = computed(() => sharedProps.laman_menu || [])

// Find related pages (simple logic: same category key via title match, or just 5 random others)
const relatedPages = computed(() => {
    // Exclude current page
    return allPages.value.filter(p => p.url !== window.location.pathname).slice(0, 5)
})
</script>

<template>
    <Head :title="page.label" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden min-h-screen flex flex-col">
        <PageHeader 
            :title="page.label" 
            :subtitle="page.created_at ? 'Dipublikasikan pada ' + new Date(page.created_at).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : ''"
            :breadcrumbs="[{ label: 'Informasi', url: '#' }, { label: page.label, url: '#' }]"
        />

        <section class="flex-grow py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 md:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    
                    <!-- Main Content -->
                    <div class="lg:col-span-8">
                        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 p-8 md:p-12">
                            <article class="prose prose-lg prose-emerald dark:prose-invert max-w-none">
                                <div v-html="page.content"></div>
                            </article>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <aside class="lg:col-span-4 space-y-8">
                        <!-- Quick Actions / Download (Placeholder) -->
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                            <h3 class="text-xl font-bold mb-4 relative z-10">Butuh Bantuan?</h3>
                            <p class="text-emerald-50 mb-6 text-sm relative z-10">
                                Jika ada pertanyaan mengenai dokumen atau informasi ini, silakan hubungi kami.
                            </p>
                            <Link href="/faq" class="inline-flex items-center gap-2 bg-white text-emerald-600 px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:shadow-md transition-all relative z-10">
                                <Icon icon="solar:question-circle-bold" />
                                Lihat FAQ
                            </Link>
                        </div>

                        <!-- Related Pages -->
                        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2 text-gray-800 dark:text-white">
                                <Icon icon="solar:document-text-bold-duotone" class="text-emerald-500" />
                                Informasi Terkait
                            </h3>
                            <div class="space-y-3">
                                <Link 
                                    v-for="(item, idx) in relatedPages" 
                                    :key="idx" 
                                    :href="item.url"
                                    class="group flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <div class="mt-1 min-w-[4px] h-[4px] rounded-full bg-gray-300 group-hover:bg-emerald-500 transition-colors"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors font-medium">
                                        {{ item.label }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </aside>

                </div>
            </div>
        </section>
    </main>

    <Footer />
</template>
