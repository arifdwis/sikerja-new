<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Paginator from 'primevue/paginator';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import FileDiskusiSection from '../Permohonan/Components/FileDiskusiSection.vue';
import ReviewModal from './Components/ReviewModal.vue';
import axios from 'axios';

const props = defineProps({
    datas: Object,
    share: Object,
    filters: Object,
});

const toast = useToast();
const page = usePage();

// Auth Check
const isUserAdmin = computed(() => {
    const roles = page.props.auth?.roles || [];
    const permissions = page.props.auth?.permissions || [];
    const adminRoleSlugs = ['admin', 'administrator', 'superadmin', 'super-admin'];
    return roles.some(r => adminRoleSlugs.includes(r?.toLowerCase())) || permissions.includes('permohonan.menu.pembahasan.lanjut');
});

// Pagination & Search
const filterQuery = ref(props.filters?.search || '');
let searchTimeout;
watch(filterQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route(props.share.prefix + '.index'), {
            data: { search: val },
            preserveState: true,
            preserveScroll: true,
            only: ['datas']
        });
    }, 500);
});

// View mode
const viewMode = ref(localStorage.getItem('pembahasanViewMode') || 'grid');
watch(viewMode, (newVal) => localStorage.setItem('pembahasanViewMode', newVal));

// States
const detailDialog = ref(false);
const confirmDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);
const processing = ref(false);
const selectedFile = ref(null);

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

// Actions
const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    selectedFile.value = null;
    try {
        const response = await axios.get(route('permohonan.show', item.uuid));
        detailData.value = response.data;
        if (detailData.value.files?.length > 0) {
            selectedFile.value = detailData.value.files[0];
        }
    } catch (error) {
        toast.error("Gagal memuat detail data");
    } finally {
        loadingDetail.value = false;
    }
};

const selectFile = (file) => {
    selectedFile.value = file;
};

const handleFileStatusUpdate = (updatedFile) => {
    if (detailData.value?.files) {
        const index = detailData.value.files.findIndex(f => f.id === updatedFile.id);
        if (index !== -1) {
            detailData.value.files[index] = updatedFile;
        }
        checkAllFilesApproved();
    }
};

const checkAllFilesApproved = () => {
    if (detailData.value?.files) {
        detailData.value.all_files_approved = detailData.value.files.length > 0 && detailData.value.files.every(f => f.status === 1);
    }
};

const allFilesApproved = computed(() => detailData.value?.all_files_approved || false);

const openConfirmDialog = (item) => {
    if (!allFilesApproved.value) {
        toast.warning('Semua dokumen harus disetujui terlebih dahulu.');
        return;
    }
    selectedItem.value = item;
    confirmDialog.value = true;
};

const submitPembahasan = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 2
    }, {
        onSuccess: () => {
            confirmDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Pembahasan selesai, dilanjutkan ke Penjadwalan');
        },
        onError: (errors) => {
            processing.value = false;
            toast.error(errors.error || 'Gagal memproses pembahasan');
        }
    });
};

