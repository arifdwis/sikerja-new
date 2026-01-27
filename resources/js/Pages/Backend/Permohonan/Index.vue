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
import FileDiskusiSection from './Components/FileDiskusiSection.vue';
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
const fileDiskusiDialog = ref(false);
const selectedItem = ref(null);
const selectedFile = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);

const openFileDiskusi = (file) => {
    selectedFile.value = file;
    fileDiskusiDialog.value = true;
};

const handleFileStatusUpdate = (updatedFile) => {
    if (detailData.value?.files) {
        const index = detailData.value.files.findIndex(f => f.id === updatedFile.id);
        if (index !== -1) {
            detailData.value.files[index] = updatedFile;
        }
    }
};

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
        const response = await axios.get(route(props.share.prefix + '.show', item.uuid));
        detailData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch details", error);
    } finally {
        loadingDetail.value = false;
    }
};

const openUploadDialog = (item) => {
    if (item.status == 0) {
        toast.error('Mohon tunggu validasi admin sebelum mengupload berkas.');
        return;
    }
    selectedItem.value = item;
    uploadDialog.value = true;
};

const handleUploadSuccess = () => {
    uploadDialog.value = false;
    toast.success('Dokumen berhasil diupload!');
    router.reload({ only: ['permohonan'] });
};

const goToDetail = (item) => {
    openDetailModal(item);
};

const isAdmin = computed(() => {
    const userRole = page.props.auth?.user?.roles?.[0]?.name;
    // Update role names to match DB (administrator, superadmin) + keep compatibility
    return ['administrator', 'admin', 'superadmin', 'super-admin', 'verifikator', 'tkksd'].includes(userRole);
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
                    @create="openCreateModal"
                />

                <PermohonanList 
                    :groupedData="groupedData"
                    :viewMode="viewMode"
                    :isAdmin="isAdmin"
                    :hasData="filteredData.length > 0"
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
            @open-file-diskusi="openFileDiskusi"
            @refresh="openDetailModal(detailData)" 
        />

        <Dialog v-model:visible="fileDiskusiDialog" modal header="Diskusi Dokumen" :style="{ width: '600px' }" :breakpoints="{ '960px': '95vw' }">
            <FileDiskusiSection 
                v-if="selectedFile"
                :file="selectedFile"
                :permohonan="detailData"
                :isAdmin="false"
                @statusUpdated="handleFileStatusUpdate"
            />
        </Dialog>
    </AuthenticatedLayout>
</template>
