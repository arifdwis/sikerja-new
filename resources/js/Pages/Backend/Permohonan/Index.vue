<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Sidebar from 'primevue/sidebar';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import CreateForm from './Components/CreateForm.vue';
import TrackingCard from './Components/TrackingCard.vue';
import ControlBar from './Components/ControlBar.vue';
import PermohonanList from './Components/PermohonanList.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import JadwalForm from './Components/JadwalForm.vue';
import DetailModal from './Components/DetailModal.vue';
import FileUploadForm from './Components/FileUploadForm.vue';

const props = defineProps({
    permohonan: Object,
    provinsis: Array,
    pemohon: Object,
    corporate: Object,
    pemohonanList: Array,
    statusLabels: Object,
    kategoris: Array,
    opds: {
        type: Array,
        default: () => []
    },
    userOpd: {
        type: Object,
        default: null
    },
    share: Object,
    filters: Object,
});

const jadwalDialog = ref(false);

const openJadwalForm = (item) => {
    selectedItem.value = item;
    jadwalDialog.value = true;
};

const handleJadwalSuccess = () => {
    jadwalDialog.value = false;
    toast.success('Jadwal berhasil diajukan');
    router.reload({ only: ['permohonan'] });
};

const page = usePage();
const toast = useToast();

const filterQuery = ref(props.filters?.search || '');
const filteredData = computed(() => {
    if (!filterQuery.value) return props.permohonan?.data || [];
    const query = filterQuery.value.toLowerCase();
    return (props.permohonan?.data || []).filter(item =>
        item.label?.toLowerCase().includes(query) ||
        item.kode?.toLowerCase().includes(query) ||
        item.nomor_permohonan?.toLowerCase().includes(query) ||
        item.nama_instansi?.toLowerCase().includes(query)
    );
});

const viewMode = ref(localStorage.getItem('permohonanViewMode') || 'grid');
watch(viewMode, (newVal) => {
    localStorage.setItem('permohonanViewMode', newVal);
});

const groupBy = ref(localStorage.getItem('permohonanGroupBy') || 'latest');
watch(groupBy, (newVal) => {
    localStorage.setItem('permohonanGroupBy', newVal);
});

const createDialog = ref(false);
const editDialog = ref(false);
const trackingSidebar = ref(false);
const detailDialog = ref(false);
const uploadDialog = ref(false);
const statusActionDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);
const pendingStatusAction = ref(null);
const statusReason = ref('');

onMounted(() => {
    if (props.filters?.detail) {
        openDetailModal({ uuid: props.filters.detail });
    }
});

const openCreateModal = () => createDialog.value = true;
const closeCreateModal = () => createDialog.value = false;

const openEditModal = (item) => {
    selectedItem.value = item; // Re-use selectedItem for edit too
    editDialog.value = true;
};
const closeEditModal = () => editDialog.value = false;

const handleCreateSuccess = () => {
    closeCreateModal();
    router.reload({ only: ['permohonan'] });
};

const handleEditSuccess = () => {
    closeEditModal();
    router.reload({ only: ['permohonan'] });
};

const openTrackingSidebar = (item) => {
    selectedItem.value = item;
    trackingSidebar.value = true;
};

const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        // Cache busting + accept JSON header agar respons selalu segar (penting setelah aksi PKS approve / TTD validate)
        const response = await axios.get(route(props.share.prefix + '.show', item.uuid), {
            params: { _: Date.now() },
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        detailData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch details", error);
    } finally {
        loadingDetail.value = false;
    }
    // Pastikan list permohonan di belakang juga ter-refresh sehingga
    // permohonan berpindah dari grup status lama ke status baru.
    router.reload({ only: ['permohonan'] });
};