const getFileStatusClass = (file) => {
    if (file.status === 1) return 'border-green-500 bg-green-50 dark:bg-green-900/20';
    if (file.status === 2) return 'border-red-500 bg-red-50 dark:bg-red-900/20';
    return 'border-gray-200 bg-white dark:bg-gray-800';
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
                        <div class="relative w-full sm:w-80">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari pembahasan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-blue-500 rounded-lg text-sm dark:bg-gray-700" />
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:layout-grid" class="w-4 h-4" />
                                </button>
                                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:list" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                     <div class="w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:chat-round-line-bold-duotone" class="w-12 h-12 text-blue-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak ada Pembahasan</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Tidak ada permohonan yang sedang dalam tahap pembahasan saat ini.</p>
                </div>

                <!-- Content -->
                <div v-else class="mb-10">
                    <!-- Grid View -->
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="item in datas.data" :key="item.id" 
                            class="group relative rounded-lg border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 flex flex-col h-full"
                        >
                            <div class="p-5 flex flex-col h-full relative z-10">
                                <div class="flex justify-between items-center mb-3 gap-2">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 text-xs font-bold rounded border border-blue-200 uppercase tracking-wide">
                                        {{ item.kategori?.label || 'Kerjasama' }}
                                    </span>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <span class="bg-indigo-100 text-indigo-700 px-2 py-1 text-xs font-bold rounded border border-indigo-200 uppercase tracking-wide flex items-center gap-1 w-fit">
                                            <Icon icon="solar:chat-round-dots-bold" /> PEMBAHASAN
                                        </span>
                                        <!-- Visual Badge for Contribution -->
                                        <span v-if="item.contributed" class="bg-green-100 text-green-700 px-2 py-1 text-xs font-bold rounded border border-green-200 uppercase tracking-wide flex items-center gap-1 w-fit">
                                            <Icon icon="solar:check-read-bold" /> SUDAH ADA MASUKAN
                                        </span>
                                        <span v-else class="bg-orange-100 text-orange-700 px-2 py-1 text-xs font-bold rounded border border-orange-200 uppercase tracking-wide flex items-center gap-1 w-fit animate-pulse">
                                            <Icon icon="solar:bell-bold" /> PERLU MASUKAN
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-2 text-xs text-gray-400 font-mono">
                                    {{ item.nomor_permohonan || item.kode }} • {{ formatDate(item.updated_at) }}
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-blue-600 transition-colors cursor-pointer">
                                    {{ item.label }}
                                </h4>
                                <div class="text-sm text-gray-500 mb-4 flex items-center gap-2">
                                     <Icon icon="solar:buildings-bold" class="w-4 h-4" /> {{ item.nama_instansi }}
                                </div>
                                
                                <div class="mt-auto border-t border-gray-100 dark:border-gray-700 pt-4">
                                     <button @click="openDetailModal(item)" class="w-full py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2">
                                        <Icon icon="solar:chat-square-call-bold" />
                                        Review & Diskusi
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
                                    <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 text-[10px] font-bold rounded border border-indigo-200 uppercase">PEMBAHASAN</span>
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-semibold text-gray-950 dark:text-white hover:text-blue-600 cursor-pointer transition-colors capitalize text-lg">
                                    {{ item.label }}
                                </h4>
                                <div class="text-sm text-gray-500 mt-1 flex gap-2 items-center">
                                     <span>{{ item.nama_instansi }}</span> 
                                     <span class="text-gray-300">•</span>
                                     <span>{{ formatDate(item.updated_at) }}</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button icon="pi pi-comments" severity="info" label="Diskusi" @click="openDetailModal(item)" />
                            </div>
                         </div>
                    </div>

                     <!-- Paginator -->
                     <div class="mt-4">
                        <Paginator :totalRecords="datas?.total || 0" :rows="10" @page="params => router.get(datas.next_page_url)" template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Use Detail Modal with File Discussion -->
        <ReviewModal 
            v-model:visible="detailDialog"
            :loading="loadingDetail"
            :data="detailData"
            :isUserAdmin="isUserAdmin"
            :selectedFile="selectedFile"
            @select-file="selectFile"
            @confirm-finish="openConfirmDialog"
            @status-update="handleFileStatusUpdate"
        />

        <!-- Confirm Dialog -->
        <Dialog v-model:visible="confirmDialog" modal header="Konfirmasi Selesai Pembahasan" :style="{ width: '450px' }">
            <div class="text-center p-4">
                <Icon icon="solar:check-circle-bold-duotone" class="w-16 h-16 text-green-500 mx-auto mb-4" />
                <p class="mb-4">Semua dokumen sudah disetujui. Lanjutkan ke tahap <strong>Penjadwalan</strong>?</p>
                <div class="font-semibold text-gray-700 mb-6">
                    {{ selectedItem?.nomor_permohonan || selectedItem?.kode }}
                </div>
                <div class="flex justify-center gap-2">
                    <Button label="Batal" icon="pi pi-times" text @click="confirmDialog = false" />
                    <Button label="Ya, Lanjutkan" icon="pi pi-check" severity="success" @click="submitPembahasan" :loading="processing" />
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
