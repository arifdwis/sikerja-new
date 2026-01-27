<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Sidebar from 'primevue/sidebar';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import axios from 'axios';
import DetailModal from '../Permohonan/Components/DetailModal.vue';

const props = defineProps({
    datas: Object, // Paginator from controller
    share: Object,
    filters: Object,
});

const toast = useToast();

// Pagination state (Inertia handles this via Paginator, but we need search ref)
const filterQuery = ref(props.filters?.search || '');

// Search Debounce
let searchTimeout;
watch(filterQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route(props.share.prefix + '.index'), { search: val }, { preserveState: true, preserveScroll: true });
    }, 500);
});

// View mode: grid/list (persist in localStorage)
const viewMode = ref(localStorage.getItem('riwayatViewMode') || 'grid');
watch(viewMode, (newVal) => {
    localStorage.setItem('riwayatViewMode', newVal);
});

// Detail Logic
const detailDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);

const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        const response = await axios.get(route('permohonan.show', item.uuid));
        detailData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch details", error);
        toast.error("Gagal memuat detail data");
    } finally {
        loadingDetail.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};

const diffForHumans = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffDays = Math.floor(diffMs / 86400000);
    return `${diffDays} hari yang lalu`;
};

const getStatusColor = (status) => {
    // Should typically be green since this is Riwayat (Selesai), but fallback for others
    const colors = {
        4: { bg: 'bg-green-400', text: 'text-green-950', border: 'border-green-400' },
        9: { bg: 'bg-red-400', text: 'text-red-950', border: 'border-red-400' },
    };
    return colors[status] || { bg: 'bg-gray-400', text: 'text-gray-950', border: 'border-gray-400' };
};

const onPageChange = (url) => {
    if (url) router.visit(url);
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ share.title }}
            </h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" :crumbs="[{ label: 'Dashboard', route: 'dashboard' }, { label: share.title, route: null }]" />

                <!-- Control Bar -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Search -->
                        <div class="relative w-full sm:w-80">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari riwayat kerjasama..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700" />
                        </div>
                        
                        <!-- View Switcher -->
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white dark:bg-gray-800 shadow-sm text-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:layout-grid" class="w-4 h-4" />
                                </button>
                                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white dark:bg-gray-800 shadow-sm text-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:list" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:history-bold-duotone" class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak ada Riwayat</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Anda belum memiliki riwayat kerjasama yang telah selesai.</p>
                </div>

                <!-- Content -->
                <div v-else class="mb-10">
                    <!-- Grid View -->
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="item in datas.data" :key="item.id" 
                            class="group relative rounded-lg border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden flex flex-col h-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700"
                        >
                            <div class="p-4 flex flex-col h-full relative z-10">
                                <!-- Tag Selesai -->
                                <div class="flex justify-between items-center mb-3">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 text-xs font-bold rounded border border-blue-200 uppercase tracking-wide">
                                        {{ item.kategori?.label || 'Kerjasama' }}
                                    </span>
                                    <span class="bg-green-100 text-green-700 px-2 py-1 text-xs font-bold rounded border border-green-200 uppercase tracking-wide flex items-center gap-1">
                                        <Icon icon="solar:check-circle-bold" /> SELESAI
                                    </span>
                                </div>

                                <!-- Kode & Date -->
                                <div class="mb-2 text-xs text-gray-400 font-mono">
                                    {{ item.nomor_permohonan || item.kode }} • {{ formatDate(item.created_at) }}
                                </div>

                                <!-- Judul -->
                                <h4 @click="openDetailModal(item)" class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-blue-600 transition-colors">
                                    {{ item.label }}
                                </h4>

                                <!-- Instansi Metadata -->
                                <div class="text-sm text-gray-500 mb-4 flex items-center gap-2">
                                     <Icon icon="solar:buildings-bold" class="w-4 h-4" /> {{ item.nama_instansi }}
                                </div>

                                <!-- Footer -->
                                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                    <span class="text-xs text-gray-400">{{ diffForHumans(item.created_at) }}</span>
                                    <button @click="openDetailModal(item)" class="text-xs font-bold text-blue-600 hover:text-blue-800 uppercase flex items-center gap-1">
                                        Lihat Detail <Icon icon="solar:arrow-right-linear" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div v-else class="space-y-3">
                        <div v-for="item in datas.data" :key="item.id" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center gap-4 group">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-bold text-sm font-mono text-gray-500">{{ item.nomor_permohonan || item.kode }}</span>
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 text-[10px] font-bold rounded border border-green-200 uppercase">SELESAI</span>
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-semibold text-gray-950 dark:text-white hover:text-blue-600 cursor-pointer transition-colors capitalize text-lg">
                                    {{ item.label }}
                                </h4>
                                <div class="text-sm text-gray-500 mt-1 flex gap-2 items-center">
                                     <span>{{ item.nama_instansi }}</span> 
                                     <span class="text-gray-300">•</span>
                                     <span>{{ formatDate(item.created_at) }}</span>
                                </div>
                            </div>
                            <div>
                                <button @click="openDetailModal(item)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg text-sm font-medium transition-colors">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center" v-if="datas.links.length > 3">
                        <div class="flex gap-1">
                            <component 
                                :is="link.url ? Link : 'span'" 
                                v-for="(link, i) in datas.links" 
                                :key="i"
                                :href="link.url" 
                                v-html="link.label"
                                class="px-3 py-1 rounded text-sm"
                                :class="{
                                    'bg-blue-600 text-white': link.active,
                                    'bg-white border text-gray-600 hover:bg-gray-50': !link.active && link.url,
                                    'text-gray-400': !link.url
                                }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <DetailModal 
            v-model:visible="detailDialog" 
            :loading="loadingDetail" 
            :data="detailData" 
            :isAdmin="false"
        />
    </AuthenticatedLayout>
</template>