const openUploadDialog = async (item) => {
    if (item.status == 0) {
        toast.error('Mohon tunggu validasi admin sebelum mengupload berkas.');
        return;
    }
    if (item.files && item.files.length > 0) {
        toast.error('Berkas sudah diupload. Upload ulang hanya tersedia saat dokumen diminta revisi.');
        return;
    }

    try {
        const response = await axios.get(route(props.share.prefix + '.show', item.uuid));
        const latestItem = response.data;

        if (latestItem.files?.length > 0) {
            toast.error('Berkas sudah diupload. Tunggu review atau upload perbaikan dari dokumen yang diminta revisi.');
            router.reload({ only: ['permohonan'] });
            return;
        }

        selectedItem.value = latestItem;
        uploadDialog.value = true;
    } catch (error) {
        toast.error('Gagal memeriksa berkas terbaru. Silakan coba lagi.');
    }
};

const handleUploadSuccess = () => {
    uploadDialog.value = false;
    toast.success('Dokumen berhasil diupload!');
    router.reload({ only: ['permohonan'] });
};

const handleDetailUploadSuccess = () => {
    detailDialog.value = false;
    router.reload({ only: ['permohonan'] });
};

const goToDetail = (item) => {
    openDetailModal(item);
};

const updateExecutionStatus = (nextStatus) => {
    if (!detailData.value?.uuid) return;

    if (nextStatus === 7) {
        if (!confirm('Tandai kerjasama ini sebagai selesai?')) return;
        router.post(route('permohonan.status', detailData.value.uuid), {
            status: 7,
            keterangan: 'Kerjasama ditutup oleh admin pada tahap pelaksanaan.',
        }, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Kerjasama berhasil ditandai selesai.');
                openDetailModal({ uuid: detailData.value.uuid });
            },
        });
        return;
    }

    pendingStatusAction.value = 8;
    statusReason.value = '';
    statusActionDialog.value = true;
};

const submitStatusAction = () => {
    if (!detailData.value?.uuid || pendingStatusAction.value !== 8) return;
    if (!statusReason.value.trim()) {
        toast.error('Alasan pencabutan wajib diisi.');
        return;
    }

    router.post(route('permohonan.status', detailData.value.uuid), {
        status: 8,
        keterangan: statusReason.value.trim(),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Kerjasama berhasil dicabut.');
            statusActionDialog.value = false;
            openDetailModal({ uuid: detailData.value.uuid });
        },
    });
};

const isAdmin = computed(() => {
    // HandleInertiaRequests menyediakan auth.role (slug terakhir) dan auth.roles (array slug)
    const role = page.props.auth?.role;
    const roles = page.props.auth?.roles || [];
    const adminSlugs = ['administrator', 'admin', 'superadmin', 'super-admin', 'verifikator', 'tkksd'];
    return adminSlugs.includes(role) || roles.some((r) => adminSlugs.includes(r));
});

const canCreatePermohonan = computed(() => {
    const role = page.props.auth?.role;
    const roles = page.props.auth?.roles || [];
    const blocked = ['administrator', 'admin', 'superadmin'];
    return !(blocked.includes(role) || roles.some((r) => blocked.includes(r)));
});

