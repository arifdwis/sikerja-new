<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import ReviewModal from './Components/ReviewModal.vue';
import ControlBar from '../Permohonan/Components/ControlBar.vue';
import PembahasanList from './Components/PembahasanList.vue';
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
    // Strict Admin check: Only actual Administrators can finish the discussion
    const adminRoleSlugs = ['admin', 'administrator', 'superadmin', 'super-admin'];
    return roles.some(r => adminRoleSlugs.includes(r?.toLowerCase()));
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
const groupBy = ref(localStorage.getItem('pembahasanGroupBy') || 'latest');
watch(groupBy, (newVal) => localStorage.setItem('pembahasanGroupBy', newVal));

// Filter & Grouping Logic
const filteredData = computed(() => {
    if (!filterQuery.value) return props.datas?.data || [];
    const query = filterQuery.value.toLowerCase();
    return (props.datas?.data || []).filter(item =>
        item.label?.toLowerCase().includes(query) ||
        item.kode?.toLowerCase().includes(query) ||
        item.nomor_permohonan?.toLowerCase().includes(query) ||
        item.nama_instansi?.toLowerCase().includes(query)
    );
});

const groupedData = computed(() => {
    const data = filteredData.value;
    if (groupBy.value === 'latest') {
        const title = props.share.title?.includes("Riwayat") ? "Riwayat Pembahasan" : "Daftar Pembahasan";
        return { [title]: data };
    } else if (groupBy.value === 'kategori') {
        return data.reduce((acc, item) => {
            const key = item.kategori?.label || "Tanpa Kategori";
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    } else if (groupBy.value === 'status') {
         // Group by Contribution Status instead of ID status (since all are status 1)
        return data.reduce((acc, item) => {
            const key = item.contributed ? "Sudah Dibahas" : "Perlu Masukan";
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    }
    return { "Semua Pembahasan": data };
});

// States

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
    }
};

const allFilesApproved = computed(() => {
    const files = detailData.value?.files || [];
    return files.length > 0 && files.every(f => f.status == 1);
});

const openConfirmDialog = (item) => {
    if (!allFilesApproved.value) {
        // Warning but allow proceed for Admin
        toast.info('Catatan: Belum semua dokumen disetujui. Melanjutkan sebagai Administrator.');
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
                <Breadcrumb class="mb-6" />

                <ControlBar 
                    v-model:filterQuery="filterQuery" 
                    v-model:viewMode="viewMode" 
                    v-model:groupBy="groupBy" 
                    @create="() => {}"
                    :showCreate="false"
                />

                <PembahasanList 
                    :groupedData="groupedData"
                    :viewMode="viewMode"
                    :isAdmin="isUserAdmin"
                    :hasData="filteredData.length > 0"
                    @detail="openDetailModal"
                />
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
                <Icon v-if="allFilesApproved" icon="solar:check-circle-bold-duotone" class="w-16 h-16 text-green-500 mx-auto mb-4" />
                <Icon v-else icon="solar:danger-triangle-bold-duotone" class="w-16 h-16 text-orange-500 mx-auto mb-4" />
                
                <p class="mb-2 font-semibold">Selesaikan Pembahasan?</p>
                <p class="mb-4 text-sm text-gray-600">
                    <span v-if="!allFilesApproved" class="text-orange-600 block mb-1">⚠️ Beberapa dokumen belum disetujui.</span>
                    Lanjutkan ke tahap <strong>Penjadwalan</strong>?
                </p>
                <div class="font-semibold text-gray-700 mb-6 bg-gray-50 py-2 rounded border border-gray-200">
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
