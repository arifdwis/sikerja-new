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

const relatedPages = computed(() => {
    return allPages.value.filter(p => p.url !== window.location.pathname).slice(0, 5)
})

const hasLongContent = computed(() => {
    if (!props.page.content) return false
    const text = props.page.content.replace(/<[^>]*>/g, '').trim()
    return text.length > 200
})

const formattedDate = computed(() => {
    if (!props.page.created_at) return ''
    return new Date(props.page.created_at).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    })
})
</script>

<template>
    <Head :title="page.label" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-x-hidden min-h-screen flex flex-col">
        <PageHeader 
            :title="page.label" 
            :subtitle="formattedDate ? 'Dipublikasikan pada ' + formattedDate : ''"
            :breadcrumbs="[{ label: 'Informasi', url: '#' }, { label: page.label, url: '#' }]"
        />

        <section class="flex-grow py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4 md:px-6">

                <!-- Compact layout when content is short -->
                <div v-if="!hasLongContent" class="max-w-4xl mx-auto">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <!-- Document info bar -->
                        <div class="flex items-center gap-4 px-6 py-4 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800/30">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shrink-0">
                                <Icon icon="solar:document-text-bold" class="w-5 h-5 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="font-bold text-gray-900 dark:text-white truncate">{{ page.label }}</h2>
                                <p v-if="formattedDate" class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">{{ formattedDate }}</p>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <article class="prose prose-emerald dark:prose-invert max-w-none">
                                <div v-html="page.content"></div>
                            </article>
                        </div>
                    </div>

                    <!-- Related pages - horizontal cards -->
                    <div v-if="relatedPages.length" class="mt-8">
                        <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Icon icon="solar:documents-bold-duotone" class="text-emerald-500 w-4 h-4" />
                            Informasi Lainnya
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <Link 
                                v-for="(item, idx) in relatedPages" 
                                :key="idx" 
                                :href="item.url"
                                class="group flex items-center gap-3 bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md transition-all"
                            >
                                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/30 flex items-center justify-center shrink-0 transition-colors">
                                    <Icon icon="solar:document-text-linear" class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors" />
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors font-medium line-clamp-2">
                                    {{ item.label }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Full layout when content is long -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-8">
                        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-8 md:p-10">
                            <article class="prose prose-lg prose-emerald dark:prose-invert max-w-none">
                                <div v-html="page.content"></div>
                            </article>
                        </div>
                    </div>

                    <aside class="lg:col-span-4 space-y-6">
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full blur-2xl -mr-8 -mt-8"></div>
                            <h3 class="text-lg font-bold mb-3 relative z-10">Butuh Bantuan?</h3>
                            <p class="text-emerald-50 mb-5 text-sm relative z-10">
                                Jika ada pertanyaan mengenai dokumen atau informasi ini, silakan hubungi kami.
                            </p>
                            <Link href="/faq" class="inline-flex items-center gap-2 bg-white text-emerald-600 px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:shadow-md transition-all relative z-10">
                                <Icon icon="solar:question-circle-bold" />
                                Lihat FAQ
                            </Link>
                        </div>

                        <div v-if="relatedPages.length" class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                            <h3 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800 dark:text-white">
                                <Icon icon="solar:document-text-bold-duotone" class="text-emerald-500" />
                                Informasi Terkait
                            </h3>
                            <div class="space-y-2">
                                <Link 
                                    v-for="(item, idx) in relatedPages" 
                                    :key="idx" 
                                    :href="item.url"
                                    class="group flex items-start gap-3 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <div class="mt-1.5 min-w-[4px] h-[4px] rounded-full bg-gray-300 group-hover:bg-emerald-500 transition-colors"></div>
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
