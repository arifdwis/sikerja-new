<script setup>
import { ref, onMounted, watchEffect } from 'vue';
import axios from 'axios';
import { Icon } from "@iconify/vue";
import { FwbSelect, FwbToggle } from 'flowbite-vue';
import { router, usePage } from '@inertiajs/vue3';
import { eventBus } from '@/utils/eventBus';

// State
const menuItems = ref([]);
const openDropdowns = ref({});
const selected = ref(localStorage.getItem('sidebarSelected') || '');
const expandAll = ref(JSON.parse(localStorage.getItem('sidebarExpandAll') || 'false'));
const currentRoute = ref(usePage().url);
const isCollapsed = ref(JSON.parse(localStorage.getItem('sidebarCollapsed') || 'true'));

// Daftar menu parent
const menuParents = [
    { value: '', name: 'Menu Utama' },
    { value: 'master_data', name: 'Master Data' },
    { value: 'pengaturan_logs', name: 'Pengaturan Logs' },
    { value: 'daftar_isi_arsip', name: 'Daftar Isi Arsip' },
    { value: 'depo_arsip', name: 'Depo Arsip' },
    { value: 'laporan_arsip', name: 'Laporan Arsip' },
    { value: 'publikasi_berkas', name: 'Publikasi Berkas' },
    { value: 'pengaturan_sistem', name: 'Pengaturan Sistem' },
];

// Memuat daftar menu berdasarkan kategori yang dipilih
const updateMenuItems = async () => {
    try {
        localStorage.setItem('sidebarSelected', selected.value);

        const cachedMenu = localStorage.getItem(`sidebarMenu_${selected.value}`);
        if(cachedMenu) {
            menuItems.value = JSON.parse(cachedMenu);
            initializeDropdownState();
            expandActiveParents();
            return;
        }

        const response = await axios.get(`/api/sidebar/${selected.value}`);
        menuItems.value = response.data;
        initializeDropdownState();
        expandActiveParents();
    } catch (error) {
        console.error('Gagal memuat menu sidebar:', error);
    }
};

// Cek apakah suatu item aktif
const isActive = (route) => {
    const currentSegments = currentRoute.value.split('/').slice(0, 3).join('/');
    const targetSegments = route.split('/').slice(0, 3).join('/');
    return currentSegments === targetSegments;
};

// Pastikan parent expand jika ada child yang aktif
const expandActiveParents = () => {
    menuItems.value.forEach((item, index) => {
        if(item.children && item.children.some(child => isActive(child.route))) {
            openDropdowns.value[index] = true;
        }
    });
};

// Navigasi ke route
const navigateTo = (url) => {
    router.visit(url);
};

// Toggle dropdown individual
const toggleDropdown = (index) => {
    openDropdowns.value[index] = !openDropdowns.value[index];
};

// Toggle semua dropdown berdasarkan toggle expandAll
const toggleAllDropdowns = () => {
    menuItems.value.forEach((_, index) => {
        openDropdowns.value[index] = expandAll.value;
    });
    localStorage.setItem('sidebarExpandAll', JSON.stringify(expandAll.value));
};

// Inisialisasi dropdown
const initializeDropdownState = () => {
    menuItems.value.forEach((_, index) => {
        openDropdowns.value[index] = expandAll.value;
    });
};

// Perubahan pada `selected` langsung memicu update menu
watchEffect(() => {
    updateMenuItems();
});

// Watch perubahan route agar sidebar tetap sinkron
watchEffect(() => {
    expandActiveParents();
});

// Ambil data dari localStorage saat komponen dimuat
onMounted(() => {
    updateMenuItems();
});

// Sinkronisasi event toggle sidebar dari eventBus
onMounted(() => {
    eventBus.on('toggle-sidebar', (state) => {
        isCollapsed.value = state;
        localStorage.setItem('sidebarCollapsed', JSON.stringify(state));
    });
});

</script>
<template>
    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed top-0 left-0 z-20 flex flex-col flex-shrink-0 h-full pt-16 font-normal duration-300 lg:flex transition-width bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" :class="{ 'w-64': isCollapsed, 'w-16': !isCollapsed }" aria-label="Sidebar">
            <div class="relative flex flex-col flex-1 min-h-0 pt-0">
                <div class="flex flex-col flex-1 pt-2 overflow-y-auto">
                    <div class="flex-1 px-3 space-y-1 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Toggle Expand/Collapse -->
                        <div class="py-2 space-y-4" v-if="isCollapsed">
                            <fwb-select color="teal" v-model="selected" :options="menuParents" @change="updateMenuItems" />
                            <fwb-toggle size="sm" color="teal" v-model="expandAll" label="Expand All" @change="toggleAllDropdowns" />
                        </div>
                        <ul class="py-2 space-y-2">
                            <li v-for="(item, index) in menuItems" :key="item.title">
                                <!-- Menu Utama -->
                                <div v-if="!item.children || isCollapsed" @click="item.children ? toggleDropdown(index) : navigateTo(item.route)" class="flex items-center justify-between py-1.5 px-2 text-base font-medium rounded-lg cursor-pointer transition-all duration-150" :class="{
                              'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white': isActive(item.route),
                              'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-200': !isActive(item.route)
                           }">
                                    <div class="flex items-center">
                                        <Icon class="w-6 h-6 transition duration-75" :class="{ 
                                 'text-gray-900 dark:text-white': isActive(item.route),
                                 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !isActive(item.route)
                              }" :icon="item.icon" />
                                        <span v-if="isCollapsed" class="ml-3">{{ item.title }}</span>
                                    </div>
                                    <svg v-if="item.children && isCollapsed" class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': openDropdowns[index] }" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <!-- Sub-menu -->
                                <ul v-if="item.children && openDropdowns[index] && isCollapsed" class="pl-5 space-y-1">
                                    <li v-for="child in item.children" :key="child.title">
                                        <a :href="child.route" class="block py-1 px-2 text-base font-medium border-l border-dashed transition-all duration-150 rounded-e-lg" :class="{
                                    'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white': isActive(child.route),
                                    'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300': !isActive(child.route)
                                 }">
                                            <span class="ml-4">{{ child.title }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>
        <!-- Konten Utama -->
        <div class="flex-1 p-6 transition-all duration-300" :class="{ 'ml-64': isCollapsed, 'ml-16': !isCollapsed }">
            <slot />
        </div>
    </div>
</template>
