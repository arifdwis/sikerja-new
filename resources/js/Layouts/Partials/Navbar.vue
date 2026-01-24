<script setup>
import { ref, watchEffect, onMounted, onUnmounted, nextTick } from 'vue';
import { FwbAvatar, FwbModal } from 'flowbite-vue';
import { Link, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Icon } from '@iconify/vue';
import { eventBus } from '@/utils/eventBus';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const refSearch = ref(null);
const searchQuery = ref('');
const searchedData = ref([]);
const isSearchModal = ref(false);
const isCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const isExpandAll = ref(localStorage.getItem('sidebarExpandAll') === 'true');
const isDarkMode = ref(localStorage.getItem('darkMode') === 'true');
const isShowModal = ref(false);

const menus = ref([
  { label: 'Beranda', route: '/', icon: 'solar:screencast-broken' },
  { label: 'Profil', route: '/profile', icon: 'solar:arrow-right-broken' },
  { label: 'Pengaturan', route: '/pengaturan', icon: 'solar:arrow-right-broken' },
]);

// Deteksi mobile (hanya sekali, lebih ringan)
const isMobile = ref(window.innerWidth <= 640);
const screenSize = ref(window.innerWidth + 'px');
const updateMobileState = () => {
  isMobile.value = window.innerWidth <= 640;
  screenSize.value = window.innerWidth + 'px';
};

const showModal = () => (isShowModal.value = true);
const closeModal = () => (isShowModal.value = false);

const logout = () => router.post(route('logout'));

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  localStorage.setItem('darkMode', isDarkMode.value);
  eventBus.emit('themeChanged', isDarkMode.value);
  document.documentElement.classList.toggle('dark', isDarkMode.value);
};

watchEffect(() => {
  document.documentElement.classList.toggle('dark', isDarkMode.value);
});

const getInitials = (name) => {
  if (!name) return '';
  const names = name.split(' ').filter((n) => n);
  return names.length > 1
    ? (names[0][0] + names[1][0]).toUpperCase()
    : names[0][0].toUpperCase();
};

const currentDateTime = ref('');
const getCurrentDateTime = () => {
  const now = new Date();
  return now.toISOString().slice(0, 19).replace('T', ' ');
};

onMounted(() => {
  currentDateTime.value = getCurrentDateTime();
  setInterval(() => {
    currentDateTime.value = getCurrentDateTime();
  }, 1000);

  updateMobileState();
  window.addEventListener('resize', updateMobileState);

  // Auto-collapse sidebar di mobile saat pertama kali load
  if (isMobile.value && localStorage.getItem('sidebarCollapsed') === null) {
    isCollapsed.value = true;
    localStorage.setItem('sidebarCollapsed', 'true');
    eventBus.emit('toggle-sidebar', true);
  }

  eventBus.on('open-search', openSearchModal);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateMobileState);
});

const openSearchModal = () => {
  isSearchModal.value = true;
  fetchData();
  nextTick(() => {
    refSearch.value?.focus();
  });
};

const closeSearchModal = () => {
  isSearchModal.value = false;
  searchQuery.value = '';
  searchedData.value = [];
};

const fetchData = async () => {
  const token = usePage().props.auth.user.device_token;
  if (!token) {
    // toast.error('Token tidak tersedia.');
    return;
  }
  try {
    const response = await axios.get('/api/search/klasifikasi', {
      params: { token, q: searchQuery.value },
    });
    searchedData.value = Array.isArray(response.data) ? response.data : [];
  } catch (error) {
    console.error('Error fetching data:', error);
    searchedData.value = [];
  }
};

const navigateTo = (route) => {
  router.visit(route);
  closeModal();
};

const goToDetail = (slug) => {
  router.visit(route('filemanager.show', slug));
  closeSearchModal();
};

const toggleAllDropdowns = () => {
  isExpandAll.value = !isExpandAll.value;
  eventBus.emit('toggle-expandall', isExpandAll.value);
  localStorage.setItem('sidebarExpandAll', JSON.stringify(isExpandAll.value));
};

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
  localStorage.setItem('sidebarCollapsed', JSON.stringify(isCollapsed.value));
  eventBus.emit('toggle-sidebar', isCollapsed.value);
};

const truncateText = (text, maxLength = 90) => {
  if (!text) return '';
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};
</script>

