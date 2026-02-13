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

const formattedDate = computed(() => {
    if (!props.page.created_at) return ''
    return new Date(props.page.created_at).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    })
})

const isShortContent = computed(() => {
    if (!props.page.content) return true
    const text = props.page.content.replace(/<[^>]*>/g, '').trim()
    return text.length <= 300
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

        <section class="flex-grow py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-4 md:px-6">

                <!-- Compact layout for short content -->
                <div v-if="isShortContent" class="max-w-4xl mx-auto">
                    <div class="page-content-compact bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                        <div v-html="page.content"></div>
                    </div>

                    <div v-if="relatedPages.length" class="mt-6">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <Icon icon="solar:documents-bold-duotone" class="text-emerald-500 w-3.5 h-3.5" />
                            Informasi Lainnya
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <Link 
                                v-for="(item, idx) in relatedPages" 
                                :key="idx" 
                                :href="item.url"
                                class="group flex items-center gap-2.5 bg-white dark:bg-gray-900 rounded-lg p-3 border border-gray-100 dark:border-gray-800 hover:border-emerald-300 hover:shadow-sm transition-all"
                            >
                                <div class="w-7 h-7 rounded-md bg-gray-100 dark:bg-gray-800 group-hover:bg-emerald-100 flex items-center justify-center shrink-0 transition-colors">
                                    <Icon icon="solar:document-text-linear" class="w-3.5 h-3.5 text-gray-400 group-hover:text-emerald-500 transition-colors" />
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400 group-hover:text-emerald-600 transition-colors font-medium line-clamp-2">
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

<style scoped>
.page-content-compact :deep(a) {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 1px solid #a7f3d0;
    border-radius: 10px;
    color: #047857;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}
.page-content-compact :deep(a):hover {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-color: #6ee7b7;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}
.page-content-compact :deep(a)::before {
    content: '';
    display: inline-block;
    width: 32px;
    height: 32px;
    min-width: 32px;
    background: #10b981;
    border-radius: 8px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M12 16l-5-5h3V4h4v7h3l-5 5zm-7 2h14v2H5v-2z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 18px;
}
.page-content-compact :deep(a)::after {
    content: 'Download ▸';
    margin-left: auto;
    font-size: 12px;
    font-weight: 700;
    color: white;
    background: #10b981;
    padding: 6px 14px;
    border-radius: 8px;
    white-space: nowrap;
}
.page-content-compact :deep(a):hover::after {
    background: #059669;
}
</style>
