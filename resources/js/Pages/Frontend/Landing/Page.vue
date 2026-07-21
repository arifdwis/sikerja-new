<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Icon } from '@iconify/vue'
import DOMPurify from 'dompurify'

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
const appUrl = import.meta.env.VITE_APP_URL || window.location.origin
const allPages = computed(() => sharedProps.laman_menu || [])

const relatedPages = computed(() => {
    return allPages.value.filter(p => p.url !== window.location.pathname).slice(0, 5)
})

const safeContent = computed(() => {
    return props.page?.content ? DOMPurify.sanitize(props.page.content) : ''
})

const formattedDate = computed(() => {
    if (!props.page.created_at) return ''
    return new Date(props.page.created_at).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    })
})

const isFileLink = (href) => /\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar)$/i.test(href)

const getFileExt = (href) => {
    const match = href.match(/\.(\w+)$/i)
    return match ? match[1].toUpperCase() : 'FILE'
}

// Search state for document files
const fileSearchQuery = ref('')
const filteredFileLinks = computed(() => {
    if (!fileSearchQuery.value) return props.fileLinks
    return props.fileLinks.filter(link => 
        link.text.toLowerCase().includes(fileSearchQuery.value.toLowerCase())
    )
})

// Share state
const copied = ref(false)
const copyLink = () => {
    navigator.clipboard.writeText(window.location.href)
    copied.value = true
    setTimeout(() => {
        copied.value = false
    }, 2000)
}

// Check if the page content is just a repeat of the page title (common in raw attachments pages)
const isRepeatedTitle = computed(() => {
    if (!props.page.content) return false
    const stripped = props.page.content.replace(/<[^>]*>/g, '').trim()
    return stripped.toLowerCase() === props.page.label.toLowerCase()
})

// Generate dynamic properties for various file types
const getFileMeta = (href) => {
    const ext = getFileExt(href).toLowerCase()
    if (ext === 'pdf') {
        return {
            icon: 'solar:document-text-bold-duotone',
            bgColor: 'bg-red-500/10 dark:bg-red-500/20 text-red-500 dark:text-red-405',
            badgeBg: 'bg-red-500/10 text-red-650 dark:text-red-400 border border-red-500/20'
        }
    } else if (['xls', 'xlsx'].includes(ext)) {
        return {
            icon: 'solar:document-text-bold-duotone',
            bgColor: 'bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-500 dark:text-emerald-400',
            badgeBg: 'bg-emerald-500/10 text-emerald-655 dark:text-emerald-400 border border-emerald-500/20'
        }
    } else if (['doc', 'docx'].includes(ext)) {
        return {
            icon: 'solar:document-bold-duotone',
            bgColor: 'bg-blue-500/10 dark:bg-blue-500/20 text-blue-500 dark:text-blue-400',
            badgeBg: 'bg-blue-500/10 text-blue-650 dark:text-blue-400 border border-blue-500/20'
        }
    } else if (['zip', 'rar'].includes(ext)) {
        return {
            icon: 'solar:archive-bold-duotone',
            bgColor: 'bg-amber-500/10 dark:bg-amber-500/20 text-amber-500 dark:text-amber-400',
            badgeBg: 'bg-amber-500/10 text-amber-650 dark:text-amber-400 border border-amber-500/20'
        }
    } else {
        return {
            icon: 'solar:document-bold-duotone',
            bgColor: 'bg-teal-500/10 dark:bg-teal-500/20 text-teal-500 dark:text-teal-400',
            badgeBg: 'bg-teal-500/10 text-teal-650 dark:text-teal-400 border border-teal-500/20'
        }
    }
}