const groupedData = computed(() => {
    const data = filteredData.value;
    if (groupBy.value === 'latest') {
        return { "Semua Permohonan (Terbaru)": data };
    } else if (groupBy.value === 'kategori') {
        return data.reduce((acc, item) => {
            const key = item.kategori?.label || "Tanpa Kategori";
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    } else if (groupBy.value === 'status') {
        return data.reduce((acc, item) => {
            const key = item.status_label?.label || "Unknown";
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    }
    return { "Semua Permohonan": data };
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

                <ControlBar 
                    v-model:filterQuery="filterQuery" 
                    v-model:viewMode="viewMode" 
                    v-model:groupBy="groupBy" 
                    :showCreate="canCreatePermohonan"
                    @create="openCreateModal"
                />

                <PermohonanList 
                    :groupedData="groupedData"
                    :viewMode="viewMode"
                    :isAdmin="isAdmin"
                    :hasData="filteredData.length > 0"
                    :showCreate="canCreatePermohonan"
                    @create="openCreateModal"
                    @detail="goToDetail"
                    @edit="openEditModal"
                    @schedule="openJadwalForm"
                    @tracking="openTrackingSidebar"
                    @upload="openUploadDialog"
                />
            </div>
        </section>

        <Dialog v-model:visible="createDialog" modal header="Buat Permohonan Kerjasama" :style="{ width: '1000px' }" :breakpoints="{ '960px': '95vw' }" class="p-0 overflow-hidden">
            <div class="h-[80vh] flex flex-col">
                <CreateForm 
                    :kategoris="props.kategoris || []" 
                    :provinsis="props.provinsis" 
                    :opds="props.opds || []"
                    :userOpd="props.userOpd"
                    :pemohon="props.pemohon"
                    :corporate="props.corporate"
                    :pemohonanList="props.pemohonanList"
                    @success="handleCreateSuccess" 
                />
            </div>
        </Dialog>

        <Dialog v-model:visible="editDialog" modal header="Edit Permohonan Kerjasama" :style="{ width: '1000px' }" :breakpoints="{ '960px': '95vw' }" class="p-0 overflow-hidden">
            <div class="h-[80vh] flex flex-col">
                <CreateForm 
                    v-if="selectedItem"
                    mode="edit"
                    :initialData="selectedItem"
                    :kategoris="props.kategoris || []" 
                    :provinsis="props.provinsis" 
                    :opds="props.opds || []"
                    :userOpd="props.userOpd"
                    :pemohon="props.pemohon"
                    :corporate="props.corporate"
                    :pemohonanList="props.pemohonanList"
                    @success="handleEditSuccess" 
                />
            </div>
        </Dialog>

        <Dialog v-model:visible="uploadDialog" modal header="Upload Dokumen Kerjasama" :style="{ width: '700px' }" :breakpoints="{ '960px': '95vw' }">
            <FileUploadForm 
                v-if="selectedItem"
                :permohonan="selectedItem"
                @close="uploadDialog = false"
                @success="handleUploadSuccess"
            />
        </Dialog>

        <Dialog
            v-model:visible="statusActionDialog"
            modal
            header="Cabut Kerjasama"
            :style="{ width: '520px' }"
            :breakpoints="{ '640px': '95vw' }"
        >
            <div class="space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">Masukkan alasan pencabutan kerjasama.</p>
                <textarea
                    v-model="statusReason"
                    rows="4"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Contoh: Salah satu pihak melanggar ketentuan pada pasal ..."
                />
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="statusActionDialog = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                        @click="submitStatusAction"
                    >
                        Simpan Pencabutan
                    </button>
                </div>
            </template>
        </Dialog>

        <Dialog v-model:visible="jadwalDialog" modal header="Pengajuan Jadwal" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <JadwalForm :permohonan="selectedItem" @close="jadwalDialog = false" @submitted="handleJadwalSuccess" />
        </Dialog>

        <Sidebar v-model:visible="trackingSidebar" position="left" class="w-full md:w-[480px]">
            <template #header>
                <div class="flex items-center gap-3 font-bold text-xl text-gray-800 dark:text-gray-200">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600">
                         <Icon icon="solar:history-bold" />
                    </div>
                    Lacak Permohonan
                </div>
            </template>
            <TrackingCard v-if="selectedItem" :permohonan="selectedItem" />
        </Sidebar>

        <DetailModal 
            :visible="detailDialog" 
            @update:visible="detailDialog = $event"
            :loading="loadingDetail"
            :data="detailData"
            :isAdmin="isAdmin"
            @refresh="openDetailModal(detailData)" 
            @upload-complete="handleDetailUploadSuccess"
        >
            <template #footer="{ data }">
                <div
                    v-if="isAdmin && Number(data?.status) === 6"
                    class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                        @click="updateExecutionStatus(8)"
                    >
                        Cabut Kerjasama
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        @click="updateExecutionStatus(7)"
                    >
                        Tandai Selesai
                    </button>
                </div>
            </template>
        </DetailModal>
    </AuthenticatedLayout>
</template>
