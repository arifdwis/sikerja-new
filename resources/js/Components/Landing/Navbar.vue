<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ref, onMounted, onUnmounted, computed } from 'vue'

const page = usePage()
const lamanMenu = computed(() => page.props.laman_menu || [])

// Grouping Logic for Mega Menu
const groupedLaman = computed(() => {
    const groups = {
        regulasi: { title: 'Regulasi & Kebijakan', icon: 'solar:gavel-bold-duotone', color: 'text-teal-500', items: [] },
        format: { title: 'Format & Dokumen', icon: 'solar:document-add-bold-duotone', color: 'text-cyan-500', items: [] },
        panduan: { title: 'Panduan & Informasi', icon: 'solar:book-bookmark-bold-duotone', color: 'text-amber-500', items: [] }
    };

    lamanMenu.value.forEach(item => {
        const title = item.label.toLowerCase();
        if (title.includes('undang') || title.includes('peraturan') || title.includes('keputusan') || title.includes('daerah')) {
            groups.regulasi.items.push(item);
        } else if (title.includes('format') || title.includes('contoh') || title.includes('surat') || title.includes('template')) {
            groups.format.items.push(item);
        } else {
            groups.panduan.items.push(item);
        }
    });

    return groups;
});

const isOpen = ref(false)
const isScrolled = ref(false)

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
    handleScroll()
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
    <nav :class="[
        'fixed left-0 right-0 z-[100] transition-all duration-500',
        isScrolled 
            ? 'top-4 max-w-7xl mx-4 lg:mx-auto bg-white/85 dark:bg-slate-950/85 backdrop-blur-xl shadow-xl shadow-slate-900/5 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl' 
            : 'top-0 bg-transparent border-b border-white/10'
    ]" class="font-['Inter']">
        <div class="max-w-7xl mx-auto">
            <div :class="[
                'flex items-center justify-between transition-all duration-500',
                isScrolled ? 'h-16 px-6 lg:px-8' : 'h-20 px-6 lg:px-8'
            ]">
                <Link href="/" class="flex items-center gap-3">
                    <img src="/foto/logo-sikerja.svg" alt="SiKerja" class="w-10 h-10 rounded-xl shadow-lg border border-white/20 transition-transform duration-300 hover:scale-105" />
                    <span :class="[
                        'font-[\'Outfit\'] text-xl font-black tracking-wider transition-colors',
                        isScrolled ? 'text-slate-900 dark:text-white' : 'text-white'
                    ]">SIKERJA</span>
                </Link>

                <div class="hidden md:flex items-center gap-8">
                    <Link href="/" :class="[
                        'font-semibold transition-colors',
                        isScrolled ? 'text-slate-800 dark:text-slate-200 hover:text-amber-500' : 'text-white hover:text-amber-300'
                    ]">Beranda</Link>
                    <Link href="/alur" :class="[
                        'font-semibold transition-colors',
                        isScrolled ? 'text-slate-800 dark:text-slate-200 hover:text-amber-500' : 'text-white hover:text-amber-300'
                    ]">Alur Proses</Link>
                    <Link href="/infografis" :class="[
                        'font-semibold transition-colors',
                        isScrolled ? 'text-slate-800 dark:text-slate-200 hover:text-amber-500' : 'text-white hover:text-amber-300'
                    ]">Infografis</Link>
                    
                    <!-- Mega Menu Dropdown -->
                    <div class="relative group" v-if="lamanMenu.length">
                        <button class="flex items-center gap-1 font-semibold transition-colors py-4"
                            :class="isScrolled ? 'text-slate-800 dark:text-slate-200 hover:text-amber-500' : 'text-white hover:text-amber-300'">
                            Informasi
                            <Icon icon="solar:alt-arrow-down-bold" class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" />
                        </button>
                        
                        <!-- Mega Menu Content -->
                        <div class="absolute top-full right-[-100px] mt-0 w-[800px] bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden transform opacity-0 translate-y-2 invisible group-hover:opacity-100 group-hover:translate-y-0 group-hover:visible transition-all duration-300">
                            <div class="grid grid-cols-3 gap-0 divide-x divide-slate-100 dark:divide-slate-800">
                                <div v-for="(group, key) in groupedLaman" :key="key" class="p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div :class="['p-2 rounded-lg bg-slate-50 dark:bg-slate-800', group.color]">
                                            <Icon :icon="group.icon" class="w-5 h-5" />
                                        </div>
                                        <h3 class="font-bold text-slate-900 dark:text-white">{{ group.title }}</h3>
                                    </div>
                                    <ul class="space-y-1.5">
                                        <li v-for="(item, idx) in group.items" :key="idx">
                                            <Link :href="item.url" class="group/item flex items-center gap-3 p-2 rounded-xl hover:bg-amber-500/10 dark:hover:bg-amber-400/10 text-sm text-slate-600 dark:text-slate-400 hover:text-amber-700 dark:hover:text-amber-300 transition-all duration-200">
                                                <div class="w-7 h-7 rounded-lg bg-slate-50 dark:bg-slate-800 group-hover/item:bg-amber-500/20 flex items-center justify-center shrink-0 transition-colors">
                                                    <Icon icon="solar:document-linear" class="w-4.5 h-4.5 text-slate-400 dark:text-slate-500 group-hover/item:text-amber-500 dark:group-hover/item:text-amber-400 transition-colors" />
                                                </div>
                                                <span class="font-medium line-clamp-2 transition-transform duration-200 group-hover/item:translate-x-1">{{ item.label }}</span>
                                            </Link>
                                        </li>
                                        <li v-if="group.items.length === 0" class="text-xs text-slate-400 italic px-2 py-1">Tidak ada dokumen</li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Static Footer Links inside Menu -->
                            <div class="bg-slate-50/50 dark:bg-slate-800/30 px-6 py-3 flex justify-between items-center border-t border-slate-100 dark:border-slate-800">
                                <span class="text-xs text-slate-500 font-medium tracking-wide">PUSAT BANTUAN</span>
                                <div class="flex gap-4 text-sm font-semibold">
                                    <Link href="/tentang" class="text-slate-600 hover:text-amber-600 transition-colors flex items-center gap-1">
                                        Tentang Kami
                                    </Link>
                                    <Link href="/faq" class="text-slate-600 hover:text-amber-600 transition-colors flex items-center gap-1">
                                        FAQ
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <Link v-else href="/tentang" :class="[
                        'font-semibold transition-colors',
                        isScrolled ? 'text-slate-800 dark:text-slate-200 hover:text-amber-500' : 'text-white hover:text-amber-300'
                    ]">Tentang</Link>

                    <Link href="/login" class="inline-flex items-center gap-2 px-6 py-2 text-sm font-semibold rounded-xl bg-amber-400 text-slate-900 shadow-lg shadow-amber-400/20 hover:shadow-xl hover:scale-[1.03] transition-all duration-300">
                        <Icon icon="solar:login-3-bold" class="w-4 h-4" />
                        Masuk
                    </Link>
                </div>

                <!-- Mobile Button -->
                <button @click="isOpen = !isOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <Icon :icon="isOpen ? 'solar:close-circle-bold' : 'solar:hamburger-menu-bold'" :class="['w-6 h-6', isScrolled ? 'text-gray-900' : 'text-white']" />
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="isOpen" class="md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 h-screen overflow-y-auto pb-20">
            <div class="px-6 py-4 space-y-3">
                <Link href="/" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-amber-500 transition-colors">Beranda</Link>
                <Link href="/alur" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-amber-500 transition-colors">Alur Proses</Link>
                <Link href="/infografis" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-amber-500 transition-colors">Infografis</Link>
                
                <div v-if="lamanMenu.length" class="space-y-4 pl-4 border-l border-gray-200 dark:border-gray-800 my-2">
                    <div v-for="(group, key) in groupedLaman" :key="key">
                        <div class="flex items-center gap-2 mb-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                            <Icon :icon="group.icon" class="w-4 h-4 text-amber-500" /> {{ group.title }}
                        </div>
                        <div class="space-y-1">
                            <Link v-for="(item, idx) in group.items" :key="idx" :href="item.url" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-amber-500/10 hover:text-amber-600 transition-colors">
                                <Icon icon="solar:document-linear" class="w-4 h-4 text-gray-400 shrink-0" />
                                <span class="truncate">{{ item.label }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <Link href="/faq" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-amber-500 transition-colors">FAQ</Link>
                <Link href="/login" class="flex items-center justify-center gap-2 py-3 text-sm font-semibold rounded-xl bg-amber-400 text-slate-900 shadow-lg">
                    <Icon icon="solar:login-3-bold" class="w-4 h-4" />
                    Masuk
                </Link>
            </div>
        </div>
    </nav>
</template>
