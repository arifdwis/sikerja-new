<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import GridItem from '@/Pages/Backend/Permohonan/Components/GridItem.vue';
import ListItem from '@/Pages/Backend/Permohonan/Components/ListItem.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DetailModal from '@/Pages/Backend/Permohonan/Components/DetailModal.vue';
import axios from 'axios';

const props = defineProps({
    permohonan: Object,
    statusLabels: Object,
    share: Object,
    filters: Object,
});

const filterQuery = ref(props.filters?.search || '');
const viewMode = ref(localStorage.getItem('permohonanViewMode') || 'grid');

watch(viewMode, (newVal) => {
    localStorage.setItem('permohonanViewMode', newVal);
});

const detailDialog = ref(false);
const detailData = ref(null);
const loadingDetail = ref(false);
const selectedItem = ref(null);

const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        // Use permohonan.show route since Riwayat show might not exist separate
        const response = await axios.get(route('permohonan.show', item.uuid));
        detailData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch details", error);
    } finally {
        loadingDetail.value = false;
    }
};

const goToDetail = (item) => {
    openDetailModal(item);
};

// Simplified filteredData just for search client-side if needed, but we rely on server side usually.
// But GridItem loop needs the data.
const filteredData = computed(() => {
    return props.permohonan?.data || [];
});

const page = usePage();
const isAdmin = computed(() => {
    const userRole = page.props.auth?.user?.roles?.[0]?.name;
    return ['administrator', 'admin', 'superadmin', 'super-admin', 'verifikator', 'tkksd'].includes(userRole);
});
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
                <Breadcrumb class="mb-6" />

                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                     <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1 relative w-full sm:w-80">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari kode / label permohonan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700" />
                        </div>
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

                <div v-if="!filteredData.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:history-bold-duotone" class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Belum ada Riwayat</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Anda belum memiliki riwayat permohonan (Selesai/Ditolak).</p>
                </div>

                <div v-else class="mb-10">
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <GridItem 
                            v-for="item in filteredData" 
                            :key="item.id" 
                            :item="item"
                            :isAdmin="isAdmin"
                            @detail="goToDetail"
                        />
                    </div>

                    <div v-else class="space-y-3">
                        <ListItem 
                            v-for="item in filteredData" 
                            :key="item.id" 
                            :item="item"
                            @detail="goToDetail"
                        />
                    </div>
                </div>
            </div>
        </section>

        <DetailModal 
            :visible="detailDialog" 
            @update:visible="detailDialog = $event"
            :loading="loadingDetail"
            :data="detailData"
            :isAdmin="isAdmin"
            @refresh="openDetailModal(detailData)" 
        />
    </AuthenticatedLayout>
</template>
