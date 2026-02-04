<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import FileUpload from 'primevue/fileupload';
import Paginator from 'primevue/paginator';

const props = defineProps({
    datas: Object,
    pendingPermohonans: Array,
    share: Object,
    filters: Object,
    isAdmin: Boolean,
});

// Filter/Search
const searchQuery = ref(props.filters?.search || '');
const currentPage = ref(props.datas?.current_page || 1);
const perPage = ref(props.datas?.per_page || 10);
const rowsPerPageOptions = [10, 25, 50];

let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        applyFilters();
    }, 500);
});

const applyFilters = () => {
    router.visit(route('monev.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas']
    });
};

const onPageChange = (e) => {
    perPage.value = e.rows;
    currentPage.value = e.page + 1;
    applyFilters();
};

// Modal Form
const formDialog = ref(false);
const selectedPermohonan = ref(null);

const form = useForm({
    id_permohonan: null,
    tanggal_evaluasi: new Date(),
    kesesuaian_tujuan: null,
    ketepatan_waktu: null,
    kontribusi_mitra: null,
    tingkat_koordinasi: null,
    capaian_indikator: null,
    dampak_pelaksanaan: null,
    inovasi_manfaat: null,
    kelengkapan_dokumen: null,
    pelaporan_berkala: null,
    kendala_administrasi: '',
    relevansi_kebutuhan: null,
    rekomendasi_lanjutan: null,
    saran_rekomendasi: '',
    file_bukti: null,
});

const options = {
    kesesuaian_tujuan: ['Ya seluruhnya', 'Sebagian', 'Tidak'],
    ketepatan_waktu: ['Tepat waktu', 'Terlambat', 'Tidak terlaksana'],
    kontribusi_mitra: ['Ya sepenuhnya', 'Sebagian', 'Tidak'],
    tingkat_koordinasi: ['Sangat baik', 'Baik', 'Cukup', 'Kurang'],
    capaian_indikator: ['Tercapai seluruhnya', 'Sebagian', 'Tidak'],
    dampak_pelaksanaan: ['Sangat berdampak', 'Cukup', 'Kurang'],
    inovasi_manfaat: ['Ya signifikan', 'Ada', 'Tidak'],
    kelengkapan_dokumen: ['Lengkap', 'Sebagian', 'Tidak'],
    pelaporan_berkala: ['Rutin', 'Kadang', 'Tidak'],
    relevansi_kebutuhan: ['Sangat relevan', 'Cukup', 'Tidak'],
    rekomendasi_lanjutan: ['Dilanjutkan', 'Diperluas', 'Dihentikan'],
};

const questions = [
    { key: 'kesesuaian_tujuan', label: 'Kesesuaian dengan tujuan', section: 'Pelaksanaan' },
    { key: 'ketepatan_waktu', label: 'Ketepatan waktu', section: 'Pelaksanaan' },
    { key: 'kontribusi_mitra', label: 'Kontribusi mitra', section: 'Pelaksanaan' },
    { key: 'tingkat_koordinasi', label: 'Tingkat koordinasi', section: 'Pelaksanaan' },
    { key: 'capaian_indikator', label: 'Capaian indikator', section: 'Capaian' },
    { key: 'dampak_pelaksanaan', label: 'Dampak pelaksanaan', section: 'Capaian' },
    { key: 'inovasi_manfaat', label: 'Inovasi & manfaat', section: 'Capaian' },
    { key: 'kelengkapan_dokumen', label: 'Kelengkapan dokumen', section: 'Administrasi' },
    { key: 'pelaporan_berkala', label: 'Pelaporan berkala', section: 'Administrasi' },
    { key: 'relevansi_kebutuhan', label: 'Relevansi kebutuhan', section: 'Rekomendasi' },
    { key: 'rekomendasi_lanjutan', label: 'Rekomendasi lanjutan', section: 'Rekomendasi' },
];