let revealObserver = null
const initRevealAnimations = () => {
    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed')
                revealObserver.unobserve(entry.target)
            }
        })
    }, { threshold: 0.1 })

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
    <Head :title="page.label" />
    <Navbar />

    <main class="font-['Inter'] antialiased bg-gradient-to-tr from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 text-gray-900 dark:text-slate-100 overflow-x-hidden min-h-screen flex flex-col transition-colors duration-300">
        <PageHeader 
            :title="page.label" 
            :subtitle="formattedDate ? 'Dipublikasikan pada ' + formattedDate : ''"
            :breadcrumbs="[{ label: 'Informasi', url: '#' }, { label: page.label, url: '#' }]"
        />

        <section class="flex-grow py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-6">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    
                    <!-- Left Column: Unified Document Dashboard Card -->
                    <div class="lg:col-span-8 space-y-0 reveal">
                        
                        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-slate-900/5 dark:shadow-slate-950/20 border border-slate-200/50 dark:border-slate-800/80 overflow-hidden transition-colors duration-300">
                            
                            <!-- Card Body Content -->
                            <div class="p-6 md:p-10 space-y-8">
                                
                                <!-- Elegant Dynamic Intro Banner (If title is repeated, show descriptive lead instead of raw text) -->
                                <div v-if="isRepeatedTitle" class="p-6 rounded-2xl bg-amber-500/5 dark:bg-amber-400/5 border border-amber-400/20 dark:border-amber-400/10 flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-amber-400/15 dark:bg-amber-400/10 flex items-center justify-center shrink-0">
                                        <Icon icon="solar:info-circle-bold-duotone" class="text-amber-500 w-5 h-5" />
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-slate-900 dark:text-white text-sm font-['Outfit']">Lampiran Dokumen Kerjasama</h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                            Halaman ini merupakan berkas resmi **{{ page.label }}**. Silakan gunakan tombol unduh di bagian bawah untuk melihat dokumen selengkapnya.
                                        </p>
                                    </div>
                                </div>

                                <!-- Rich typography content (if it has actual custom body content) -->
                                <article v-else-if="page.content" class="prose dark:prose-invert max-w-none prose-slate prose-headings:font-['Outfit'] prose-headings:font-black prose-headings:tracking-tight prose-a:text-teal-600 dark:prose-a:text-teal-400 prose-img:rounded-2xl prose-blockquote:border-l-4 prose-blockquote:border-amber-400 prose-blockquote:bg-slate-50 dark:prose-blockquote:bg-slate-850/40 prose-blockquote:p-5 prose-blockquote:rounded-r-2xl prose-blockquote:not-italic">
                                    <div v-html="safeContent"></div>
                                </article>

                                <div v-else class="py-10 text-center text-slate-400 dark:text-slate-500 italic text-sm">
                                    Tidak ada deskripsi tertulis pada halaman ini.
                                </div>

                                <!-- Folder Section: File List -->
                                <div v-if="fileLinks.length > 0" class="pt-8 border-t border-slate-100 dark:border-slate-800">
                                    
                                    <!-- Attachment header and filter search in one line -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-400/10 flex items-center justify-center border border-amber-500/20 shrink-0">
                                                <Icon icon="solar:folder-open-bold-duotone" class="text-amber-500 w-5 h-5" />
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900 dark:text-white font-['Outfit']">Lampiran Dokumen Resmi</h3>
                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 leading-none mt-0.5">Terdapat {{ fileLinks.length }} berkas resmi untuk diunduh</p>
                                            </div>
                                        </div>

                                        <!-- Small clean search input -->
                                        <div class="relative w-full sm:w-60">
                                            <input 
                                                v-model="fileSearchQuery" 
                                                type="text" 
                                                placeholder="Cari lampiran berkas..."
                                                class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-amber-400 focus:bg-white dark:focus:bg-slate-900 focus:border-amber-400 dark:focus:border-amber-400 outline-none transition-all placeholder-slate-450"
                                            />
                                            <Icon icon="solar:magnifer-linear" class="absolute left-3 top-2 w-4 h-4 text-slate-400" />
                                        </div>
                                    </div>

                                    <!-- Clean full-width list cards to look balanced and tidy -->
                                    <div v-if="filteredFileLinks.length > 0" class="space-y-3">
                                        <a 
                                            v-for="(link, idx) in filteredFileLinks" 
                                            :key="idx"
                                            :href="link.href"
                                            target="_blank"
                                            class="group relative flex items-center justify-between gap-4 bg-slate-50/45 dark:bg-slate-950/20 hover:bg-amber-500/[0.03] dark:hover:bg-amber-450/[0.03] rounded-2xl border border-slate-200/60 dark:border-slate-800/80 hover:border-amber-450 dark:hover:border-amber-450/40 p-4 transition-all duration-200"
                                        >
                                            <div class="flex items-center gap-4 min-w-0">
                                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border border-slate-200/10 shadow-inner group-hover:scale-105 transition-transform"
                                                    :class="getFileMeta(link.href).bgColor"
                                                >
                                                    <Icon 
                                                        :icon="getFileMeta(link.href).icon" 
                                                        class="w-6 h-6"
                                                    />
                                                </div>

                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors text-xs md:text-sm leading-snug line-clamp-2">
                                                        {{ link.text }}
                                                    </h4>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded" :class="getFileMeta(link.href).badgeBg">
                                                            {{ getFileExt(link.href) }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400 dark:text-slate-500">Official Document</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="shrink-0 w-9 h-9 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 group-hover:bg-amber-400 dark:group-hover:bg-amber-400 text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-950 flex items-center justify-center shadow-sm group-hover:shadow-md transition-all duration-300">
                                                <Icon icon="solar:download-minimalistic-bold" class="w-4.5 h-4.5 group-hover:translate-y-0.5 transition-transform" />
                                            </div>
                                        </a>
                                    </div>
                                    <div v-else class="text-center py-8 border border-dashed border-slate-250 dark:border-slate-800 rounded-2xl bg-slate-50/20 dark:bg-slate-950/10">
                                        <Icon icon="solar:folder-error-bold-duotone" class="w-10 h-10 text-slate-350 dark:text-slate-600 mx-auto mb-2" />
                                        <p class="text-xs text-slate-400 dark:text-slate-500">Berkas tidak ditemukan</p>
                                    </div>
                                </div>

                            </div>

                            <!-- Integrated Unified Card Footer: Share Widget -->
                            <div class="bg-slate-50/50 dark:bg-slate-950/30 px-6 py-4 md:px-10 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="flex items-center gap-2.5">
                                    <Icon icon="solar:share-bold-duotone" class="text-amber-500 w-5 h-5 shrink-0" />
                                    <span class="text-xs text-slate-500 dark:text-slate-455 font-medium leading-none">Bagikan dokumen dan tautan informasi resmi ini</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button 
                                        @click="copyLink"
                                        class="relative px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-850 hover:bg-amber-500/10 dark:hover:bg-amber-400/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-amber-400 dark:hover:border-amber-400/40 shadow-sm transition-all flex items-center gap-2 group"
                                    >
                                        <Icon :icon="copied ? 'solar:check-square-bold-duotone' : 'solar:copy-bold-duotone'" class="w-4 h-4 text-slate-450 group-hover:text-amber-500 transition-colors" />
                                        {{ copied ? 'Tautan Disalin!' : 'Salin Tautan' }}
                                    </button>

                                    <a 
                                        :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent(page.label + ' ' + appUrl + window.location.pathname)"
                                        target="_blank"
                                        class="p-2 rounded-xl bg-[#25D366]/10 hover:bg-[#25D366]/20 border border-[#25D366]/20 text-[#25D366] transition-all flex items-center justify-center"
                                        title="Bagikan ke WhatsApp"
                                    >
                                        <Icon icon="mdi:whatsapp" class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Right Column: Premium Integrated Sidebar -->
                    <aside class="lg:col-span-4 space-y-6 reveal reveal-d1">
                        
                        <!-- Metadata Hub Widget -->
                        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-slate-900/5 border border-slate-200/50 dark:border-slate-800/80 p-6 space-y-5">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white pb-3.5 border-b border-slate-150 dark:border-slate-800 flex items-center gap-2 font-['Outfit']">
                                <Icon icon="solar:info-square-bold-duotone" class="text-amber-500 w-4.5 h-4.5" />
                                Hub Informasi
                            </h3>

                            <div class="space-y-3.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">Status Halaman</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 border border-teal-500/10 dark:border-teal-900/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                                        Aktif / Publik
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">Tipe Konten</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-350">
                                        {{ fileLinks.length > 0 ? 'Dokumen & Lampiran' : 'Informasi Umum' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">Tanggal Rilis</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-350 flex items-center gap-1.5">
                                        <Icon icon="solar:calendar-bold-duotone" class="text-slate-400 w-4 h-4" />
                                        {{ formattedDate ? new Date(page.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '-' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">Penerbit</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-350 flex items-center gap-1.5">
                                        <Icon icon="solar:shield-user-bold-duotone" class="text-slate-400 w-4 h-4" />
                                        Bagian Kerjasama
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Need Help Callout Box -->
                        <div class="bg-slate-900 rounded-3xl p-6 text-white border border-slate-800/80 shadow-lg relative overflow-hidden group">
                            <!-- Background glow effects -->
                            <div class="absolute -right-16 -top-16 w-36 h-36 rounded-full bg-teal-500/10 blur-2xl group-hover:bg-teal-500/20 transition-all duration-500"></div>
                            <div class="absolute -left-16 -bottom-16 w-36 h-36 rounded-full bg-amber-500/10 blur-2xl group-hover:bg-amber-500/20 transition-all duration-500"></div>
                            
                            <h3 class="text-base font-bold mb-2.5 relative z-10 font-['Outfit']">Butuh Bantuan?</h3>
                            <p class="text-slate-300 mb-4 text-xs relative z-10 leading-relaxed">
                                Jika Anda memerlukan bantuan administrasi pengajuan, penjelasan file kerjasama, silakan kunjungi Help Center.
                            </p>
                            <Link href="/faq" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all relative z-10">
                                <Icon icon="solar:question-circle-bold" />
                                Hubungi Kami / FAQ
                            </Link>
                        </div>

                        <!-- Refined Related Pages Widget with clean margins and list indicators -->
                        <div v-if="relatedPages.length" class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-slate-900/5 border border-slate-200/50 dark:border-slate-800/80 p-6 space-y-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white pb-3.5 border-b border-slate-150 dark:border-slate-800 flex items-center gap-2 font-['Outfit']">
                                <Icon icon="solar:document-text-bold-duotone" class="text-amber-500 w-4.5 h-4.5" />
                                Informasi Terkait
                            </h3>
                            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                <Link 
                                    v-for="(item, idx) in relatedPages" 
                                    :key="idx" 
                                    :href="item.url"
                                    class="group flex items-start justify-between gap-3 py-3 hover:text-amber-600 dark:hover:text-amber-400 transition-colors"
                                >
                                    <div class="flex gap-2.5 min-w-0">
                                        <Icon icon="solar:document-text-linear" class="w-4 h-4 text-slate-400 group-hover:text-amber-500 mt-0.5 shrink-0 transition-colors" />
                                        <span class="text-xs text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white font-medium leading-relaxed transition-colors line-clamp-2">
                                            {{ item.label }}
                                        </span>
                                    </div>
                                    <Icon icon="solar:alt-arrow-right-linear" class="w-3.5 h-3.5 text-slate-350 group-hover:text-amber-500 dark:group-hover:text-amber-400 mt-0.5 shrink-0 group-hover:translate-x-0.5 transition-all" />
                                </Link>
                            </div>
                        </div>
                    </aside>
                </div>

            </div>
        </section>
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
