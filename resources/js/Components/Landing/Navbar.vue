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
        format: { title: 'Format & Dokumen', icon: 'solar:document-add-bold-duotone', color: 'text-blue-500', items: [] },
        panduan: { title: 'Panduan & Informasi', icon: 'solar:book-bookmark-bold-duotone', color: 'text-purple-500', items: [] }
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
        'fixed top-0 left-0 right-0 z-[100] transition-all duration-300',
        isScrolled ? 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg shadow-lg' : 'bg-white/10 backdrop-blur-md border-b border-white/20'
    ]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-20">
                <Link href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-500 rounded-xl flex items-center justify-center shadow-lg">
                        <Icon icon="solar:document-bold" class="w-5 h-5 text-white" />
                    </div>
                    <span :class="[
                        'text-xl font-black transition-colors',
                        isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'
                    ]">SIKERJA</span>
                </Link>

                <div class="hidden md:flex items-center gap-8">
                    <Link href="/" :class="[
                        'font-semibold hover:text-emerald-400 transition-colors',
                        isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'
                    ]">Beranda</Link>
                    <Link href="/alur" :class="[
                        'font-semibold hover:text-emerald-400 transition-colors',
                        isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'
                    ]">Alur Proses</Link>
                    
                    <!-- Mega Menu Dropdown -->
                    <div class="relative group" v-if="lamanMenu.length">
                        <button class="flex items-center gap-1 font-semibold hover:text-emerald-400 transition-colors py-8"
                            :class="isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'">
                            Informasi
                            <Icon icon="solar:alt-arrow-down-bold" class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" />
                        </button>
                        
                        <!-- Mega Menu Content -->
                        <div class="absolute top-full right-[-100px] mt-0 w-[800px] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden transform opacity-0 translate-y-2 invisible group-hover:opacity-100 group-hover:translate-y-0 group-hover:visible transition-all duration-300">
                            <div class="grid grid-cols-3 gap-0 divide-x divide-gray-100 dark:divide-gray-800">
                                <div v-for="(group, key) in groupedLaman" :key="key" class="p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div :class="['p-2 rounded-lg bg-gray-50 dark:bg-gray-800', group.color]">
                                            <Icon :icon="group.icon" class="w-5 h-5" />
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ group.title }}</h3>
                                    </div>
                                    <ul class="space-y-3">
                                        <li v-for="(item, idx) in group.items" :key="idx">
                                            <Link :href="item.url" class="group/item flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                                <Icon icon="solar:arrow-right-linear" class="w-4 h-4 mt-0.5 opacity-0 -translate-x-2 group-hover/item:opacity-100 group-hover/item:translate-x-0 transition-all text-emerald-500" />
                                                <span class="line-clamp-2 transform translate-x-[-1rem] group-hover/item:translate-x-0 transition-transform duration-200">{{ item.label }}</span>
                                            </Link>
                                        </li>
                                        <li v-if="group.items.length === 0" class="text-xs text-gray-400 italic">Tidak ada dokumen</li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Static Footer Links inside Menu -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-3 flex justify-between items-center border-t border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-500 font-medium tracking-wide">PUSAT BANTUAN</span>
                                <div class="flex gap-4 text-sm font-semibold">
                                    <Link href="/tentang" class="text-gray-600 hover:text-emerald-600 transition-colors flex items-center gap-1">
                                        Tentang Kami
                                    </Link>
                                    <Link href="/faq" class="text-gray-600 hover:text-emerald-600 transition-colors flex items-center gap-1">
                                        FAQ
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <Link v-else href="/tentang" :class="[
                        'font-semibold hover:text-emerald-400 transition-colors',
                        isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'
                    ]">Tentang</Link>

                    <Link href="/login" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold rounded-xl bg-gradient-to-r from-emerald-600 to-green-500 text-white shadow-lg hover:shadow-xl hover:scale-[1.03] transition-all duration-300">
                        <Icon icon="solar:login-3-bold" class="w-4 h-4" />
                        Masuk
                    </Link>
                </div>

                <!-- Mobile Button -->
                <button @click="isOpen = !isOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <Icon :icon="isOpen ? 'solar:close-circle-bold' : 'solar:hamburger-menu-bold'" :class="[
                        'w-6 h-6',
                        isScrolled ? 'text-gray-900 dark:text-white' : 'text-white'
                    ]" />
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="isOpen" class="md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 h-screen overflow-y-auto pb-20">
            <div class="px-6 py-4 space-y-3">
                <Link href="/" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-teal-500 transition-colors">Beranda</Link>
                <Link href="/alur" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-emerald-400 transition-colors">Alur Proses</Link>
                
                <div v-if="lamanMenu.length" class="space-y-4 pl-4 border-l-2 border-gray-100 dark:border-gray-800 my-2">
                    <div v-for="(group, key) in groupedLaman" :key="key">
                        <div class="flex items-center gap-2 mb-2 text-xs font-bold text-gray-400 uppercase tracking-widest">
                            <Icon :icon="group.icon" /> {{ group.title }}
                        </div>
                        <div class="space-y-2">
                            <Link v-for="(item, idx) in group.items" :key="idx" :href="item.url" class="block py-1 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-emerald-500">
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <Link href="/faq" class="block py-2 font-semibold text-gray-900 dark:text-white hover:text-emerald-400 transition-colors">FAQ</Link>
                <Link href="/login" class="flex items-center justify-center gap-2 py-3 text-sm font-semibold rounded-xl bg-gradient-to-r from-emerald-600 to-green-500 text-white shadow-lg">
                    <Icon icon="solar:login-3-bold" class="w-4 h-4" />
                    Masuk
                </Link>
            </div>
        </div>
    </nav>
</template>
