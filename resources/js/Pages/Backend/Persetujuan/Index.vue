<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import GridItem from '../Pembahasan/Components/PembahasanGridItem.vue';
import ListItem from '../Permohonan/Components/ListItem.vue';
import ApprovalModal from './Components/ApprovalModal.vue';
import ActionDialogs from './Components/ActionDialogs.vue';
import axios from 'axios';

const props = defineProps({
    datas: Object,
    kategoris: Array,
    share: Object,
    filters: Object,
});

const toast = useToast();

// Pagination & Search
const filterQuery = ref(props.filters?.search || '');
const selectedKategori = ref(props.filters?.kategori || null);
let searchTimeout;

const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: { 
            search: filterQuery.value,
            kategori: selectedKategori.value
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas']
    });
};

watch([filterQuery, selectedKategori], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// View mode
const viewMode = ref(localStorage.getItem('persetujuanViewMode') || 'grid');
watch(viewMode, (newVal) => localStorage.setItem('persetujuanViewMode', newVal));

// Grouped data (like Pembahasan)
const groupedData = computed(() => {
    const data = props.datas?.data || [];
    return { 'Daftar Persetujuan': data };
});

// States
const detailDialog = ref(false);
const confirmDialog = ref(false);
const rejectDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);
const processing = ref(false);
const rejectReason = ref('');

// Actions
const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        const response = await axios.get(route('permohonan.show', item.uuid));
        detailData.value = response.data;
    } catch (error) {
        toast.error("Gagal memuat detail data");
    } finally {
        loadingDetail.value = false;
    }
};

const openConfirmDialog = (item) => {
    selectedItem.value = item;
    confirmDialog.value = true;
};

const openRejectDialog = (item) => {
    selectedItem.value = item;
    rejectReason.value = '';
    rejectDialog.value = true;
};

const submitRejection = () => {
    if (!rejectReason.value) return toast.error('Mohon isi alasan penolakan');
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 9, admin_comment: rejectReason.value
    }, {
        onSuccess: () => {
            rejectDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Permohonan berhasil ditolak');
        },
        onError: () => processing.value = false
    });
};

const submitPersetujuan = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 1
    }, {
        onSuccess: () => {
            confirmDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Permohonan berhasil disetujui');
        },
        onError: () => processing.value = false
    });
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
                <Breadcrumb class="mb-6" :crumbs="[{ label: 'Dashboard', route: 'dashboard' }, { label: share.title, route: null }]" />

                <!-- Control Bar -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <div class="relative w-full sm:w-80">
                                <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="filterQuery" type="text" placeholder="Cari permohonan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-indigo-500 rounded-lg text-sm dark:bg-gray-700" />
                            </div>
                            <Dropdown v-model="selectedKategori" :options="kategoris" optionLabel="label" optionValue="id" placeholder="Semua Kategori" class="w-full sm:w-48" showClear />
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:layout-grid" class="w-4 h-4" />
                                </button>
                                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:list" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                     <div class="w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:calendar-date-bold-duotone" class="w-12 h-12 text-indigo-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak ada Persetujuan</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Tidak ada jadwal yang menunggu persetujuan saat ini.</p>
                </div>

                <!-- Content (reusing Pembahasan layout) -->
                <div v-else v-for="(items, groupTitle) in groupedData" :key="groupTitle" class="mb-10">
                    <div class="flex justify-between gap-2 mb-4">
                        <div class="border-l-4 border-blue-600 pl-2">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 uppercase">{{ groupTitle }}</h3>
                        </div>
                        <span class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ items.length }} Permohonan</span>
                    </div>

                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <GridItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="openDetailModal($event)"
                        />
                    </div>

                    <div v-else class="space-y-3">
                        <ListItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="openDetailModal($event)"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Components -->
        <ApprovalModal 
            v-model:visible="detailDialog"
            :loading="loadingDetail"
            :data="detailData"
            @reject="openRejectDialog"
            @approve="openConfirmDialog"
        />

        <ActionDialogs 
            v-model:confirmVisible="confirmDialog"
            v-model:rejectVisible="rejectDialog"
            v-model:rejectReason="rejectReason"
            :item="selectedItem"
            :processing="processing"
            @confirm="submitPersetujuan"
            @reject="submitRejection"
        />
    </AuthenticatedLayout>
</template>
