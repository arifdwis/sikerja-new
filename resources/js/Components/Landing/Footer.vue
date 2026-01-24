<script setup>
import { Icon } from '@iconify/vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const lamanMenu = computed(() => page.props.laman_menu || [])
const currentYear = new Date().getFullYear()
</script>

<template>
    <footer class="bg-gray-900 border-t border-gray-800 text-gray-400 py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12 mb-12">
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-500 rounded-xl flex items-center justify-center shadow-lg">
                            <Icon icon="solar:document-bold" class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-xl font-black text-white">SiCerdas</span>
                    </div>
                    <p class="leading-relaxed mb-6 max-w-sm">
                        Sistem Informasi Kerjasama Daerah Terintegrasi Pemerintah Kota Samarinda. Mewujudkan tata kelola kerjasama yang transparan, efektif, dan akuntabel.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-colors">
                            <Icon icon="mdi:instagram" class="w-5 h-5" />
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-colors">
                            <Icon icon="mdi:twitter" class="w-5 h-5" />
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-colors">
                            <Icon icon="mdi:youtube" class="w-5 h-5" />
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-6">Tautan</h4>
                    <ul class="space-y-3">
                        <li><Link href="/" class="text-sm hover:text-emerald-400 transition-colors flex items-center gap-2 group"><Icon icon="solar:alt-arrow-right-line-duotone" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />Beranda</Link></li>
                        <li><Link href="/alur" class="text-sm hover:text-emerald-400 transition-colors flex items-center gap-2 group"><Icon icon="solar:alt-arrow-right-line-duotone" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />Alur Proses</Link></li>
                        <!-- Dynamic Pages -->
                        <!-- Dynamic Pages (Limit 5) -->
                        <li v-for="(item, index) in lamanMenu.slice(0, 5)" :key="index">
                            <Link :href="item.url" class="text-sm hover:text-emerald-400 transition-colors flex items-center gap-2 group">
                                <Icon icon="solar:alt-arrow-right-line-duotone" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                <span class="line-clamp-1">{{ item.label }}</span>
                            </Link>
                        </li>
                        <li><Link href="/faq" class="text-sm hover:text-emerald-400 transition-colors flex items-center gap-2 group"><Icon icon="solar:alt-arrow-right-line-duotone" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />FAQ</Link></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <div class="w-1 h-4 bg-gradient-to-b from-emerald-500 to-green-500 rounded-full"></div>
                        Kontak
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-2.5 group">
                            <Icon icon="solar:map-point-bold" class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0 group-hover:scale-110 transition-transform" />
                            <span class="text-xs leading-relaxed">Jl. Kesuma Bangsa No.25, Samarinda, Kalimantan Timur</span>
                        </li>
                        <li class="flex items-center gap-2.5 group">
                            <Icon icon="solar:phone-bold" class="w-4 h-4 text-emerald-500 flex-shrink-0 group-hover:scale-110 transition-transform" />
                            <a href="tel:05417433322" class="text-xs hover:text-emerald-400 transition-colors">(0541) 743322</a>
                        </li>
                        <li class="flex items-center gap-2.5 group">
                            <Icon icon="solar:letter-bold" class="w-4 h-4 text-emerald-500 flex-shrink-0 group-hover:scale-110 transition-transform" />
                            <a href="mailto:hukum@samarindakota.go.id" class="text-xs hover:text-emerald-400 transition-colors">hukum@samarindakota.go.id</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800/50 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-500 text-center md:text-left">
                    &copy; {{ currentYear }} SiCerdas - Pemerintah Kota Samarinda. All rights reserved.
                </p>
                <div class="flex gap-6 text-xs">
                    <a href="#" class="hover:text-emerald-400 transition-colors">Kebijakan Privasi</a>
                    <span class="text-gray-700">•</span>
                    <a href="#" class="hover:text-emerald-400 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>
</template>