const openFormModal = (permohonan) => {
    selectedPermohonan.value = permohonan;
    form.reset();
    form.id_permohonan = permohonan.id;
    form.tanggal_evaluasi = new Date();
    formDialog.value = true;
};

const handleFileSelect = (event) => {
    form.file_bukti = event.files[0];
};

const submitForm = () => {
    form.post(route('monev.store'), {
        forceFormData: true,
        onSuccess: () => {
            formDialog.value = false;
            form.reset();
        }
    });
};

const getStatusSeverity = (status) => {
    const map = { 0: 'secondary', 1: 'warning', 2: 'success' };
    return map[status] || 'info';
};

const getStatusLabel = (status) => {
    const map = { 0: 'Draft', 1: 'Menunggu Review', 2: 'Sudah Direview' };
    return map[status] || '-';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const viewDetail = (uuid) => {
    router.visit(route('monev.show', uuid));
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
                <Breadcrumb />

                <!-- Admin: Pending Permohonan Section -->
                <div v-if="isAdmin && pendingPermohonans?.length > 0" class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon icon="solar:clipboard-check-bold-duotone" class="w-5 h-5 text-orange-500" />
                        <h3 class="font-semibold text-gray-800 dark:text-white">Kerjasama Perlu Evaluasi</h3>
                        <Tag :value="pendingPermohonans.length" severity="warning" />
                    </div>
                    
                    <DataTable :value="pendingPermohonans" showGridlines stripedRows tableStyle="min-width: 50rem">
                        <Column header="NO" class="w-10 !text-center">
                            <template #body="slotProps">{{ slotProps.index + 1 }}</template>
                        </Column>
                        <Column field="nomor_permohonan" header="KODE">
                            <template #body="{ data }">
                                <span class="font-mono text-xs">{{ data.nomor_permohonan || data.kode }}</span>
                            </template>
                        </Column>
                        <Column field="label" header="JUDUL KERJASAMA" sortable>
                            <template #body="{ data }">
                                <div class="font-medium">{{ data.label || '-' }}</div>
                                <div class="text-xs text-gray-500">{{ data.nama_instansi }}</div>
                            </template>
                        </Column>
                        <Column field="kategori.label" header="KATEGORI">
                            <template #body="{ data }">{{ data.kategori?.label || 'Umum' }}</template>
                        </Column>
                        <Column header="AKSI" class="w-32 !text-center">
                            <template #body="{ data }">
                                <Button label="Isi Monev" icon="pi pi-pencil" severity="warning" size="small" @click="openFormModal(data)" />
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <!-- Riwayat Monev -->
                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex items-center gap-2">
                            <Icon icon="solar:document-text-bold-duotone" class="w-5 h-5 text-blue-500" />
                            <h3 class="font-semibold">Riwayat Monev</h3>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4 items-start sm:items-center">
                            <InputText size="small" v-model="searchQuery" placeholder="Cari..." class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600" />
                            <Paginator 
                                v-if="datas?.total > 10"
                                size="small" 
                                :totalRecords="datas.total" 
                                :rows="perPage" 
                                :first="(currentPage - 1) * perPage"
                                :rowsPerPageOptions="rowsPerPageOptions" 
                                @page="onPageChange" 
                                template="RowsPerPageDropdown PrevPageLink CurrentPageReport NextPageLink"
                                currentPageReportTemplate="{first}-{last} / {totalRecords}" 
                            />
                        </div>
                    </div>

                    <DataTable :value="datas?.data || []" showGridlines stripedRows tableStyle="min-width: 50rem">
                        <template #empty>
                            <div class="text-center py-8 text-gray-500">
                                <Icon icon="solar:clipboard-list-bold-duotone" class="w-12 h-12 mx-auto text-gray-300 mb-2" />
                                <p>Belum ada riwayat Monev</p>
                            </div>
                        </template>
                        <Column header="NO" class="w-10 !text-center">
                            <template #body="slotProps">{{ (currentPage - 1) * perPage + slotProps.index + 1 }}</template>
                        </Column>
                        <Column field="kode_monev" header="KODE MONEV">
                            <template #body="{ data }">
                                <span class="font-mono text-xs">{{ data.kode_monev }}</span>
                            </template>
                        </Column>
                        <Column field="permohonan.label" header="KERJASAMA" sortable>
                            <template #body="{ data }">
                                <div class="font-medium">{{ data.permohonan?.label || '-' }}</div>
                                <div class="text-xs text-gray-500">{{ data.permohonan?.nama_instansi }}</div>
                            </template>
                        </Column>
                        <Column header="TGL EVALUASI" class="w-32">
                            <template #body="{ data }">{{ formatDate(data.tanggal_evaluasi) }}</template>
                        </Column>
                        <Column header="REKOMENDASI" class="w-32">
                            <template #body="{ data }">
                                <Tag v-if="data.rekomendasi_lanjutan" :value="data.rekomendasi_lanjutan" :severity="data.rekomendasi_lanjutan === 'Dilanjutkan' ? 'success' : data.rekomendasi_lanjutan === 'Diperluas' ? 'info' : 'danger'" />
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column header="STATUS" class="w-32">
                            <template #body="{ data }">
                                <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                            </template>
                        </Column>
                        <Column header="AKSI" class="w-20 !text-center">
                            <template #body="{ data }">
                                <Button icon="pi pi-eye" severity="secondary" outlined size="small" @click="viewDetail(data.uuid)" v-tooltip.top="'Lihat Detail'" class="!w-8 !h-8 !p-0" />
                            </template>
                        </Column>
                    </DataTable>

                    <div v-if="datas?.total > 10" class="mt-4">
                        <Paginator 
                            :totalRecords="datas.total" 
                            :rows="perPage" 
                            :first="(currentPage - 1) * perPage"
                            :rowsPerPageOptions="rowsPerPageOptions" 
                            @page="onPageChange" 
                            template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                            currentPageReportTemplate="{first}-{last} dari {totalRecords}" 
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Monev Form Modal -->
        <Dialog v-model:visible="formDialog" modal header="Form Monitoring & Evaluasi" :style="{ width: '700px' }" :breakpoints="{ '960px': '90vw' }">
            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <!-- Selected Permohonan Info -->
                    <div v-if="selectedPermohonan" class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="font-bold text-blue-900 text-sm">{{ selectedPermohonan.label }}</div>
                        <div class="text-xs text-blue-700">{{ selectedPermohonan.nama_instansi }}</div>
                    </div>

                    <!-- Tanggal Evaluasi -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <Calendar v-model="form.tanggal_evaluasi" dateFormat="dd/mm/yy" class="w-full" />
                    </div>

                    <!-- Questions -->
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="q in questions" :key="q.key" class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-600">{{ q.label }} <span class="text-red-500">*</span></label>
                            <Dropdown v-model="form[q.key]" :options="options[q.key]" placeholder="Pilih" class="w-full text-sm" size="small" :class="{ 'p-invalid': form.errors[q.key] }" />
                        </div>
                    </div>

                    <!-- Kendala & Saran -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-600">Kendala Administrasi</label>
                            <Textarea v-model="form.kendala_administrasi" rows="2" class="w-full text-sm" placeholder="Jelaskan..." />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-600">Saran & Rekomendasi</label>
                            <Textarea v-model="form.saran_rekomendasi" rows="2" class="w-full text-sm" placeholder="Tuliskan..." />
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600">Bukti Pendukung (opsional)</label>
                        <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" @select="handleFileSelect" chooseLabel="Pilih File" class="w-full" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                    <Button label="Batal" severity="secondary" text size="small" @click="formDialog = false" />
                    <Button type="submit" label="Submit" icon="pi pi-check" size="small" :loading="form.processing" />
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>
