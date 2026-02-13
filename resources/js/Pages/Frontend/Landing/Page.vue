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
    },
    fileLinks: {
        type: Array,
        default: () => []
    }
})

const sharedProps = usePage().props
const allPages = computed(() => sharedProps.laman_menu || [])

const relatedPages = computed(() => {
    return allPages.value.filter(p => p.url !== window.location.pathname).slice(0, 5)
})

const formattedDate = computed(() => {
    if (!props.page.created_at) return ''
    return new Date(props.page.created_at).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    })
})

const isShortContent = computed(() => {
    if (props.fileLinks.length > 0) return true
    if (!props.page.content) return true
    const text = props.page.content.replace(/<[^>]*>/g, '').trim()
    return text.length <= 300
})

const isFileLink = (href) => /\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar)$/i.test(href)

const getFileExt = (href) => {
    const match = href.match(/\.(\w+)$/i)
    return match ? match[1].toUpperCase() : 'FILE'
}
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

        <section class="flex-grow py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-4 md:px-6">

                <!-- Compact layout for pages with file downloads -->
                <div v-if="isShortContent" class="max-w-4xl mx-auto">

                    <!-- File download cards -->
                    <div v-if="fileLinks.length > 0" class="space-y-3">
                        <a 
                            v-for="(link, idx) in fileLinks" 
                            :key="idx"
                            :href="link.href"
                            target="_blank"
                            class="group flex items-center gap-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 hover:border-emerald-400 hover:shadow-lg transition-all p-6"
                        >
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                                :class="isFileLink(link.href) ? 'bg-red-50 dark:bg-red-900/20' : 'bg-emerald-50 dark:bg-emerald-900/20'"
                            >
                                <Icon 
                                    icon="solar:document-bold" 
                                    class="w-6 h-6"
                                    :class="isFileLink(link.href) ? 'text-red-500' : 'text-emerald-500'"
                                />
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors text-base line-clamp-2">
                                    {{ link.text }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span v-if="isFileLink(link.href)" class="text-xs font-bold px-2 py-0.5 rounded bg-red-100 text-red-600">
                                        {{ getFileExt(link.href) }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ formattedDate }}</span>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-500 text-white group-hover:bg-emerald-600 shadow-sm group-hover:shadow-md transition-all">
                                <Icon icon="solar:download-minimalistic-bold" class="w-4 h-4" />
                                Download
                            </div>
                        </a>
                    </div>

                    <!-- Plain text content (no file links) -->
                    <div v-else class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                        <article class="prose prose-emerald dark:prose-invert max-w-none">
                            <div v-html="page.content"></div>
                        </article>
                    </div>

                    <!-- Related pages -->
                    <div v-if="relatedPages.length" class="mt-8">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Icon icon="solar:documents-bold-duotone" class="text-emerald-500 w-4 h-4" />
                            Informasi Lainnya
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <Link 
                                v-for="(item, idx) in relatedPages" 
                                :key="idx" 
                                :href="item.url"
                                class="group flex items-center gap-3 bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800 hover:border-emerald-300 hover:shadow-md transition-all"
                            >
                                <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 group-hover:bg-emerald-100 flex items-center justify-center shrink-0 transition-colors">
                                    <Icon icon="solar:document-text-linear" class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors" />
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-emerald-600 transition-colors font-medium line-clamp-2">
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
                                    <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-emerald-600 transition-colors font-medium">
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