<template>
  <!-- Navbar -->
  <nav class="fixed top-0 left-0 right-0 z-40 h-16 border-b border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-center justify-between h-full px-3 sm:px-4">
      <!-- Kiri: Logo + Toggle + Search -->
      <div class="flex items-center flex-1 min-w-0">
        <!-- Logo & Toggle -->
        <div class="flex items-center gap-2">
          <Link :href="route('dashboard')" class="flex-shrink-0" v-if="!isMobile">
            <ApplicationLogo class="h-8 w-auto" />
          </Link>

          <!-- Toggle Desktop (hanya muncul >640px) -->
          <div class="hidden sm:flex items-center gap-2">
            <button @click="toggleSidebar"
              class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700">
              <Icon :icon="isCollapsed ? 'mingcute:left-fill' : 'mingcute:right-fill'" class="w-5 h-5" />
            </button>
            <button @click="toggleAllDropdowns"
              class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700">
              <Icon :icon="isExpandAll ? 'mingcute:up-fill' : 'mingcute:down-fill'" class="w-5 h-5" />
            </button>
          </div>

          <!-- Hamburger khusus Mobile -->
          <button @click="toggleSidebar" class="sm:hidden p-2">
            <Icon icon="mingcute:menu-fill" class="w-7 h-7 text-gray-700 dark:text-gray-300" />
          </button>
        </div>

        <!-- Search Bar -->
        <div class="flex-1 ml-3 max-w-md">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
              </svg>
            </div>
            <input type="search" readonly @click.stop="openSearchModal"
              class="block w-full pl-10 pr-3 py-2.5 text-sm rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 placeholder-gray-400 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition cursor-pointer"
              placeholder="Pencarian..." />
          </div>
        </div>
      </div>

      <!-- Kanan: Dark Mode + User -->
      <div class="flex items-center gap-2 ml-2">
        <!-- Dark Mode -->
        <div class="flex items-center gap-2 rounded-3xl hover:cursor-pointer border border-gray-100 dark:border-gray-700 p-1">
        <button @click="toggleDarkMode" class="p-1 rounded-full bg-gray-100 dark:bg-gray-700 text-black dark:text-white flex items-center gap-1">
            <Icon :icon="isDarkMode ? 'solar:cloudy-moon-broken' : 'solar:sun-2-broken'" class="w-6 h-6" />
        </button>
        </div>
        <!-- Avatar & User Info -->
        <div @click="showModal" class="flex items-center gap-2 rounded-3xl hover:cursor-pointer border border-gray-100 dark:border-gray-700 p-1">
            <fwb-avatar size='sm' :initials="getInitials($page.props.auth.user.name)" rounded />
            <div v-if="!isMobile" class="flex flex-col items-start pe-4">
                <span class="font-medium text-base text-gray-900 dark:text-gray-100">
                    {{ $page.props.auth.user.name }}
                </span>
                <span class="-mt-1 text-xs text-gray-500">
                    {{ $page.props.auth.user.email }}
                </span>
            </div>
        </div>
      </div>
    </div>
  </nav>
  <!-- Modal -->
    <fwb-modal v-if="isShowModal" @close="closeModal" size="sm" not-escapable :position="[isMobile ? 'center' : 'top-end']">
        <template #header>
            <div class="flex items-center text-md">
                <fwb-avatar size='sm' :initials="getInitials($page.props.auth.user.name)" rounded />
                <div class="flex flex-col items-start ps-2">
                    <span class="font-medium text-base text-gray-900 dark:text-gray-100">
                        {{ $page.props.auth.user.name }}
                    </span>
                    <span class="-mt-1 text-xs text-gray-500">
                        {{ $page.props.auth.user.email }}
                    </span>
                </div>
            </div>
        </template>
        <template #body>
            <div class="flex flex-col space-y-2 -m-2">
                <div v-for="(menu, index) in menus" :key="index" @click="navigateTo(menu.route)" class="cursor-pointer flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group bg-gray-100 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800 dark:bg-gray-600 font-semibold">
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">
                        {{ menu.label }}
                    </span>
                    <Icon class="w-6 h-6 me-1 text-gray-700 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" :icon="menu.icon"></Icon>
                </div>
                <div class="p-4 mt-2 not-prose overflow-auto rounded-lg bg-gray-100 outline outline-white/5 dark:bg-gray-950/50 text-xs">
                    <ul class="capitalize">
                        <li><code>Roles {{ $page.props.auth.role }}</code></li>
                        <li><code>{{ screenSize }}</code></li>
                        <li><code>{{ currentDateTime }}</code></li>
                    </ul>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col space-y-2 -m-2">
                <div @click="logout" class="cursor-pointer flex items-center w-full p-2 text-base font-extrabold text-gray-100 transition duration-75 rounded-lg group bg-red-900 hover:bg-red-800 dark:text-white dark:hover:bg-red-700">
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">
                        Log Out
                    </span>
                    <Icon class="w-6 h-6 me-1 text-gray-100 transition duration-75 group-hover:text-gray-100 dark:text-red-400 dark:group-hover:text-white" icon="solar:power-broken"></Icon>
                </div>
            </div>
        </template>
    </fwb-modal>
    <fwb-modal v-if="isSearchModal" size="4xl" persistent position="center" class="!top-10" @keyup.esc="closeSearchModal">
        <template #header>
            <div class="relative w-full">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="search" class="block w-full px-5 py-2.5 ps-10 text-base text-gray-900 border border-gray-300 focus:border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 dark:text-white" placeholder="Pencarian Klasifikasi dan JRA ..." ref="refSearch" @keyup="fetchData" v-model="searchQuery" />
            </div>
        </template>
        <template #body>
            <div class="min-h-[500px]">
                <div v-if="searchedData.length > 0">
                    <h3 class="font-bold text-sm pb-4 opacity-40">Klasifikasi & JRA</h3>
                    <ul class="space-y-2">
                        <li v-for="(item, index) in searchedData" :key="index">
                            <div @click="goToDetail(item.slug)" class="cursor-pointer inline-flex group w-full bg-gray-50 hover:bg-blue-800 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <div class="relative w-full">
                                    <div class="me-16">
                                        {{ truncateText(item.label) }}
                                        <span class="block text-gray-500 group-hover:text-gray-50">{{ item.kode }}</span>
                                    </div>
                                    <div class="absolute inset-y-0 end-0 flex items-center ps-3 pointer-events-none">
                                        <Icon class="rtl:rotate-180 w-5 h-5 ms-2 text-end" icon="mingcute:right-fill" />
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <p v-else>No results found.</p>
            </div>
        </template>
        <template #footer>
            <div class="w-full text-end py-0">
                <h3 class="font-bold text-sm opacity-40">Supported By @Enterwind</h3>
            </div>
        </template>
    </fwb-modal>
</template>

<style scoped>
/* Smooth di iOS & Android */
input[type="search"]::-webkit-search-cancel-button {
  -webkit-appearance: none;
}
</style>