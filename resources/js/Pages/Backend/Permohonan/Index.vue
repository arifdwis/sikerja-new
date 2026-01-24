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
import GridItem from './Components/GridItem.vue';
import ListItem from './Components/ListItem.vue';
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

const handleCreateSuccess = () => {
    closeCreateModal();
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
    return ['admin', 'super-admin', 'verifikator', 'tkksd'].includes(userRole);
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

                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                            <div class="relative w-full sm:w-80">
                                <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="filterQuery" type="text" placeholder="Cari kode / label permohonan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700" />
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">Group By:</span>
                                <button @click="groupBy = 'latest'" :class="groupBy === 'latest' ? 'bg-blue-600 border-blue-500 text-blue-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600'" class="px-3 py-1.5 rounded-md text-sm font-medium border transition-colors">
                                    Terbaru
                                </button>
                                <button @click="groupBy = 'kategori'" :class="groupBy === 'kategori' ? 'bg-blue-600 border-blue-500 text-blue-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600'" class="px-3 py-1.5 rounded-md text-sm font-medium border transition-colors">
                                    Kategori
                                </button>
                                <button @click="groupBy = 'status'" :class="groupBy === 'status' ? 'bg-blue-600 border-blue-500 text-blue-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600'" class="px-3 py-1.5 rounded-md text-sm font-medium border transition-colors">
                                    Status
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition-colors">
                                <Icon icon="lucide:plus" class="w-4 h-4" />
                                <span>Pengajuan Baru</span>
                            </button>
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
                        <Icon icon="solar:documents-minimalistic-bold-duotone" class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Belum ada Permohonan</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-8">Anda belum memiliki riwayat permohonan kerjasama. Buat permohonan baru untuk memulai kerjasama.</p>
                    <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                        <Icon icon="lucide:plus" class="w-4 h-4" />
                        Buat Permohonan Baru
                    </button>
                </div>

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
                            :isAdmin="isAdmin"
                            @detail="goToDetail"
                            @schedule="openJadwalForm"
                            @tracking="openTrackingSidebar"
                            @upload="openUploadDialog"
                        />
                    </div>

                    <div v-else class="space-y-3">
                        <ListItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="goToDetail"
                            @tracking="openTrackingSidebar"
                        />
                    </div>
                </div>
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
